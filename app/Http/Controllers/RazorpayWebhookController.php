<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderNotCancellableException;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayGateway $gateway, OrderService $orders): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if (! $gateway->verifyWebhook($payload, $signature)) {
            abort(400, 'Invalid webhook signature.');
        }

        if ($request->input('event') !== 'payment.captured') {
            return response()->json(['ok' => true]);
        }

        $entity = $request->input('payload.payment.entity');
        $gatewayOrderId = is_array($entity) ? ($entity['order_id'] ?? null) : null;
        $paymentId = is_array($entity) ? ($entity['id'] ?? null) : null;

        if (! filled($gatewayOrderId) || ! filled($paymentId)) {
            return response()->json(['ok' => true]);
        }

        $order = Order::query()->where('gateway_order_id', $gatewayOrderId)->first();
        if (! $order) {
            return response()->json(['ok' => true]);
        }

        try {
            $orders->confirmPaid($order, (string) $paymentId);
        } catch (OrderNotCancellableException) {
            // Paid after cancel: acknowledge so Razorpay does not retry.
        } catch (Throwable) {
            abort(500, 'Unable to confirm payment.');
        }

        return response()->json(['ok' => true]);
    }
}
