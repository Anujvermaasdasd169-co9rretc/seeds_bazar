<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderNotCancellableException;
use App\Exceptions\OutOfStockException;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\ShippingQuoteRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\OrderCancellationService;
use App\Services\RazorpayGateway;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function store(CheckoutRequest $request, RazorpayGateway $gateway, InventoryService $inventory, ShippingService $shipping): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->integer('address_id'));
        $cart = collect($request->validated('cart'))->keyBy('id');

        $paymentMethod = $request->validated('payment_method');
        if ($paymentMethod === 'online' && ! $gateway->configured()) {
            return back()->withInput()->withErrors(['payment_method' => 'Online payments are not configured for this local environment.']);
        }

        try {
            $order = DB::transaction(function () use ($user, $address, $cart, $paymentMethod, $inventory, $shipping): Order {
                $products = Product::query()
                    ->whereIn('id', $cart->keys())
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($products->count() !== $cart->count()) {
                    throw new OutOfStockException;
                }

                if (! $shipping->serviceable($address)) {
                    abort(422, 'Sorry, delivery is currently unavailable for this pincode.');
                }

                $items = [];
                foreach ($cart as $line) {
                    $product = $products->get((int) $line['id']);
                    $quantity = (int) $line['quantity'];
                    if ($paymentMethod === 'online' && $quantity > $product->stock_quantity) {
                        throw new OutOfStockException;
                    }
                    $lineTotal = (float) $product->price * $quantity;
                    $items[] = compact('product', 'quantity', 'lineTotal');
                }

                $quote = $shipping->quote(
                    $products,
                    collect($items)->map(fn (array $item): array => [
                        'id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                    ]),
                    $address,
                );

                $order = $user->orders()->create([
                    'order_number' => 'SB-'.now()->format('ymd').'-'.strtoupper(Str::random(6)),
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'payment_gateway' => $paymentMethod === 'online' ? 'razorpay' : null,
                    'shipping_name' => $address->full_name,
                    'shipping_phone' => $address->phone,
                    'shipping_address' => collect([
                        $address->address_line_1, $address->address_line_2, $address->landmark,
                        $address->city, $address->state.' - '.$address->postal_code, $address->country,
                    ])->filter()->implode(', '),
                    'shipping_method' => $quote['method'],
                    'delivery_estimate' => $quote['estimate'],
                    'subtotal' => $quote['subtotal'],
                    'shipping_charge' => $quote['shipping'],
                    'total' => $quote['total'],
                ]);

                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'sku' => $item['product']->sku,
                        'unit' => $item['product']->unit,
                        'unit_price' => $item['product']->price,
                        'mrp' => $item['product']->mrp,
                        'discount' => 0,
                        'quantity' => $item['quantity'],
                        'line_total' => $item['lineTotal'],
                    ]);
                }

                if ($paymentMethod === 'cod') {
                    $inventory->deductForOrder($order);
                }

                return $order;
            });
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

        return view('orders.show', ['order' => $order->load('items')]);
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
