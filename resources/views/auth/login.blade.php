@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">Welcome back</p>
    <h1 id="auth-title">Sign in to your account</h1>
    @include('auth.partials.messages')
    <form method="POST" action="{{ route('login.submit') }}" class="auth-form">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password')<p class="field-error">{{ $message }}</p>@enderror
        <label class="auth-check"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="auth-button" type="submit">Sign in</button>
    </form>
    <p class="auth-links"><a href="{{ route('password.request') }}">Forgot your password?</a></p>
    <p class="auth-footer">New here? <a href="{{ route('register') }}">Create an account</a></p>
</section>
@endsection