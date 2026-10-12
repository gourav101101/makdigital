@if($testimonials->isNotEmpty())
<section class="testimonials-section section-space"><div class="container"><div class="section-heading" data-reveal><div><span class="eyebrow section-label">In our clients’ words</span><h2>Good relationships.<br><span class="muted-heading">Better work.</span></h2></div></div><div class="quote-grid">@foreach($testimonials as $testimonial)<figure class="client-quote" data-reveal><span class="quote-mark" aria-hidden="true">“</span><blockquote>{{ $testimonial->body }}</blockquote><figcaption><span>{{ $testimonial->title }}</span><small>{{ $testimonial->summary }}</small></figcaption></figure>@endforeach</div></div></section>
@endif
@if($articles->isNotEmpty())
<section class="insights-section section-space container"><div class="section-heading" data-reveal><div><span class="eyebrow section-label">A fresh perspective</span><h2>Ideas worth<br><span class="muted-heading">thinking about.</span></h2></div><a class="text-link" href="{{ route('insights.index') }}">All insights <x-icon /></a></div><div class="insight-grid">@foreach($articles as $article)@include('frontend.partials.article-card')@endforeach</div></section>
@endif
