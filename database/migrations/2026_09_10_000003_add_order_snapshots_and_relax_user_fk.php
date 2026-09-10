<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('guest_email')->nullable()->after('user_id');
            $table->string('guest_phone', 30)->nullable()->after('guest_email');
            $table->string('shipping_email')->nullable()->after('shipping_phone');
            $table->string('shipping_line_1', 255)->nullable()->after('shipping_address');
            $table->string('shipping_line_2', 255)->nullable()->after('shipping_line_1');
            $table->string('shipping_landmark', 150)->nullable()->after('shipping_line_2');
            $table->string('shipping_city', 100)->nullable()->after('shipping_landmark');
            $table->string('shipping_state', 100)->nullable()->after('shipping_city');
            $table->string('shipping_postal_code', 10)->nullable()->after('shipping_state');
            $table->string('shipping_country', 100)->nullable()->after('shipping_postal_code');
            $table->index('guest_email');
            $table->index('guest_phone');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('sku', 64)->nullable()->after('product_name');
            $table->decimal('mrp', 12, 2)->nullable()->after('unit_price');
            $table->decimal('discount', 12, 2)->default(0)->after('mrp');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['sku', 'mrp', 'discount']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['guest_email']);
            $table->dropIndex(['guest_phone']);
            $table->dropColumn([
                'guest_email', 'guest_phone', 'shipping_email',
                'shipping_line_1', 'shipping_line_2', 'shipping_landmark',
                'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country',
            ]);
        });
    }
};
