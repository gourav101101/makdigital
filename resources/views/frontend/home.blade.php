@extends('layouts.site')
@section('content')
<section class="hero container" aria-labelledby="hero-title">
    <div class="hero-copy">
        <div class="eyebrow hero-eyebrow"><span class="status-dot"></span> Independent minds. Connected thinking.</div>
        <h1 id="hero-title"><span>You visualize.</span><span class="blue-text">We digitalize<span class="period">.</span></span></h1>
        <p class="hero-description">Big ideas meet their digital potential.<br>We bring strategy, design and technology together<br class="desktop-break"> to move your business forward.</p>
        <div class="hero-actions"><a class="button button-dark" href="#contact">Let’s make it happen <span class="button-icon"><x-icon /></span></a><a class="text-link" href="#work">Explore our work <x-icon name="arrow-right" /></a></div>
        <div class="hero-note"><span class="note-line"></span><span>Rooted in Indore.<br>Thinking beyond boundaries.</span></div>
    </div>
    <div class="hero-art"><x-globe /><div class="idea-tag"><span class="idea-spark">✳</span> Ideas into impact.</div></div>
    <div class="hero-bottom"><span>STRATEGY &nbsp; / &nbsp; DESIGN &nbsp; / &nbsp; DEVELOPMENT &nbsp; / &nbsp; GROWTH</span><a href="#work">A little scroll. A lot of possibility. <span>↓</span></a></div>
</section>
<section class="portfolio-names container" aria-label="Businesses in our portfolio"><p>Different industries.<br>Shared ambition.</p><div class="client-names"><span class="client-stone">STONEPLUS<span>SURFACES</span></span><span class="client-hans">HANS LINE</span><span class="client-kpl">KPL<span>KESHAYONA</span></span><span class="client-md">MD<span>MEDICINE</span></span><span class="client-msm">MSM BHARAT</span></div></section>
<section id="work" class="work-section section-space"><div class="container">
    <div class="section-heading" data-reveal><div><span class="eyebrow section-label"><span>01 /</span> Selected work</span><h2>Good ideas.<br><span class="muted-heading">Real-world presence.</span></h2></div><p>A few businesses we’ve helped<br>bring into the digital arena.<br>Different challenges. Individual approaches.</p></div>
    @include('frontend.partials.projects')
</div></section>
<section id="services" class="services-section section-space container">
    <div class="section-heading" data-reveal><div><span class="eyebrow section-label"><span>02 /</span> What we do</span><h2>Many ways to move.<br><span class="muted-heading">One team to make it happen.</span></h2></div></div>
    <div class="services-layout"><div class="services-art" aria-hidden="true"><div class="services-art-grid"></div><div class="service-orb"><span></span><span></span><span></span><span></span></div><div class="service-art-bottom"><x-icon name="star" /><span>Different disciplines.<br>One connected perspective.</span></div><span class="service-art-caption">THE MAK APPROACH</span></div><div class="service-list">
        @foreach($services as $slug => $service)<details class="service-item" name="services" {{ $loop->first ? 'open' : '' }}><summary><span class="service-number">{{ $service['number'] }}</span><span><small>{{ $service['label'] }}</small><h3>{{ $service['title'] }}</h3></span><span class="service-toggle"><x-icon name="plus" /></span></summary><div class="service-content"><p>{{ $service['description'] }}</p><div class="service-tags">@foreach($service['items'] as $item)<span>{{ $item }}</span>@endforeach</div><a class="text-link" href="{{ route('services.show', $slug) }}">Explore this service <x-icon /></a></div></details>@endforeach
    </div></div>
</section>
<section id="about" class="about-section section-space"><div class="container"><span class="eyebrow section-label"><span>03 /</span> The MAK way</span><div class="about-statement" data-reveal><h2>We see the bigger picture.<br>Then obsess over<br><span>the little details.</span><span class="statement-star" aria-hidden="true">✳</span></h2></div><div class="about-bottom"><span class="about-location">INDEPENDENT THINKING.<br>INDORE, INDIA.</span><p>We’re a mix of curious thinkers, designers and digital problem-solvers. Bringing fresh perspectives and hands-on experience together, we help businesses turn possibilities into a purposeful online presence.</p><a class="circle-link" href="#contact" aria-label="Talk to the MAK team"><x-icon /></a></div></div></section>
<section class="process-section section-space container" aria-labelledby="process-title"><div class="section-heading" data-reveal><div><span class="eyebrow section-label"><span>04 /</span> From possibility to progress</span><h2 id="process-title">Great work starts<br>with a good conversation.</h2></div><p>We stay curious. We stay connected.<br>And we keep your business goals<br>at the heart of every step.</p></div><div class="process-grid">
    @foreach([['Listen & discover', 'We get to know your business, your audience and what you want to change.'], ['Connect the dots', 'We turn what we learn into a clear direction and a practical plan.'], ['Create & build', 'We bring the idea to life, with thoughtful design and careful development.'], ['Launch & evolve', 'We help you take the next step, then keep learning and improving together.']] as $step)<article class="process-step" data-reveal><div class="step-top"><span>0{{ $loop->iteration }}</span><x-icon name="{{ $loop->last ? 'star' : 'arrow-right' }}" /></div><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach
</div></section>
@include('frontend.partials.social-proof')
@include('frontend.partials.contact')
@endsection
