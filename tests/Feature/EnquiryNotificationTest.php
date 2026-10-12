<?php

namespace Tests\Feature;

use App\Jobs\NotifyEnquiry;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnquiryNotificationTest extends TestCase
{
    private function payload(): array
    {
        return ['submission_id' => (string) Str::uuid(), 'name' => 'Notification test', 'email' => 'test@example.com', 'service' => 'not-sure-yet', 'message' => 'Please help with a new website project.', 'consent' => '1'];
    }

    public function test_enabled_notifications_are_queued_once(): void
    {
        Bus::fake();
        config(['site.enquiry_notifications' => true, 'site.enquiry_recipient' => 'owner@example.com']);
        $payload = $this->payload();
        $this->postJson('/enquiries', $payload)->assertCreated();
        $this->postJson('/enquiries', $payload)->assertCreated();
        Bus::assertDispatchedTimes(NotifyEnquiry::class, 1);
        $this->assertDatabaseHas('enquiries', ['notification_status' => 'queued']);
    }

    public function test_queue_failure_does_not_lose_submission(): void
    {
        config(['site.enquiry_notifications' => true, 'site.enquiry_recipient' => 'owner@example.com']);
        Bus::shouldReceive('dispatch')->once()->andThrow(new \RuntimeException('Queue unavailable'));
        $this->postJson('/enquiries', $this->payload())->assertCreated();
        $this->assertDatabaseHas('enquiries', ['notification_status' => 'failed']);
    }

    public function test_log_transport_is_not_marked_as_delivered(): void
    {
        config(['site.enquiry_notifications' => true, 'site.enquiry_recipient' => 'owner@example.com', 'mail.default' => 'log']);
        Mail::shouldReceive('raw')->once()->andReturnNull();
        $enquiry = Enquiry::create(collect($this->payload())->except('consent')->all());
        (new NotifyEnquiry($enquiry->id))->handle();
        $this->assertSame('logged', $enquiry->fresh()->notification_status);
        $this->assertNull($enquiry->fresh()->notified_at);
    }

    public function test_mail_failure_is_visible_and_retryable(): void
    {
        config(['site.enquiry_notifications' => true, 'site.enquiry_recipient' => 'owner@example.com']);
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $enquiry = Enquiry::create(collect($this->payload())->except('consent')->all());
        $job = new NotifyEnquiry($enquiry->id);
        try {
            $job->handle();
            $this->fail('Expected transport failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('SMTP unavailable', $error->getMessage());
        }
        $this->assertSame('retrying', $enquiry->fresh()->notification_status);
        $job->failed(new \RuntimeException('Failed'));
        $this->assertSame('failed', $enquiry->fresh()->notification_status);
        Bus::fake();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post('/admin/enquiries/'.$enquiry->id.'/retry')->assertSessionHasNoErrors();
        Bus::assertDispatched(NotifyEnquiry::class);
    }

    public function test_enquiry_management_does_not_expose_data_to_visitors(): void
    {
        $enquiry = Enquiry::create(collect($this->payload())->except('consent')->all());
        $this->get('/admin/enquiries/'.$enquiry->id)->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/enquiries/'.$enquiry->id)->assertOk();
        $this->put('/admin/enquiries/'.$enquiry->id, ['status' => 'in_progress', 'notes' => 'Reviewed internally'])->assertSessionHasNoErrors();
        $this->assertSame('in_progress', $enquiry->fresh()->status);
    }
}
