<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_gateway', 30)->nullable()->after('payment_method');
            $table->string('gateway_order_id', 80)->nullable()->unique()->after('payment_gateway');
            $table->string('gateway_payment_id', 80)->nullable()->unique()->after('gateway_order_id');
            $table->string('gateway_signature', 128)->nullable()->after('gateway_payment_id');
            $table->timestamp('paid_at')->nullable()->after('gateway_signature');
            $table->string('payment_failure_reason', 255)->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['gateway_order_id']);
            $table->dropUnique(['gateway_payment_id']);
            $table->dropColumn([
                'payment_gateway', 'gateway_order_id', 'gateway_payment_id',
                'gateway_signature', 'paid_at', 'payment_failure_reason',
            ]);
        });
    }
};
