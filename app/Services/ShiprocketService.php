<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShiprocketService
{
    public function configured(): bool
    {
        return filled(config('services.shiprocket.email')) && filled(config('services.shiprocket.password'));
    }

    public function createForOrder(Order $order, Shipment $shipment): void
    {
        $shipment->increment('attempts');
        $shipment->forceFill(['last_attempt_at' => now()])->save();

        try {
            $token = $this->token();
            $response = Http::baseUrl('https://apiv2.shiprocket.in/v1/external')
                ->withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post('/orders/create/adhoc', $this->payload($order));

            if (! $response->successful()) {
                throw new RuntimeException('Shiprocket rejected the shipment: '.$response->body());
            }

            $shipment->update([
                'status' => Shipment::STATUS_CREATED,
                'provider_shipment_id' => (string) ($response->json('order_id') ?? $response->json('shipment_id') ?? ''),
                'awb' => $response->json('awb_code'),
                'label_url' => $response->json('label_url'),
                'last_error' => null,
                'meta' => $response->json(),
            ]);
        } catch (Throwable $exception) {
            $shipment->update([
                'status' => Shipment::STATUS_FAILED,
                'last_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function token(): string
    {
        $response = Http::baseUrl('https://apiv2.shiprocket.in/v1/external')
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post('/auth/login', [
                'email' => config('services.shiprocket.email'),
                'password' => config('services.shiprocket.password'),
            ]);

        if (! $response->successful() || ! filled($response->json('token'))) {
            throw new RuntimeException('Shiprocket authentication failed.');
        }

        return (string) $response->json('token');
    }

    /** @return array<string, mixed> */
    private function payload(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            'pickup_location' => config('services.shiprocket.pickup_location', 'Primary'),
            'billing_customer_name' => $order->shipping_name,
            'billing_last_name' => '',
            'billing_address' => $order->shipping_line_1 ?: $order->shipping_address,
            'billing_address_2' => $order->shipping_line_2,
            'billing_city' => $order->shipping_city ?: 'NA',
            'billing_pincode' => $order->shipping_postal_code ?: '000000',
            'billing_state' => $order->shipping_state ?: 'NA',
            'billing_country' => $order->shipping_country ?: 'India',
            'billing_email' => $order->shipping_email ?: $order->guest_email ?: 'orders@seedplanta.com',
            'billing_phone' => preg_replace('/\D+/', '', (string) $order->shipping_phone),
            'shipping_is_billing' => true,
            'order_items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'sku' => $item->sku ?: 'SKU-'.$item->id,
                'units' => $item->quantity,
                'selling_price' => (float) $item->unit_price,
            ])->all(),
            'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'sub_total' => (float) $order->subtotal,
            'length' => 10,
            'breadth' => 10,
            'height' => 10,
            'weight' => 0.5,
        ];
    }
}
