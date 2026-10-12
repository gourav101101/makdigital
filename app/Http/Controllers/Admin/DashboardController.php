<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyEnquiry;
use App\Models\ContentEntry;
use App\Models\Enquiry;
use App\Models\SiteSetting;
use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', ['contentCount' => ContentEntry::count(), 'draftCount' => ContentEntry::where('published', false)->count(), 'newCount' => Enquiry::where('status', 'new')->count(), 'failedCount' => Enquiry::whereIn('notification_status', ['failed', 'retrying'])->count(), 'recent' => Enquiry::latest()->limit(5)->get()]);
    }

    public function enquiries(Request $request): View
    {
        $query = Enquiry::latest();
        if (in_array($request->query('status'), ['new', 'in_progress', 'closed'], true)) {
            $query->where('status', $request->query('status'));
        }

        return view('admin.enquiries', ['enquiries' => $query->paginate(20)->withQueryString()]);
    }

    public function show(Enquiry $enquiry): View
    {
        return view('admin.enquiry', compact('enquiry'));
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $enquiry->update($request->validate(['status' => 'required|in:new,in_progress,closed', 'notes' => 'nullable|string|max:10000']));

        return back()->with('status', 'Enquiry updated.');
    }

    public function retry(Enquiry $enquiry): RedirectResponse
    {
        if (! config('site.enquiry_notifications') || ! config('site.enquiry_recipient')) {
            return back()->withErrors(['notification' => 'Configure notifications and a recipient in the environment before retrying.']);
        }
        if ($enquiry->notified_at || in_array($enquiry->notification_status, ['queued', 'retrying'], true)) {
            return back()->withErrors(['notification' => 'Already sent or awaiting processing.']);
        }
        try {
            $enquiry->update(['notification_status' => 'queued', 'notification_error' => null]);
            NotifyEnquiry::dispatch($enquiry->id);
        } catch (\Throwable $error) {
            $enquiry->update(['notification_status' => 'failed', 'notification_error' => 'Could not queue notification.']);
            report($error);

            return back()->withErrors(['notification' => 'Could not queue notification. The enquiry remains saved.']);
        }

        return back()->with('status', 'Notification queued.');
    }

    public function settings(SiteContent $content): View
    {
        return view('admin.settings', ['settings' => $content->settings()]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'phone' => ['required', 'string', 'max:40', 'regex:/^[+0-9 ()-]+$/'], 'location' => 'required|string|max:255', 'instagram' => 'nullable|url:https|max:1000', 'facebook' => 'nullable|url:https|max:1000']);
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('status', 'Contact settings saved.');
    }
}
