<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('stock_quantity')->default(0)->after('price');
            $table->index(['is_active', 'stock_quantity']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('stock_deducted_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('stock_deducted_at');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['is_active', 'stock_quantity']);
            $table->dropColumn('stock_quantity');
        });
    }
};
