@extends('layouts.auth')
@section('title', 'Your addresses')
@section('content')
<section class="auth-card" aria-labelledby="address-title">
    <p class="auth-eyebrow">Delivery details</p><h1 id="address-title">Your addresses</h1>
    @include('auth.partials.messages')
    @forelse ($addresses as $address)
        <article class="address-item">
            <strong>{{ $address->full_name }} @if ($address->is_default)<span class="address-default">Default</span>@endif</strong>
            <p>{{ $address->phone }}<br>{{ $address->address_line_1 }}{{ $address->address_line_2 ? ', '.$address->address_line_2 : '' }}<br>{{ $address->city }}, {{ $address->state }} - {{ $address->postal_code }}, {{ $address->country }}</p>
            <p class="auth-inline-form"><a class="auth-text-button" href="{{ route('addresses.index', ['edit' => $address->id]) }}">Edit</a></p>
            @if (! $address->is_default)<form method="POST" action="{{ route('addresses.default', $address) }}" class="auth-inline-form">@csrf @method('PATCH')<button class="auth-text-button">Make default</button></form>@endif
            <form method="POST" action="{{ route('addresses.destroy', $address) }}" class="auth-inline-form">@csrf @method('DELETE')<button class="auth-text-button" type="submit">Remove</button></form>
        </article>
    @empty
        <p class="auth-copy">No saved addresses yet.</p>
    @endforelse
    <h2>{{ $editing ? 'Edit address' : 'Add address' }}</h2>
    <form method="POST" action="{{ $editing ? route('addresses.update', $editing) : route('addresses.store') }}" class="auth-form">
        @csrf @if ($editing) @method('PUT') @endif
        <label>Full name<input name="full_name" value="{{ old('full_name', $editing?->full_name ?: auth()->user()->name) }}" required></label>
        <label>Phone<input name="phone" type="tel" value="{{ old('phone', $editing?->phone ?: auth()->user()->mobile) }}" required></label>
        <label>Address line 1<input name="address_line_1" value="{{ old('address_line_1', $editing?->address_line_1) }}" required></label>
        <label>Address line 2<input name="address_line_2" value="{{ old('address_line_2', $editing?->address_line_2) }}"></label>
        <label>Landmark<input name="landmark" value="{{ old('landmark', $editing?->landmark) }}"></label>
        <div class="auth-grid"><label>City<input name="city" value="{{ old('city', $editing?->city) }}" required></label><label>State<input name="state" value="{{ old('state', $editing?->state) }}" required></label></div>
        <div class="auth-grid"><label>Postal code<input name="postal_code" inputmode="numeric" value="{{ old('postal_code', $editing?->postal_code) }}" required></label><label>Country<input name="country" value="{{ old('country', $editing?->country ?: 'India') }}" required></label></div>
        <label class="auth-check"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $editing?->is_default))> Make default</label>
        <button class="auth-button" type="submit">{{ $editing ? 'Update address' : 'Save address' }}</button>
    </form>
    <p class="auth-footer"><a href="{{ route('account') }}">Back to account</a></p>
</section>
@endsection