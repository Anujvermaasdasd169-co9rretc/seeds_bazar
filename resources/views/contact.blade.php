@extends('layouts.app')

@section('title', 'Contact Us — Seed Planta')

@section('content')
<div class="contact-wrap">
    <div class="contact-card">
        <div class="contact-hero">
            <h1>Contact Us</h1>
            <p>Share your details and we’ll contact you soon. Query is optional.</p>
        </div>

        @if (session('success'))
            <div class="form-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="form-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('contact.store', absolute: false) }}" class="contact-form">
            <div class="contact-field">
                <label for="name">Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" placeholder="Your name">
            </div>

            <div class="contact-field">
                <label for="mobile">Mobile *</label>
                <input id="mobile" name="mobile" type="text" value="{{ old('mobile') }}" required maxlength="25" placeholder="Your mobile number">
            </div>

            <div class="contact-field contact-field--full">
                <label for="email">Email *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="you@example.com">
            </div>

            <div class="contact-field contact-field--full">
                <label for="address">Address *</label>
                <input id="address" name="address" type="text" value="{{ old('address') }}" required maxlength="255" placeholder="Your address">
            </div>

            <div class="contact-field contact-field--full">
                <label for="query">Query (optional)</label>
                <textarea id="query" name="query" rows="4" maxlength="2000" placeholder="Write your message (optional)">{{ old('query') }}</textarea>
            </div>

            <div class="contact-actions">
                <div class="form-note">Fields marked * are required.</div>
                <div style="display:flex; gap: 0.75rem; flex-wrap: wrap; align-items:center;">
                    <a class="contact-back" href="{{ route('shop.index') }}">← Back to store</a>
                    <button type="submit" class="contact-submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

