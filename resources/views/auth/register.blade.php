@extends('layouts.auth')
@section('title', 'Create account')
@section('content')
<article class="register-page" aria-labelledby="auth-title">
    <aside class="register-intro">
        <span class="register-intro__sprout" aria-hidden="true">🌱</span>
        <p class="auth-eyebrow">Join Seed Planta</p>
        <h1 id="auth-title">Grow with a garden account</h1>
        <p>Save your details, track orders, and keep favourite seeds close — all in one place.</p>
        <ul class="register-perks">
            <li>Faster checkout with saved addresses</li>
            <li>Order history whenever you need it</li>
            <li>Fresh varieties and restock updates</li>
        </ul>
        <p class="register-intro__note"><span aria-hidden="true">✓</span> We never share your details. You can shop as a guest too.</p>
    </aside>

    <section class="register-workspace">
        <p class="auth-eyebrow">Create account</p>
        <h2>Tell us a little about you</h2>
        @include('auth.partials.messages')
        <form method="POST" action="{{ route('register.submit') }}" class="auth-form">
            @csrf
            <div class="auth-grid">
                <div>
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" placeholder="Ada" required>
                    @error('first_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" placeholder="Lovelace" required>
                    @error('last_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
            <label for="mobile">Mobile number</label>
            <input id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" autocomplete="tel" placeholder="+91 98765 43210" required>
            @error('mobile')<p class="field-error">{{ $message }}</p>@enderror
            <div class="auth-grid">
                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" placeholder="At least 8 characters" required>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Repeat password" required>
                </div>
            </div>
            <p class="auth-hint">Use upper and lower case letters, a number, and a symbol.</p>
            <label class="auth-check">
                <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                <span>I accept the <a href="{{ route('policies.terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>.</span>
            </label>
            @error('terms')<p class="field-error">{{ $message }}</p>@enderror
            <button class="auth-button" type="submit">Create account</button>
        </form>
        <p class="auth-footer">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
    </section>
</article>
@endsection
