@extends('layouts.site')
@section('title', $project['name'].' — MAK Digital Arena')
@section('description', $project['description'] ?? '')
@section('content')
<section class="detail-hero container"><a class="text-link" href="{{ route('home') }}#work">← Back to selected work</a><span class="eyebrow">{{ $project['category'] }} / Website showcase</span><h1>{{ $project['name'] }}</h1><p>{{ $project['headline'] }}</p></section>
<section class="container detail-body"><div class="project-image project-{{ $project['color'] }}"><img src="{{ $project['image_url'] }}" @if($project['image_small']) srcset="{{ $project['image_small'] }} 768w, {{ $project['image_url'] }} 1280w" sizes="(max-width: 800px) 92vw, 80vw" @endif width="1280" height="877" alt="{{ $project['image_alt'] }}"></div><div class="detail-description"><h2>A closer look.</h2><div><p>{{ $project['description'] }}</p><p>{{ $project['note'] }}</p></div></div><div class="detail-cta"><h2>Your next project<br>could start here.</h2><a class="button button-dark" href="{{ route('home') }}#contact">Let’s talk <span class="button-icon"><x-icon /></span></a></div></section>
@endsection
