<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderNotCancellableException;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private readonly RazorpayGateway $gateway,
        private readonly OrderService $orders,
    ) {}

    public function show(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->status === 'cancelled') {
            return redirect()->route('orders.show', $order)->withErrors(['payment' => 'Cancelled orders cannot be paid.']);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        return view('checkout.payment', [
            'order' => $order,
            'razorpayKeyId' => config('services.razorpay.key_id'),
        ]);
    }

    public function verify(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:80'],
            'razorpay_signature' => ['required', 'string', 'max:128'],
        ]);

        if ($order->status === 'cancelled') {
            return redirect()->route('orders.show', $order)->withErrors(['payment' => 'Cancelled orders cannot be paid.']);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        try {
            $this->orders->confirmPaid($order, $data['razorpay_payment_id'], $data['razorpay_signature']);

            return redirect()->route('orders.show', $order)->with('status', 'Payment verified and order confirmed.');
        } catch (Throwable $exception) {
            $order->refresh();
            if ($order->status === 'cancelled' || $exception instanceof OrderNotCancellableException) {
                return redirect()->route('orders.show', $order)->withErrors(['payment' => 'Cancelled orders cannot be paid.']);
            }
            $order->update(['payment_status' => 'failed', 'payment_failure_reason' => 'Payment verification failed.']);

            return redirect()->route('payments.show', $order)->withErrors(['payment' => 'We could not verify this payment. You can retry safely.']);
        }
    }

    public function fail(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        if ($order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed', 'payment_failure_reason' => 'Payment was cancelled or not completed.']);
        }

        return redirect()->route('payments.show', $order)->withErrors(['payment' => 'Payment was not completed. You can retry this order.']);
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === request()->user()->id, 404);
        abort_unless($order->payment_method === 'online', 422);
    }
}
