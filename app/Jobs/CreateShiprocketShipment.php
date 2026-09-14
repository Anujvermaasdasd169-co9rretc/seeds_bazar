<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\ShiprocketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CreateShiprocketShipment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 45;

    public function __construct(public int $orderId) {}

    public function backoff(): array
    {
        return [30, 120, 300, 900];
    }

    public function handle(ShiprocketService $shiprocket): void
    {
        $order = Order::query()->with(['items', 'shipment'])->find($this->orderId);
        if (! $order || $order->status === 'cancelled') {
            return;
        }

        $shipment = $order->shipment ?: $order->shipment()->create([
            'status' => Shipment::STATUS_PENDING,
            'provider' => 'shiprocket',
        ]);

        if ($shipment->status === Shipment::STATUS_CREATED && filled($shipment->awb)) {
            return;
        }

        if (! $shiprocket->configured()) {
            return;
        }

        $shiprocket->createForOrder($order, $shipment);
    }

    public function failed(?Throwable $exception): void
    {
        $shipment = Shipment::query()->where('order_id', $this->orderId)->first();
        $shipment?->update([
            'status' => Shipment::STATUS_FAILED,
            'last_error' => $exception?->getMessage(),
        ]);
    }
}
