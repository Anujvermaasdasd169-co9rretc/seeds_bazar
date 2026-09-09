<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayGateway
{
    private const BASE_URL = 'https://api.razorpay.com/v1';

    public function configured(): bool
    {
        return filled(config('services.razorpay.key_id')) && filled(config('services.razorpay.key_secret'));
    }

    /** @return array{id: string} */
    public function createOrder(Order $order): array
    {
        $response = $this->client()->post('/orders', [
            'amount' => (int) round((float) $order->total * 100),
            'currency' => 'INR',
            'receipt' => $order->order_number,
            'notes' => ['local_order_id' => (string) $order->id],
        ]);

        if (! $response->successful() || ! filled($response->json('id'))) {
            throw new RuntimeException('Razorpay order creation failed.');
        }

        return ['id' => (string) $response->json('id')];
    }

    /** @return array{amount: int, order_id: string, status: string} */
    public function fetchPayment(string $paymentId): array
    {
        $response = $this->client()->get('/payments/'.rawurlencode($paymentId));

        if (! $response->successful()) {
            throw new RuntimeException('Razorpay payment lookup failed.');
        }

        return [
            'amount' => (int) $response->json('amount'),
            'order_id' => (string) $response->json('order_id'),
            'status' => (string) $response->json('status'),
        ];
    }

    public function verifySignature(string $gatewayOrderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $gatewayOrderId.'|'.$paymentId, (string) config('services.razorpay.key_secret'));

        return hash_equals($expected, $signature);
    }

    private function client(): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('Online payments are not configured for this environment.');
        }

        return Http::baseUrl(self::BASE_URL)
            ->withBasicAuth((string) config('services.razorpay.key_id'), (string) config('services.razorpay.key_secret'))
            ->acceptJson()
            ->asJson()
            ->timeout(10);
    }
}
