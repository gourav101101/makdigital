@extends('layouts.site')
@section('title', 'Selected work — MAK Digital Arena')
@section('description', 'Explore MAK Digital Arena website projects across interiors, automotive and construction.')
@section('content')
<section class="detail-hero container"><span class="eyebrow">Our work / Digital experiences</span><h1>Different businesses.<br><span class="blue-text">Shared ambition.</span></h1><p>Explore a selection of businesses we’ve helped bring into the digital arena.</p></section>
<section class="work-section section-space"><div class="container">@include('frontend.partials.projects')@if(!$projects)<p>Our next project showcase is on its way. <a href="{{ route('contact') }}">Talk to us about your project.</a></p>@endif</div></section>
<section class="container detail-cta"><h2>Let’s make your<br>next chapter happen.</h2><a class="button button-dark" href="{{ route('contact') }}">Start a project <span class="button-icon"><x-icon /></span></a></section>
@endsection
