@extends('layouts.storefront-auth', ['accountOpen' => false, 'shopPage' => 'orders', 'accountPanel' => 'profile'])
@section('title', 'My orders')
@section('page')
<section class="auth-card" aria-labelledby="orders-title">
    <p class="auth-eyebrow">Customer account</p><h1 id="orders-title">My orders</h1>
    @forelse ($orders as $order)
        <a class="order-row" href="{{ route('orders.show', $order) }}"><span><strong>{{ $order->order_number }}</strong><small>{{ $order->created_at->format('d M Y') }}</small></span><span><strong>Rs {{ number_format($order->total, 2) }}</strong><small>{{ ucfirst($order->status) }}</small></span></a>
    @empty
        <p class="auth-copy">You have not placed any orders yet. <a href="{{ route('shop.index') }}#products-grid">Pick a pack</a></p>
    @endforelse
    {{ $orders->links() }}
    <p class="auth-footer"><a href="{{ route('shop.index') }}">Continue shopping</a> · <a href="{{ route('account') }}" class="js-account-link" data-account-panel="profile">Account</a></p>
</section>
@endsection
