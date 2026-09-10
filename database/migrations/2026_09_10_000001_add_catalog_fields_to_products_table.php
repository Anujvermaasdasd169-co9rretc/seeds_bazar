<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('sku', 64)->nullable()->after('name');
            $table->string('slug', 191)->nullable()->after('sku');
            $table->decimal('mrp', 12, 2)->nullable()->after('price');
            $table->string('seo_title', 191)->nullable()->after('is_active');
            $table->string('meta_description', 320)->nullable()->after('seo_title');
            $table->softDeletes();
        });

        $usedSlugs = [];
        DB::table('products')->orderBy('id')->get(['id', 'name'])->each(function (object $product) use (&$usedSlugs): void {
            $base = Str::slug((string) $product->name) ?: 'product';
            $slug = $base;
            $i = 1;
            while (isset($usedSlugs[$slug])) {
                $slug = $base.'-'.$i++;
            }
            $usedSlugs[$slug] = true;

            DB::table('products')->where('id', $product->id)->update([
                'sku' => 'SP'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT),
                'slug' => $slug,
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('sku', 64)->nullable(false)->change();
            $table->string('slug', 191)->nullable(false)->change();
            $table->unique('sku');
            $table->unique('slug');
            $table->index(['is_active', 'deleted_at', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['is_active', 'deleted_at', 'category_id']);
            $table->dropUnique(['sku']);
            $table->dropUnique(['slug']);
            $table->dropColumn(['sku', 'slug', 'mrp', 'seo_title', 'meta_description', 'deleted_at']);
        });
    }
};
