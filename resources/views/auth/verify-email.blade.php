@extends('layouts.auth')
@section('title', 'Verify your email')
@section('content')
<section class="auth-card" aria-labelledby="auth-title">
    <p class="auth-eyebrow">One last step</p><h1 id="auth-title">Verify your email address</h1>
    <p class="auth-copy">We sent a verification link to your email address. You can use your account while you verify it.</p>
    @include('auth.partials.messages')
    <form method="POST" action="{{ route('verification.send') }}"><button class="auth-button" type="submit">Send another link</button>@csrf</form>
    <form method="POST" action="{{ route('logout') }}" class="auth-inline-form"><button type="submit" class="auth-text-button">Sign out</button>@csrf</form>
</section>
@endsection