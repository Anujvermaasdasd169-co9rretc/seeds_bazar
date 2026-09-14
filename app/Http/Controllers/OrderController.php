<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderNotCancellableException;
use App\Exceptions\OutOfStockException;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\ShippingQuoteRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderCancellationService;
use App\Services\OrderService;
use App\Services\RazorpayGateway;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function checkout(): View
    {
        return view('checkout.index', ['addresses' => request()->user()->addresses()->latest()->get()]);
    }

    public function quote(ShippingQuoteRequest $request, ShippingService $shipping): array
    {
        $address = $request->user()->addresses()->findOrFail($request->integer('address_id'));
        if (! $shipping->serviceable($address)) {
            abort(422, 'Sorry, delivery is currently unavailable for this pincode.');
        }

        $cart = collect($request->validated('cart'))->keyBy('id');
        $products = Product::query()
            ->whereIn('id', $cart->keys())
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $cart->count()) {
            abort(422, 'Some items are no longer available.');
        }

        return $shipping->quote($products, $cart, $address);
    }

    public function store(CheckoutRequest $request, OrderService $orders, RazorpayGateway $gateway): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->integer('address_id'));
        $cart = collect($request->validated('cart'));
        $paymentMethod = $request->validated('payment_method');

        if ($paymentMethod === 'online' && ! $gateway->configured()) {
            return back()->withInput()->withErrors(['payment_method' => 'Online payments are not configured for this local environment.']);
        }

        try {
            $order = $orders->place($user, $address, $cart, $paymentMethod, $user->email);
        } catch (OutOfStockException $exception) {
            return back()->withInput()->withErrors(['cart' => $exception->getMessage()]);
        }

        if ($paymentMethod === 'online') {
            try {
                $gatewayOrder = $gateway->createOrder($order);
                $order->update(['gateway_order_id' => $gatewayOrder['id']]);

                return redirect()->route('payments.show', $order);
            } catch (Throwable) {
                $order->update([
                    'payment_status' => 'failed',
                    'payment_failure_reason' => 'Payment provider is unavailable.',
                ]);

                return back()->withInput()->withErrors(['payment_method' => 'Online payment is temporarily unavailable. Please try again or choose Cash on Delivery.']);
            }
        }

        return redirect()->route('orders.show', $order);
    }

    public function index(): View
    {
        return view('orders.index', ['orders' => request()->user()->orders()->latest()->paginate(10)]);
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === request()->user()->id, 404);

        return view('orders.show', ['order' => $order->load(['items', 'shipment'])]);
    }

    public function cancel(Order $order, OrderCancellationService $cancellation): RedirectResponse
    {
        abort_unless($order->user_id === request()->user()->id, 404);

        try {
            $cancellation->cancel($order);
        } catch (OrderNotCancellableException $exception) {
            return back()->withErrors(['order' => $exception->getMessage()]);
        }

        return back()->with('status', 'Order cancelled successfully.');
    }
}
