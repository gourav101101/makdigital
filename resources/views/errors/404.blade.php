@extends('layouts.site')
@section('title', 'Page not found — MAK Digital Arena')
@section('content')<section class="detail-hero container"><span class="eyebrow">404 / A different direction</span><h1>This page<br>has moved on.</h1><p>Let’s get you back to something useful.</p><a class="button button-dark" href="{{ route('home') }}">Back to MAK <span class="button-icon"><x-icon /></span></a></section>@endsection
