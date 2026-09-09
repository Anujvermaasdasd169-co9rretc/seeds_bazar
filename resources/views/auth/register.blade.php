@extends('layouts.auth')
@section('title', 'Create account')
@section('content')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">Join the garden</p>
    <h1 id="auth-title">Create your account</h1>
    @include('auth.partials.messages')
    <form method="POST" action="{{ route('register.submit') }}" class="auth-form">
        @csrf
        <div class="auth-grid">
            <div><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required></div>
            <div><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required></div>
        </div>
        <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        <label for="mobile">Mobile number</label><input id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" autocomplete="tel" required>
        <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required>
        <p class="auth-hint">Use at least 8 characters with upper and lower case letters, a number, and a symbol.</p>
        <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <label class="auth-check"><input type="checkbox" name="terms" value="1" required> I accept the Terms &amp; Conditions.</label>
        <button class="auth-button" type="submit">Create account</button>
    </form>
    <p class="auth-footer">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
</section>
@endsection