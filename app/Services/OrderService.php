<?php

namespace App\Services;

use App\Exceptions\OrderNotCancellableException;
use App\Exceptions\OutOfStockException;
use App\Jobs\CreateShiprocketShipment;
use App\Mail\OrderPlacedMail;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly ShippingService $shipping,
        private readonly CartService $carts,
        private readonly RazorpayGateway $gateway,
    ) {}

    /**
     * @param  Collection<int, array{id:int,quantity:int}>  $cart
     */
    public function place(?User $user, Address $address, Collection $cart, string $paymentMethod, ?string $email = null): Order
    {
        $order = DB::transaction(function () use ($user, $address, $cart, $paymentMethod, $email): Order {
            $cart = $cart->keyBy(fn (array $line): int => (int) $line['id']);
            $products = Product::query()
                ->whereIn('id', $cart->keys())
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $cart->count()) {
                throw new OutOfStockException;
            }

            if (! $this->shipping->serviceable($address)) {
                abort(422, 'Sorry, delivery is currently unavailable for this pincode.');
            }

            $items = [];
            foreach ($cart as $line) {
                $product = $products->get((int) $line['id']);
                $quantity = (int) $line['quantity'];
                if ($quantity < 1 || $quantity > $product->stock_quantity) {
                    throw new OutOfStockException;
                }
                $unit = (float) $product->price;
                $mrp = $product->mrp !== null ? (float) $product->mrp : $unit;
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'lineTotal' => $unit * $quantity,
                    'discount' => max(0, ($mrp - $unit) * $quantity),
                ];
            }

            $quote = $this->shipping->quote(
                $products,
                collect($items)->map(fn (array $item): array => [
                    'id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                ]),
                $address,
            );

            $formattedAddress = collect([
                $address->address_line_1, $address->address_line_2, $address->landmark,
                $address->city, $address->state.' - '.$address->postal_code, $address->country,
            ])->filter()->implode(', ');

            $order = new Order([
                'order_number' => $this->nextOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_gateway' => $paymentMethod === 'online' ? 'razorpay' : null,
                'guest_email' => $user ? null : $email,
                'guest_phone' => $user ? null : $address->phone,
                'shipping_name' => $address->full_name,
                'shipping_phone' => $address->phone,
                'shipping_email' => $email ?: $user?->email,
                'shipping_address' => $formattedAddress,
                'shipping_line_1' => $address->address_line_1,
                'shipping_line_2' => $address->address_line_2,
                'shipping_landmark' => $address->landmark,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
                'shipping_postal_code' => $address->postal_code,
                'shipping_country' => $address->country ?: 'India',
                'shipping_method' => $quote['method'],
                'delivery_estimate' => $quote['estimate'],
                'subtotal' => $quote['subtotal'],
                'shipping_charge' => $quote['shipping'],
                'total' => $quote['total'],
            ]);
            $order->user_id = $user?->id;
            $order->save();

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'sku' => $item['product']->sku,
                    'unit' => $item['product']->unit,
                    'unit_price' => $item['product']->price,
                    'mrp' => $item['product']->mrp,
                    'discount' => $item['discount'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['lineTotal'],
                ]);
            }

            $this->recordStatus($order, null, 'pending', 'Order placed');
            $order->shipment()->create([
                'status' => Shipment::STATUS_PENDING,
                'provider' => 'shiprocket',
            ]);

            if ($paymentMethod === 'cod') {
                $this->inventory->deductForOrder($order);
            }

            return $order->load('items');
        });

        $this->afterPlace($order);

        return $order;
    }

    public function confirmPaid(Order $order, string $paymentId, ?string $checkoutSignature = null): Order
    {
        $payment = $this->gateway->fetchPayment($paymentId);
        $valid = $payment['order_id'] === $order->gateway_order_id
            && $payment['amount'] === (int) round((float) $order->total * 100)
            && $payment['status'] === 'captured';

        if ($checkoutSignature !== null) {
            $valid = $valid && $this->gateway->verifySignature(
                (string) $order->gateway_order_id,
                $paymentId,
                $checkoutSignature,
            );
        }

        if (! $valid) {
            throw new RuntimeException('Invalid payment verification.');
        }

        $alreadyPaid = false;
        $confirmed = DB::transaction(function () use ($order, $paymentId, $checkoutSignature, &$alreadyPaid): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_status === 'paid') {
                $alreadyPaid = true;

                return $lockedOrder;
            }
            if ($lockedOrder->status === 'cancelled') {
                throw new OrderNotCancellableException('Cancelled orders cannot be paid.');
            }

            $from = $lockedOrder->status;
            $this->inventory->deductForOrder($lockedOrder);
            $lockedOrder->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'gateway_payment_id' => $paymentId,
                'gateway_signature' => $checkoutSignature,
                'paid_at' => now(),
                'payment_failure_reason' => null,
            ]);
            if ($from !== 'confirmed') {
                $this->recordStatus($lockedOrder, $from, 'confirmed', 'Payment captured');
            }

            return $lockedOrder->fresh('items');
        });

        if (! $alreadyPaid) {
            DB::afterCommit(fn () => $this->notifyAndFulfill($confirmed));
        }

        return $confirmed;
    }

    public function recordStatus(Order $order, ?string $from, string $to, ?string $note = null, ?int $actorId = null): void
    {
        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'created_by' => $actorId,
        ]);
    }

    public function nextOrderNumber(): string
    {
        $row = DB::table('order_sequences')->where('name', 'orders')->lockForUpdate()->first();
        $number = (int) ($row->next_number ?? 100001);
        DB::table('order_sequences')->updateOrInsert(['name' => 'orders'], ['next_number' => $number + 1]);

        return 'SP'.$number;
    }

    private function afterPlace(Order $order): void
    {
        DB::afterCommit(function () use ($order): void {
            try {
                $this->carts->clear($this->carts->current());
            } catch (\Throwable) {
                // Cart persistence is optional until API routes are enabled.
            }

            if ($order->payment_method === 'cod') {
                $this->notifyAndFulfill($order);
            }
        });
    }

    private function notifyAndFulfill(Order $order): void
    {
        $order->loadMissing(['items', 'user']);
        $email = $order->shipping_email ?: $order->guest_email ?: $order->user?->email;
        if (filled($email)) {
            try {
                $mail = Mail::to($email);
                $admin = config('seeds_bazar.admin_email');
                if (filled($admin) && strcasecmp((string) $admin, (string) $email) !== 0) {
                    $mail->bcc((string) $admin);
                }
                $mail->queue(new OrderPlacedMail($order));
            } catch (\Throwable) {
                // Order must not fail because mail is misconfigured.
            }
        }

        CreateShiprocketShipment::dispatch($order->id);
    }
}
