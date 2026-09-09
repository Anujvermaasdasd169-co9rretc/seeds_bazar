@extends('layouts.auth')
@section('title', 'Reset password')
@section('content')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">Account recovery</p><h1 id="auth-title">Forgot your password?</h1>
    <p class="auth-copy">Enter your email and we will send instructions if an account matches it.</p>
    @include('auth.partials.messages')
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        <button class="auth-button" type="submit">Send reset link</button>
    </form>
    <p class="auth-footer"><a href="{{ route('login') }}">Back to sign in</a></p>
</section>
@endsection