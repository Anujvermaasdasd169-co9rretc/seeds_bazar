<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderCancellationService;
use Illuminate\Console\Command;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid {--minutes=30}';

    protected $description = 'Cancel stale unpaid online orders that were never completed.';

    public function handle(OrderCancellationService $cancellation): int
    {
        $minutes = max(5, (int) $this->option('minutes'));
        $cutoff = now()->subMinutes($minutes);

        $orders = Order::query()
            ->where('payment_method', 'online')
            ->where('status', 'pending')
            ->whereIn('payment_status', ['pending', 'failed'])
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $cancellation->cancel($order, null, 'Unpaid order expired');
            $count++;
        }

        $this->info("Expired {$count} unpaid order(s).");

        return self::SUCCESS;
    }
}
