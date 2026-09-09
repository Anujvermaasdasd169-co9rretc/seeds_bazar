@extends('layouts.auth')
@section('title', 'Your account')
@section('content')
<section class="auth-card account-card" aria-labelledby="account-title">
    <p class="auth-eyebrow">Customer account</p><h1 id="account-title">Hello, {{ $user->first_name ?: $user->name }}</h1>
    @include('auth.partials.messages')
    @if (! $user->hasVerifiedEmail())
        <div class="auth-alert auth-alert--notice">Your email is not verified yet. <a href="{{ route('verification.notice') }}">Verify it now</a>.</div>
    @endif
    <dl class="account-details"><div><dt>Name</dt><dd>{{ $user->name }}</dd></div><div><dt>Email</dt><dd>{{ $user->email }}</dd></div><div><dt>Mobile</dt><dd>{{ $user->mobile ?: 'Not provided' }}</dd></div></dl>
    <p class="auth-links"><a href="{{ route('addresses.index') }}">Manage addresses</a> | <a href="{{ route('orders.index') }}">View orders</a></p>
    <h2>Profile</h2>
    <form method="POST" action="{{ route('account.profile') }}" class="auth-form">
        @csrf @method('PUT')
        <div class="auth-grid"><label>First name<input name="first_name" value="{{ old('first_name', $user->first_name) }}" required></label><label>Last name<input name="last_name" value="{{ old('last_name', $user->last_name) }}" required></label></div>
        <label>Email<input name="email" type="email" value="{{ old('email', $user->email) }}" required></label>
        <label>Mobile<input name="mobile" type="tel" value="{{ old('mobile', $user->mobile) }}" required></label>
        <button class="auth-button" type="submit">Save profile</button>
    </form>
    <h2>Change password</h2>
    <form method="POST" action="{{ route('account.password') }}" class="auth-form">
        @csrf
        <label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
        <label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" required>
        <label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        <button class="auth-button" type="submit">Change password</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="auth-inline-form"><button type="submit" class="auth-text-button">Sign out</button>@csrf</form>
</section>
@endsection