<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Jobs\NotifyEnquiry;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EnquiryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreEnquiryRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except(['website', 'consent']);
        $enquiry = Enquiry::firstOrCreate(['submission_id' => $data['submission_id']], $data);
        if ($enquiry->wasRecentlyCreated && config('site.enquiry_notifications') && config('site.enquiry_recipient')) {
            try {
                $enquiry->update(['notification_status' => 'queued']);
                NotifyEnquiry::dispatch($enquiry->id);
            } catch (\Throwable $error) {
                $enquiry->update(['notification_status' => 'failed', 'notification_error' => 'Notification could not be queued. Check server logs.']);
                report($error);
            }
        }
        $message = 'Your project enquiry has been saved. Thank you for getting in touch with MAK.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return redirect()->to(route('home').'#contact')->with('enquiry_success', $message);
    }
}
