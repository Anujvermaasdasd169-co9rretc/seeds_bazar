@extends('layouts.auth')
@section('title', 'Your addresses')
@section('content')
<section class="address-page" aria-labelledby="address-title">
    <aside class="address-intro">
        <div class="address-intro__sprout" aria-hidden="true">✦</div>
        <p class="auth-eyebrow">Your growing space</p>
        <h1 id="address-title">Where should we send your next harvest?</h1>
        <p>Keep your delivery details close at hand. We will make sure every seed packet reaches the right garden.</p>
        <div class="address-intro__note"><span aria-hidden="true">✓</span><span>Carefully packed<br>and delivered to you</span></div>
    </aside>
    <div class="address-workspace">
        @include('auth.partials.messages')
        <div class="address-heading"><div><p class="auth-eyebrow">Delivery details</p><h2>Saved addresses</h2></div><span class="address-count">{{ $addresses->count() }} saved</span></div>
        <div class="address-list">
            @forelse ($addresses as $address)
                <article class="address-item">
                    <div class="address-item__body"><div class="address-item__title"><strong>{{ $address->full_name }}</strong>@if ($address->is_default)<span class="address-default">Default</span>@endif</div><p>{{ $address->phone }}<br>{{ $address->address_line_1 }}{{ $address->address_line_2 ? ', '.$address->address_line_2 : '' }}<br>{{ $address->city }}, {{ $address->state }} - {{ $address->postal_code }}, {{ $address->country }}</p></div>
                    <div class="address-item__actions"><a class="auth-text-button" href="{{ route('addresses.index', ['edit' => $address->id]) }}">Edit</a>@if (! $address->is_default)<form method="POST" action="{{ route('addresses.default', $address) }}">@csrf @method('PATCH')<button class="auth-text-button">Make default</button></form>@endif<form method="POST" action="{{ route('addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="auth-text-button" type="submit">Remove</button></form></div>
                </article>
            @empty
                <div class="address-empty"><span aria-hidden="true">⌂</span><strong>No saved addresses yet</strong><p>Add your first delivery spot below.</p></div>
            @endforelse
        </div>
        <div class="address-form-heading"><p class="auth-eyebrow">{{ $editing ? 'Update a destination' : 'New destination' }}</p><h2>{{ $editing ? 'Edit address' : 'Add a delivery address' }}</h2></div>
        <form method="POST" action="{{ $editing ? route('addresses.update', $editing) : route('addresses.store') }}" class="auth-form">
            @csrf @if ($editing) @method('PUT') @endif
            <div class="auth-grid"><label>Full name<input name="full_name" value="{{ old('full_name', $editing?->full_name ?: auth()->user()->name) }}" required></label><label>Phone<input name="phone" type="tel" value="{{ old('phone', $editing?->phone ?: auth()->user()->mobile) }}" required></label></div>
            <label>Address line 1<input name="address_line_1" value="{{ old('address_line_1', $editing?->address_line_1) }}" required></label>
            <div class="auth-grid"><label>Address line 2<input name="address_line_2" value="{{ old('address_line_2', $editing?->address_line_2) }}"></label><label>Landmark<input name="landmark" value="{{ old('landmark', $editing?->landmark) }}"></label></div>
            <div class="auth-grid"><label>City<input name="city" value="{{ old('city', $editing?->city) }}" required></label><label>State<input name="state" value="{{ old('state', $editing?->state) }}" required></label></div>
            <div class="auth-grid"><label>Postal code<input name="postal_code" inputmode="numeric" value="{{ old('postal_code', $editing?->postal_code) }}" required></label><label>Country<input name="country" value="{{ old('country', $editing?->country ?: 'India') }}" required></label></div>
            <label class="auth-check"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $editing?->is_default))> Make this my default address</label>
            <button class="auth-button" type="submit">{{ $editing ? 'Update address' : 'Save address' }} <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-footer"><a href="{{ route('account') }}">← Back to account</a></p>
    </div>
</section>
@endsection