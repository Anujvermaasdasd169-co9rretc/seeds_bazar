@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
@php
    $statusClass = match ($order->status) {
        'delivered', 'shipped', 'confirmed', 'processing' => 'badge--green',
        'cancelled' => 'badge--danger',
        default => 'badge--amber',
    };
    $payClass = $order->payment_status === 'paid' ? 'badge--green' : ($order->payment_status === 'failed' ? 'badge--danger' : 'badge--amber');
@endphp

<div class="page-header page-header--row">
    <div>
        <h1>{{ $order->order_number }}</h1>
        <p>{{ $order->created_at->format('d M Y, h:i A') }}</p>
    </div>
    <div class="order-show__header-meta">
        <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
        <span class="badge {{ $payClass }}">{{ $order->payment_method === 'online' ? 'Online' : 'COD' }} · {{ ucfirst($order->payment_status) }}</span>
        <a href="{{ route('admin.orders.index') }}" class="btn btn--sm btn--outline">Back to orders</a>
    </div>
</div>

<div class="order-show">
    <div class="card order-show__info">
        <h2 class="card__title">Customer &amp; delivery</h2>
        <div class="order-show__tiles">
            <div class="order-show__tile">
                <span class="order-show__label">Customer</span>
                <strong>{{ $order->user->name }}</strong>
                <small>{{ $order->user->email }}</small>
            </div>
            <div class="order-show__tile">
                <span class="order-show__label">Payment</span>
                <strong>{{ $order->payment_method === 'online' ? 'Online payment' : 'Cash on Delivery' }}</strong>
                <span class="badge {{ $payClass }}">{{ ucfirst($order->payment_status) }}</span>
            </div>
            <div class="order-show__tile order-show__tile--wide">
                <span class="order-show__label">Delivery address</span>
                <strong>{{ $order->shipping_name }}</strong>
                <small>{{ $order->shipping_phone }}</small>
                <p>{{ $order->shipping_address }}</p>
                <small>{{ $order->delivery_estimate ?: 'No delivery estimate' }}</small>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="order-show__status">
            @csrf
            @method('PATCH')
            <label class="form-field">
                <span>Update status</span>
                <select name="status">
                    @foreach (App\Models\Order::STATUSES as $status)
                        @if ($status !== 'cancelled' || in_array($order->status, ['pending', 'confirmed', 'processing', 'cancelled'], true))
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endif
                    @endforeach
                </select>
            </label>
            <button class="btn btn--primary" type="submit">Save</button>
        </form>
    </div>

    <div class="card order-show__items">
        <h2 class="card__title">Items</h2>
        <table class="data-table order-show__table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if ($item->unit)
                                <small>{{ $item->unit }}</small>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                        <td>₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-cell">No items on this order.</td></tr>
                @endforelse
            </tbody>
        </table>
        <dl class="order-show__totals">
            <div><dt>Subtotal</dt><dd>₹{{ number_format($order->subtotal, 2) }}</dd></div>
            <div><dt>Shipping</dt><dd>{{ $order->shipping_charge > 0 ? '₹'.number_format($order->shipping_charge, 2) : 'FREE' }}</dd></div>
            <div class="order-show__grand"><dt>Total</dt><dd>₹{{ number_format($order->total, 2) }}</dd></div>
        </dl>
    </div>
</div>
@endsection
