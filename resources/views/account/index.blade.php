@extends('layouts.storefront-auth', ['accountPanel' => 'profile'])
@section('title', 'Your account')
@section('page')
<p class="account-shell__hint">Swipe or tap Profile, Password, and Addresses in one place. <a href="{{ route('orders.index') }}">View orders</a></p>
@endsection
