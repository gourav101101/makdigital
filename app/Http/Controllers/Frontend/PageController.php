<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Services\SiteContent;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function work(SiteContent $content): View
    {
        return view('frontend.work', ['projects' => $content->projects()]);
    }

    public function services(SiteContent $content): View
    {
        return view('frontend.services', ['services' => $content->services()]);
    }

    public function insights(): View
    {
        return view('frontend.insights', ['articles' => ContentEntry::ofKind('article')->published()->orderBy('position')->paginate(9)]);
    }

    public function article(string $slug): View
    {
        return view('frontend.article', ['article' => ContentEntry::ofKind('article')->published()->where('slug', $slug)->firstOrFail()]);
    }

    public function page(): View
    {
        $slug = trim(request()->path(), '/');
        $page = ContentEntry::ofKind('page')->published()->where('slug', $slug)->firstOrFail();

        return view('frontend.page', compact('page'));
    }

    public function contact(SiteContent $content): View
    {
        return view('frontend.contact', ['services' => $content->services(), 'contactSettings' => $content->settings()]);
    }

    public function sitemap(): Response
    {
        $urls = collect(['home', 'work.index', 'services.index', 'insights.index', 'contact'])->map(fn ($name) => route($name));
        $urls = $urls->merge(ContentEntry::published()->get()->map(fn ($entry) => $entry->publicUrl())->filter())->unique();

        return response()->view('frontend.sitemap', compact('urls'))->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $text = app()->isProduction() ? "User-agent: *\nDisallow: /admin\nSitemap: ".route('sitemap')."\n" : "User-agent: *\nDisallow: /\n";

        return response($text)->header('Content-Type', 'text/plain');
    }
}
