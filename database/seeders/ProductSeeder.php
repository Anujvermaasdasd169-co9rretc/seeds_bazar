<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = config('seeds_bazar.products', []);

        foreach ($items as $item) {
            $category = Category::where('slug', $item['category'])->first();
            if (! $category) {
                continue;
            }

            Product::updateOrCreate(
                [
                    'name' => $item['name'],
                    'category_id' => $category->id,
                ],
                [
                    'description' => $item['description'] ?? null,
                    'price' => $item['price'],
                    'unit' => $item['unit'],
                    'emoji' => $item['emoji'] ?? '🌱',
                    'is_active' => true,
                ]
            );
        }
    }
}
