@extends('layouts.auth')
@section('title', 'Choose a new password')
@section('content')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">Account recovery</p><h1 id="auth-title">Choose a new password</h1>
    @include('auth.partials.messages')
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        <label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" required>
        <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <button class="auth-button" type="submit">Reset password</button>
    </form>
</section>
@endsection