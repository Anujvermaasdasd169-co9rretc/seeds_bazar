@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="page-header page-header--row">
    <div>
        <h1>Orders</h1>
        <p>Customer orders from checkout.</p>
    </div>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>
                        <strong>{{ $order->order_number }}</strong>
                        <small>{{ $order->created_at->format('d M Y, h:i A') }}</small>
                    </td>
                    <td>
                        {{ $order->user->name }}
                        <small>{{ $order->user->email }}</small>
                    </td>
                    <td>₹{{ number_format($order->total, 2) }}</td>
                    <td>
                        {{ $order->payment_method === 'online' ? 'Online' : 'COD' }}
                        <small>
                            @if ($order->payment_status === 'paid')
                                <span class="badge badge--green">Paid</span>
                            @else
                                <span class="badge badge--gray">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </small>
                    </td>
                    <td>
                        @if (in_array($order->status, ['cancelled', 'pending'], true))
                            <span class="badge badge--gray">{{ ucfirst($order->status) }}</span>
                        @else
                            <span class="badge badge--green">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                    <td class="cell-actions">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn--sm btn--outline">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-cell">No orders yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $orders->links('vendor.pagination.admin') }}
@endsection
