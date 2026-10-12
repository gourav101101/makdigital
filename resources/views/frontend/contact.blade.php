@extends('layouts.site')
@section('title', 'Contact MAK Digital Arena — Let’s start something')
@section('description', 'Have an idea for your business? Contact MAK Digital Arena in Indore about websites, marketing, SEO and business consulting.')
@section('content')
@include('frontend.partials.contact')
<section class="container contact-options"><div><span class="eyebrow">Based in</span><h2>{{ $contactSettings['location'] }}</h2></div><div><span class="eyebrow">Prefer a conversation?</span><a href="tel:{{ preg_replace('/[^+0-9]/', '', $contactSettings['phone']) }}">{{ $contactSettings['phone'] }} <x-icon /></a></div></section>
@endsection
