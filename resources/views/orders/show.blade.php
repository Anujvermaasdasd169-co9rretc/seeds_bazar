@extends('layouts.auth')
@section('title', 'Order '.$order->order_number)
@section('content')
<section class="auth-card" aria-labelledby="order-title">
    <p class="auth-eyebrow">Order confirmation</p><h1 id="order-title">{{ $order->order_number }}</h1>
    @if (session('status'))<div class="auth-alert auth-alert--success" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="auth-alert auth-alert--error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="order-summary"><span>Status <strong>{{ ucfirst($order->status) }}</strong></span><span>Payment <strong>{{ $order->payment_method === 'online' ? 'Online Payment' : 'Cash on Delivery' }} ({{ ucfirst($order->payment_status) }})</strong></span></div>
    @if (in_array($order->status, ['pending', 'confirmed', 'processing'], true))
        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="auth-inline-form" onsubmit="return confirm('Are you sure you want to cancel this order?')">
            @csrf
            <button class="auth-text-button" type="submit">Cancel order</button>
        </form>
    @endif
    @if ($order->payment_method === 'online' && $order->payment_status !== 'paid')<p class="auth-links"><a href="{{ route('payments.show', $order) }}">Retry payment</a></p>@endif
    <div class="checkout-items">@foreach ($order->items as $item)<div class="order-row"><span><strong>{{ $item->product_name }}</strong><small>{{ $item->quantity }} x Rs {{ number_format($item->unit_price, 2) }}</small></span><strong>Rs {{ number_format($item->line_total, 2) }}</strong></div>@endforeach</div>
    <dl class="account-details"><div><dt>Shipping address</dt><dd>{{ $order->shipping_name }}<br>{{ $order->shipping_phone }}<br>{{ $order->shipping_address }}</dd></div><div><dt>Subtotal</dt><dd>Rs {{ number_format($order->subtotal, 2) }}</dd></div><div><dt>Shipping</dt><dd>{{ $order->shipping_charge > 0 ? 'Rs '.number_format($order->shipping_charge, 2) : 'FREE' }}</dd></div><div><dt>Estimated delivery</dt><dd>{{ $order->delivery_estimate }}</dd></div><div><dt>Total</dt><dd>Rs {{ number_format($order->total, 2) }}</dd></div></dl>
    <p class="auth-footer"><a href="{{ route('orders.index') }}">My orders</a> | <a href="{{ route('shop.index') }}">Continue shopping</a></p>
</section>
@endsection