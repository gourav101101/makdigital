<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(SiteContent $content): View
    {
        return view('frontend.home', ['projects' => $content->projects(), 'services' => $content->services(), 'articles' => $content->entries('article')->take(2), 'testimonials' => $content->entries('testimonial')->take(2), 'contactSettings' => $content->settings()]);
    }

    public function project(string $slug, SiteContent $content): View
    {
        $project = $content->projects()[$slug] ?? null;
        abort_unless($project, 404);

        return view('frontend.project', compact('project'));
    }

    public function service(string $slug, SiteContent $content): View
    {
        $service = $content->services()[$slug] ?? null;
        abort_unless($service, 404);

        return view('frontend.service', compact('service'));
    }
}
