<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;

class InventoryService
{
    public function deductForOrder(Order $order): void
    {
        $order->loadMissing('items');

        if ($order->stock_deducted_at) {
            return;
        }

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                throw new OutOfStockException;
            }

            $product = Product::query()
                ->whereKey($item->product_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $product || $product->stock_quantity < $item->quantity) {
                throw new OutOfStockException;
            }

            $before = (int) $product->stock_quantity;
            $product->decrement('stock_quantity', $item->quantity);

            $this->log(
                $product,
                InventoryLog::TYPE_ORDER,
                -1 * (int) $item->quantity,
                $before,
                $before - (int) $item->quantity,
                Order::class,
                $order->id,
                $order->user_id
            );
        }

        $order->forceFill(['stock_deducted_at' => now()])->save();
    }

    public function restoreForOrder(Order $order): void
    {
        if (! $order->stock_deducted_at) {
            return;
        }

        $order->loadMissing('items');
        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
            if (! $product) {
                continue;
            }

            $before = (int) $product->stock_quantity;
            $product->increment('stock_quantity', $item->quantity);

            $this->log(
                $product,
                InventoryLog::TYPE_CANCELLATION,
                (int) $item->quantity,
                $before,
                $before + (int) $item->quantity,
                Order::class,
                $order->id,
                $order->user_id
            );
        }

        $order->forceFill(['stock_deducted_at' => null])->save();
    }

    private function log(
        Product $product,
        string $changeType,
        int $quantity,
        int $before,
        int $after,
        ?string $referenceType,
        ?int $referenceId,
        ?int $createdBy,
    ): void {
        InventoryLog::query()->create([
            'product_id' => $product->id,
            'change_type' => $changeType,
            'quantity' => $quantity,
            'before_stock' => $before,
            'after_stock' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => $createdBy,
        ]);
    }
}
