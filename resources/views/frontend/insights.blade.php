@extends('layouts.site')
@section('title', 'Insights — MAK Digital Arena')
@section('description', 'Practical perspectives on brand strategy, websites, content and digital marketing from MAK Digital Arena.')
@section('content')
<section class="detail-hero container"><span class="eyebrow">The things we think about</span><h1>A fresh<br><span class="blue-text">perspective.</span></h1><p>Ideas, observations and practical starting points for your next digital move.</p></section>
<section class="container section-space"><div class="insight-grid">@forelse($articles as $article)@include('frontend.partials.article-card')@empty<p>New perspectives are on their way.</p>@endforelse</div><div class="simple-pagination">@if($articles->previousPageUrl())<a href="{{ $articles->previousPageUrl() }}">← Previous</a>@endif @if($articles->hasMorePages())<a href="{{ $articles->nextPageUrl() }}">Next →</a>@endif</div></section>
@endsection
