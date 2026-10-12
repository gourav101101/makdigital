<?php

namespace App\Jobs;

use App\Models\Enquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Mail;

class NotifyEnquiry implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('enquiry-'.$this->enquiryId))->releaseAfter(30)->expireAfter(90)];
    }

    public function handle(): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry || $enquiry->notified_at) {
            return;
        }
        if (! config('site.enquiry_notifications') || ! config('site.enquiry_recipient')) {
            $enquiry->update(['notification_status' => 'not_configured']);

            return;
        }
        try {
            Mail::raw("New project enquiry\n\nName: ".$enquiry->name."\nEmail: ".$enquiry->email."\nService: ".$enquiry->service."\n\n".$enquiry->message."\n\nManage: ".route('admin.enquiries.show', $enquiry), function ($message) use ($enquiry): void {
                $message->to(config('site.enquiry_recipient'))->replyTo($enquiry->email)->subject('New MAK website enquiry #'.$enquiry->id);
            });
            $local = in_array(config('mail.default'), ['log', 'array'], true);
            $enquiry->update(['notification_status' => $local ? 'logged' : 'sent', 'notified_at' => $local ? null : now(), 'notification_error' => null]);
        } catch (\Throwable $error) {
            $enquiry->update(['notification_status' => 'retrying', 'notification_error' => 'Mail transport failed. See server logs.']);
            throw $error;
        }
    }

    public function failed(?\Throwable $error): void
    {
        Enquiry::whereKey($this->enquiryId)->update(['notification_status' => 'failed', 'notification_error' => 'Notification failed after retries. Check mail configuration and retry from admin.']);
    }
}
