<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
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
            $affected = Product::query()
                ->whereKey($item->product_id)
                ->where('is_active', true)
                ->where('stock_quantity', '>=', $item->quantity)
                ->decrement('stock_quantity', $item->quantity);

            if ($affected !== 1) {
                throw new OutOfStockException;
            }
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
            Product::query()->whereKey($item->product_id)->lockForUpdate()->increment('stock_quantity', $item->quantity);
        }
    }
}
