@extends('admin.layout')
@section('title', 'Sign in')
@section('content')<div class="login-card"><span class="kicker">MAK DIGITAL ARENA</span><h1>Welcome back.</h1><p>Sign in to manage your website.</p><form method="POST" action="{{ route('admin.authenticate') }}">@csrf<label>Email address<input type="email" name="email" autocomplete="username" required value="{{ old('email') }}" autofocus></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button type="submit">Sign in →</button></form></div>@endsection
