<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\RazorpayGateway;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_customer_can_create_a_cod_order_using_server_prices(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cod',
            'cart' => json_encode([['id' => $product->id, 'quantity' => 2, 'price' => 1]]),
        ]);

        $order = Order::first();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('319.00', (string) $order->total);
        $this->assertSame('79.00', (string) $order->shipping_charge);
        $this->assertSame('5-7 business days', $order->delivery_estimate);
        $this->assertSame('240.00', (string) $order->items->first()->line_total);
        $this->assertSame('Tomato Seeds', $order->items->first()->product_name);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertNotNull($order->fresh()->stock_deducted_at);
        $this->assertNotEmpty($product->fresh()->slug);
        $this->assertNotEmpty($product->fresh()->sku);
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'change_type' => 'order',
            'quantity' => -2,
            'before_stock' => 5,
            'after_stock' => 3,
            'reference_id' => $order->id,
        ]);
    }

    public function test_checkout_rejects_inactive_products(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $product->update(['is_active' => false]);

        $this->actingAs($user)->from(route('checkout'))->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'cod',
            'cart' => [['id' => $product->id, 'quantity' => 1]],
        ])->assertRedirect(route('checkout'))->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_quantity_greater_than_stock_without_creating_order(): void
    {
        [$user, $address, $product] = $this->commerceFixtures(stock: 2);

        $this->actingAs($user)->from(route('checkout'))->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'cod',
            'cart' => [['id' => $product->id, 'quantity' => 3]],
        ])->assertRedirect(route('checkout'))->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_two_orders_cannot_consume_the_same_last_unit(): void
    {
        [$user, $address, $product] = $this->commerceFixtures(stock: 1);
        $payload = [
            'address_id' => $address->id, 'payment_method' => 'cod',
            'cart' => [['id' => $product->id, 'quantity' => 1]],
        ];

        $this->actingAs($user)->post(route('checkout.store'), $payload)->assertRedirect();
        $this->actingAs($user)->from(route('checkout'))->post(route('checkout.store'), $payload)
            ->assertRedirect(route('checkout'))->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    public function test_customer_cannot_use_another_customers_address_or_view_their_order(): void
    {
        [$owner, $address, $product] = $this->commerceFixtures();
        $other = User::factory()->create();
        $otherAddress = $other->addresses()->create([
            'full_name' => 'Other', 'phone' => '+919876543211', 'address_line_1' => 'Other Road',
            'city' => 'Pune', 'state' => 'Maharashtra', 'postal_code' => '411002', 'country' => 'India',
        ]);
        $order = $owner->orders()->create([
            'order_number' => 'SB-TEST-1', 'shipping_name' => 'Owner', 'shipping_phone' => '+919876543210',
            'shipping_address' => 'Private address', 'subtotal' => 10, 'total' => 10,
        ]);

        $this->actingAs($other)->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'cod',
            'cart' => [['id' => $product->id, 'quantity' => 1]],
        ])->assertNotFound();
        $this->actingAs($other)->get(route('orders.show', $order))->assertNotFound();
        $this->assertNotSame($address->id, $otherAddress->id);
    }

    public function test_only_admin_can_update_order_status(): void
    {
        [$user] = $this->commerceFixtures();
        $order = $user->orders()->create([
            'order_number' => 'SB-TEST-2', 'shipping_name' => 'Owner', 'shipping_phone' => '+919876543210',
            'shipping_address' => 'Address', 'subtotal' => 10, 'total' => 10,
        ]);

        $this->actingAs($user)->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect();
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_admin_stock_validation_rejects_negative_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Flowers', 'slug' => 'flowers', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($admin)->from(route('admin.products.create'))->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Invalid Stock Product',
            'price' => 50,
            'stock_quantity' => -1,
            'unit' => 'pack',
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.create'))->assertSessionHasErrors('stock_quantity');
    }

    public function test_online_checkout_uses_server_total_and_stores_pending_gateway_order(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $gateway = $this->mock(RazorpayGateway::class);
        $gateway->shouldReceive('configured')->once()->andReturn(true);
        $gateway->shouldReceive('createOrder')->once()->withArgs(function (Order $order): bool {
            return (string) $order->total === '319.00' && $order->payment_method === 'online';
        })->andReturn(['id' => 'order_rzp_test']);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'online',
            'cart' => json_encode([['id' => $product->id, 'quantity' => 2, 'price' => 1]]),
        ]);

        $order = Order::first();
        $response->assertRedirect(route('payments.show', $order));
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('order_rzp_test', $order->gateway_order_id);
        $this->assertSame('319.00', (string) $order->total);
        $this->assertSame('79.00', (string) $order->shipping_charge);
    }

    public function test_online_checkout_fails_safely_when_gateway_is_not_configured(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $gateway = $this->mock(RazorpayGateway::class);
        $gateway->shouldReceive('configured')->once()->andReturn(false);

        $this->actingAs($user)->from(route('checkout'))->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'online',
            'cart' => [['id' => $product->id, 'quantity' => 1]],
        ])->assertRedirect(route('checkout'))->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_shipping_is_free_at_and_above_the_configured_threshold(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $shipping = app(ShippingService::class);

        foreach ([998 => 79.0, 999 => 0.0, 1000 => 0.0] as $subtotal => $expected) {
            $product->update(['price' => $subtotal, 'stock_quantity' => 10]);
            $quote = $shipping->quote(collect([$product])->keyBy('id'), collect([['id' => $product->id, 'quantity' => 1]]), $address);
            $this->assertSame($expected, $quote['shipping']);
            $this->assertSame($subtotal + $expected, $quote['total']);
        }
    }

    public function test_configured_unserviceable_pincode_is_rejected(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        config(['seeds_bazar.shipping.serviceable_pincodes' => '400001,110001']);

        $this->actingAs($user)->from(route('checkout'))->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'cod',
            'cart' => [['id' => $product->id, 'quantity' => 1]],
        ])->assertStatus(422);
    }

    public function test_valid_payment_verification_marks_order_paid(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        $gateway = $this->mock(RazorpayGateway::class);
        $gateway->shouldReceive('fetchPayment')->with('pay_test')->once()->andReturn([
            'amount' => 24000, 'order_id' => 'order_test', 'status' => 'captured',
        ]);
        $gateway->shouldReceive('verifySignature')->with('order_test', 'pay_test', 'sig_test')->once()->andReturn(true);

        $this->actingAs($user)->post(route('payments.verify', $order), [
            'razorpay_payment_id' => 'pay_test', 'razorpay_signature' => 'sig_test',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertNotNull($order->fresh()->stock_deducted_at);
    }

    public function test_invalid_payment_signature_marks_payment_failed(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        $gateway = $this->mock(RazorpayGateway::class);
        $gateway->shouldReceive('fetchPayment')->once()->andReturn([
            'amount' => 24000, 'order_id' => 'order_test', 'status' => 'captured',
        ]);
        $gateway->shouldReceive('verifySignature')->once()->andReturn(false);

        $this->actingAs($user)->post(route('payments.verify', $order), [
            'razorpay_payment_id' => 'pay_test', 'razorpay_signature' => 'bad_sig',
        ])->assertRedirect(route('payments.show', $order));

        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_duplicate_payment_verification_is_idempotent(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        $gateway = $this->mock(RazorpayGateway::class);
        $gateway->shouldReceive('fetchPayment')->once()->andReturn([
            'amount' => 24000, 'order_id' => 'order_test', 'status' => 'captured',
        ]);
        $gateway->shouldReceive('verifySignature')->once()->andReturn(true);

        $payload = ['razorpay_payment_id' => 'pay_test', 'razorpay_signature' => 'sig_test'];
        $this->actingAs($user)->post(route('payments.verify', $order), $payload);
        $this->assertSame('paid', $order->fresh()->payment_status);

        $this->actingAs($user)->post(route('payments.verify', $order), $payload)
            ->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_customer_cannot_verify_another_customers_payment(): void
    {
        [$owner, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($owner, $address, $product);
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('payments.verify', $order), [
            'razorpay_payment_id' => 'pay_test', 'razorpay_signature' => 'sig_test',
        ])->assertNotFound();
    }

    public function test_cod_cancellation_restores_stock_once(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        $order->update(['payment_method' => 'cod', 'payment_gateway' => null]);
        app(InventoryService::class)->deductForOrder($order);
        $this->assertSame(3, $product->fresh()->stock_quantity);

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock_quantity);

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertRedirect();
        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'change_type' => 'cancellation',
            'quantity' => 2,
        ]);
    }

    public function test_pending_online_cancellation_does_not_restore_stock(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_paid_online_cancellation_restores_stock_without_marking_refunded(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        app(InventoryService::class)->deductForOrder($order);
        $order->update(['payment_status' => 'paid', 'status' => 'confirmed', 'paid_at' => now()]);

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        [$owner, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($owner, $address, $product);
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('orders.cancel', $order))->assertNotFound();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_shipped_order_cannot_be_cancelled(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        $order->update(['status' => 'shipped']);

        $this->actingAs($user)->from(route('orders.show', $order))->post(route('orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order))->assertSessionHasErrors('order');
        $this->assertSame('shipped', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_cancelled_order_cannot_be_paid_or_reactivated(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        $order->update(['status' => 'cancelled']);
        $gateway = $this->mock(RazorpayGateway::class);
        $gateway->shouldReceive('fetchPayment')->never();

        $this->actingAs($user)->post(route('payments.verify', $order), [
            'razorpay_payment_id' => 'pay_test', 'razorpay_signature' => 'sig_test',
        ])->assertRedirect(route('orders.show', $order));
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect()->assertSessionHasErrors('status');
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_admin_can_cancel_an_eligible_order(): void
    {
        [$user, $address, $product] = $this->commerceFixtures();
        $order = $this->createOnlineOrder($user, $address, $product);
        app(InventoryService::class)->deductForOrder($order);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.orders.cancel', $order))->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    /** @return array{User, Address, Product} */
    private function commerceFixtures(int $stock = 5): array
    {
        $user = User::factory()->create();
        $address = $user->addresses()->create([
            'full_name' => 'Customer', 'phone' => '+919876543210',
            'address_line_1' => '1 Garden Road', 'city' => 'Pune', 'state' => 'Maharashtra',
            'postal_code' => '411001', 'country' => 'India', 'is_default' => true,
        ]);
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Tomato Seeds', 'description' => 'Seeds',
            'price' => 120, 'stock_quantity' => $stock, 'unit' => '50g pack', 'emoji' => 'T', 'is_active' => true,
        ]);

        return [$user, $address, $product];
    }

    private function createOnlineOrder(User $user, Address $address, Product $product): Order
    {
        $order = $user->orders()->create([
            'order_number' => 'SB-ONLINE-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'online',
            'payment_gateway' => 'razorpay', 'gateway_order_id' => 'order_test',
            'shipping_name' => $address->full_name, 'shipping_phone' => $address->phone,
            'shipping_address' => $address->address_line_1, 'subtotal' => 240,
            'total' => 240,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'unit_price' => $product->price,
            'quantity' => 2,
            'line_total' => 240,
        ]);

        return $order;
    }
}
