<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'MAK Digital Arena brings strategy, design, development and digital marketing together to help your business move forward. Based in Indore, India.')">
    @unless(app()->isProduction())<meta name="robots" content="noindex, nofollow">@endunless
    <meta name="theme-color" content="#f8f9f6">
    <title>@yield('title', 'MAK Digital Arena — Ideas into impact.')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="canonical" href="{{ url()->current() }}">
    @if(!app()->runningUnitTests() && !file_exists(public_path('hot')))
    <link rel="preload" href="{{ Vite::asset('node_modules/@fontsource-variable/manrope/files/manrope-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endif
    <meta property="og:type" content="website"><meta property="og:title" content="@yield('title', 'MAK Digital Arena ? Ideas into impact.')"><meta property="og:description" content="@yield('description', 'Strategy, design, development and digital marketing from Indore, India.')"><meta property="og:url" content="{{ url()->current() }}"><meta property="og:image" content="{{ asset('images/social-preview.jpg') }}"><meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'MAK Digital Arena', 'url' => route('home'), 'logo' => asset('images/mak-logo.png'), 'email' => $contactSettings['email'], 'telephone' => $contactSettings['phone']], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @vite(['resources/css/frontend/app.css', 'resources/js/frontend/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <svg class="icon-definitions" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <symbol id="arrow-up-right" viewBox="0 0 24 24"><path d="M5 19 19 5M5 5h14v14"/></symbol>
        <symbol id="arrow-right" viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></symbol>
        <symbol id="plus" viewBox="0 0 24 24"><path d="M12 4v16M4 12h16"/></symbol>
        <symbol id="star" viewBox="0 0 24 24"><path d="M12 1v22M1 12h22M4 4l16 16M4 20 20 4"/></symbol>
    </svg>
    <header class="site-header" data-header>
        <a class="brand" href="{{ route('home') }}" aria-label="MAK Digital Arena home"><img src="{{ asset('images/mak-logo.png') }}" width="231" height="99" alt="MAK Digital Arena"></a>
        <nav class="desktop-nav" aria-label="Main navigation"><a href="{{ route('work.index') }}">Our work</a><a href="{{ route('services.index') }}">What we do</a><a href="{{ route('about') }}">The MAK way</a><a href="{{ route('insights.index') }}">Insights</a></nav>
        <a class="header-cta" href="{{ route('contact') }}">Let’s talk <x-icon /></a>
        <button class="menu-toggle" aria-controls="mobile-menu" aria-expanded="false" aria-label="Open navigation" data-menu-toggle><span></span><span></span></button>
    </header>
    <nav id="mobile-menu" class="mobile-menu" aria-label="Mobile navigation" hidden>
        <span class="eyebrow">A good place to start</span>
        <a href="{{ route('work.index') }}">Our work <x-icon /></a><a href="{{ route('services.index') }}">What we do <x-icon /></a><a href="{{ route('about') }}">The MAK way <x-icon /></a><a href="{{ route('contact') }}">Let’s talk <x-icon /></a><a href="{{ route('insights.index') }}">Insights <x-icon /></a><a href="{{ route('careers') }}">Careers <x-icon /></a><a class="mobile-email" href="mailto:{{ $contactSettings['email'] }}">{{ $contactSettings['email'] }}</a>
    </nav>
    <noscript><nav class="noscript-nav" aria-label="Quick navigation"><a href="{{ route('work.index') }}">Work</a><a href="{{ route('services.index') }}">Services</a><a href="{{ route('about') }}">About</a><a href="{{ route('contact') }}">Contact</a></nav><style>.menu-toggle{display:none}</style></noscript>
    <main id="main">@yield('content')</main>
    <footer class="site-footer">
        <div class="footer-top container">
            <div><a class="footer-name" href="{{ route('home') }}">MAK<span>✳</span></a><p>You visualize. We digitalize.</p></div>
            <div class="footer-links"><span class="eyebrow">Find your way</span><a href="{{ route('work.index') }}">Our work</a><a href="{{ route('services.index') }}">What we do</a><a href="{{ route('about') }}">About MAK</a><a href="{{ route('insights.index') }}">Insights</a><a href="{{ route('careers') }}">Careers</a></div>
            <div class="footer-links"><span class="eyebrow">Say hello</span><a href="mailto:{{ $contactSettings['email'] }}">{{ $contactSettings['email'] }}</a><a href="tel:{{ preg_replace('/[^+0-9]/', '', $contactSettings['phone']) }}">{{ $contactSettings['phone'] }}</a><span>{{ $contactSettings['location'] }}</span></div>
            <div class="footer-links"><span class="eyebrow">Follow along</span><a href="{{ $contactSettings['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram <x-icon /></a><a href="{{ $contactSettings['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook <x-icon /></a></div>
        </div>
        <div class="footer-bottom container"><span>© {{ date('Y') }} MAK Digital Arena</span><span class="legal-links"><a href="{{ route('privacy-policy') }}">Privacy policy</a><a href="{{ route('terms-and-conditions') }}">Terms & conditions</a></span><a href="#main">Back to top ↑</a></div>
    </footer>
    @stack('scripts')
</body>
</html>
