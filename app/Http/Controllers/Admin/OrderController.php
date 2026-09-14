<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OrderNotCancellableException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderCancellationService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['user', 'items', 'shipment', 'statusHistories']),
        ]);
    }

    public function update(Request $request, Order $order, OrderCancellationService $cancellation, OrderService $orders): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', Order::STATUSES)]]);
        if ($data['status'] === $order->status) {
            return back()->with('success', 'Order status is unchanged.');
        }
        if ($data['status'] === 'cancelled') {
            return $this->cancel($request, $order, $cancellation);
        }
        if ($order->status === 'cancelled') {
            return back()->withErrors(['status' => 'A cancelled order cannot be reactivated.']);
        }
        if (! $order->canTransitionTo($data['status'])) {
            return back()->withErrors(['status' => 'That status change is not allowed from '.$order->status.'.']);
        }

        $from = $order->status;
        $order->update(['status' => $data['status']]);
        $orders->recordStatus($order, $from, $data['status'], 'Updated by admin', $request->user()?->id);

        return back()->with('success', 'Order status updated.');
    }

    public function cancel(Request $request, Order $order, OrderCancellationService $cancellation): RedirectResponse
    {
        try {
            $cancellation->cancel($order, $request->user()?->id, 'Cancelled by admin');
        } catch (OrderNotCancellableException $exception) {
            return back()->withErrors(['order' => $exception->getMessage()]);
        }

        return back()->with('success', 'Order cancelled successfully.');
    }
}
