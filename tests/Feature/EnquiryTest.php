<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    private function enquiry(): array
    {
        return ['submission_id' => (string) Str::uuid(), 'name' => 'Test Client', 'email' => 'client@example.com', 'service' => 'web-and-app-development', 'message' => 'We would like to discuss a new business website.', 'consent' => '1', 'website' => ''];
    }

    public function test_a_valid_enquiry_is_saved_once_even_when_retried(): void
    {
        $payload = $this->enquiry();
        $this->postJson(route('enquiries.store'), $payload)->assertCreated()->assertJsonStructure(['message']);
        $this->postJson(route('enquiries.store'), $payload)->assertCreated();
        $this->assertDatabaseCount('enquiries', 1);
        $this->assertDatabaseHas('enquiries', ['email' => 'client@example.com', 'service' => 'web-and-app-development']);
    }

    public function test_invalid_input_never_creates_an_enquiry(): void
    {
        $payload = array_replace($this->enquiry(), ['email' => 'invalid', 'message' => 'Short', 'service' => 'unknown', 'consent' => '0']);
        $this->postJson(route('enquiries.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors(['email', 'message', 'service', 'consent']);
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_honeypot_submissions_are_rejected(): void
    {
        $this->postJson(route('enquiries.store'), array_replace($this->enquiry(), ['website' => 'https://spam.example']))->assertUnprocessable()->assertJsonValidationErrors('website');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_html_submission_redirects_to_contact_with_confirmation(): void
    {
        $this->post(route('enquiries.store'), $this->enquiry())->assertRedirect(route('home').'#contact')->assertSessionHas('enquiry_success');
        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_rate_limiting_rejects_excessive_submissions(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('enquiries.store'), $this->enquiry())->assertCreated();
        }
        $this->postJson(route('enquiries.store'), $this->enquiry())->assertStatus(429);
        $this->assertDatabaseCount('enquiries', 5);
    }
}
