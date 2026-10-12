@extends('layouts.site')
@section('title', $service['label'].' — MAK Digital Arena')
@section('description', $service['description'] ?? '')
@section('content')
<section class="detail-hero container"><a class="text-link" href="{{ route('home') }}#services">← All our capabilities</a><span class="eyebrow">{{ $service['number'] }} / {{ $service['label'] }}</span><h1>{{ $service['title'] }}</h1><p>{{ $service['description'] }}</p></section>
<section class="container detail-body"><div class="detail-description"><h2>How we can help.</h2><div class="detail-service-list">@foreach($service['items'] as $item)<div><span>0{{ $loop->iteration }}</span><h3>{{ $item }}</h3><x-icon /></div>@endforeach</div></div><div class="reading-content service-body">@foreach(preg_split('/\n\s*\n/', $service['body'] ?? '') as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div><div class="faq-section">@foreach($service['faqs'] as $faq)<details><summary>{{ $faq['question'] }} <x-icon name="plus" /></summary><p>{{ $faq['answer'] }}</p></details>@endforeach</div><div class="detail-cta"><h2>Let’s find the right<br>direction for you.</h2><a class="button button-dark" href="{{ route('home') }}#contact">Start a conversation <span class="button-icon"><x-icon /></span></a></div></section>
@endsection
