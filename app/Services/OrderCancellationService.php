<?php

namespace App\Services;

use App\Exceptions\OrderNotCancellableException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderCancellationService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === 'cancelled') {
                return $lockedOrder;
            }

            if (! in_array($lockedOrder->status, ['pending', 'confirmed', 'processing'], true)) {
                throw new OrderNotCancellableException;
            }

            $this->inventory->restoreForOrder($lockedOrder);
            $lockedOrder->update(['status' => 'cancelled']);

            return $lockedOrder->fresh();
        });
    }
}
