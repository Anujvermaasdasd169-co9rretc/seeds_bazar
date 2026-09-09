@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<section class="admin-page"><div class="page-heading"><div><p class="eyebrow">Commerce</p><h1>Orders</h1></div></div>
<div class="table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead><tbody>@forelse ($orders as $order)<tr><td>{{ $order->order_number }}<small>{{ $order->created_at->format('d M Y') }}</small></td><td>{{ $order->user->name }}<small>{{ $order->user->email }}</small></td><td>Rs {{ number_format($order->total, 2) }}</td><td>{{ $order->payment_method === 'online' ? 'Online' : 'COD' }}<small>{{ ucfirst($order->payment_status) }}</small></td><td>{{ ucfirst($order->status) }}</td><td><a class="btn btn--small" href="{{ route('admin.orders.show', $order) }}">View</a></td></tr>@empty<tr><td colspan="6">No orders yet.</td></tr>@endforelse</tbody></table></div>{{ $orders->links() }}</section>
@endsection