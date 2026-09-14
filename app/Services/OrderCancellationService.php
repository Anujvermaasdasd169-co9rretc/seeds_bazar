<?php

namespace App\Services;

use App\Exceptions\OrderNotCancellableException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderCancellationService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly OrderService $orders,
        private readonly RazorpayGateway $gateway,
    ) {}

    public function cancel(Order $order, ?int $actorId = null, string $note = 'Order cancelled'): Order
    {
        $refundPaymentId = null;
        $refundAmount = 0;

        $cancelled = DB::transaction(function () use ($order, $actorId, $note, &$refundPaymentId, &$refundAmount): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === 'cancelled') {
                return $lockedOrder;
            }

            if (! in_array($lockedOrder->status, ['pending', 'confirmed', 'processing'], true)) {
                throw new OrderNotCancellableException;
            }

            $from = $lockedOrder->status;
            $this->inventory->restoreForOrder($lockedOrder);

            $updates = ['status' => 'cancelled'];
            if (
                $lockedOrder->payment_method === 'online'
                && $lockedOrder->payment_status === 'paid'
                && filled($lockedOrder->gateway_payment_id)
            ) {
                $refundPaymentId = (string) $lockedOrder->gateway_payment_id;
                $refundAmount = (int) round((float) $lockedOrder->total * 100);
                $updates['payment_status'] = 'refund_pending';
            }

            $lockedOrder->update($updates);
            $this->orders->recordStatus($lockedOrder, $from, 'cancelled', $note, $actorId);

            return $lockedOrder->fresh();
        });

        if ($refundPaymentId) {
            try {
                $this->gateway->refund($refundPaymentId, $refundAmount);
                $cancelled->update(['payment_status' => 'refunded']);
            } catch (Throwable) {
                $cancelled->update(['payment_status' => 'refund_pending']);
            }
        }

        return $cancelled->fresh();
    }
}
