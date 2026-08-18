<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = config('seeds_bazar.categories', []);
        $order = 0;

        foreach ($categories as $slug => $name) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'sort_order' => $order++,
                    'is_active' => true,
                ]
            );
        }
    }
}
