<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Seeds',
                'slug' => 'seeds',
                'emoji' => '🌱',
                'description' => 'Vegetable, flower, herb, fruit and other seeds for home gardens and farms.',
                'show_on_home' => false,
                'children' => [
                    ['name' => 'Organic Seeds', 'slug' => 'organic-seeds', 'emoji' => '🍃', 'description' => 'Open-pollinated and organic seed packs.'],
                    [
                        'name' => 'Vegetable Seeds',
                        'slug' => 'vegetables',
                        'emoji' => '🥦',
                        'description' => 'Kitchen garden vegetables for every season.',
                        'show_on_home' => true,
                        'children' => [
                            ['name' => 'Summer Vegetables Seeds', 'slug' => 'summer-vegetables-seeds', 'description' => 'Heat-loving vegetables for summer sowing.'],
                            ['name' => 'Exotic Vegetables Seeds', 'slug' => 'exotic-vegetables-seeds', 'description' => 'Speciality vegetables for curious growers.'],
                            ['name' => 'Winter Vegetable Seeds', 'slug' => 'winter-vegetable-seeds', 'description' => 'Cool-season vegetables for winter harvests.'],
                            ['name' => 'Rainy Season Vegetables Seeds', 'slug' => 'rainy-season-vegetables-seeds', 'description' => 'Monsoon-ready vegetable varieties.'],
                        ],
                    ],
                    ['name' => 'Herb Seeds', 'slug' => 'herb-seeds', 'emoji' => '🌿', 'description' => 'Culinary and fragrant herbs.'],
                    [
                        'name' => 'Flower Seeds',
                        'slug' => 'flowers',
                        'emoji' => '🌸',
                        'description' => 'Seasonal blooms for pots, beds and borders.',
                        'show_on_home' => true,
                        'children' => [
                            ['name' => 'Winter Flower Seeds', 'slug' => 'winter-flower-seeds'],
                            ['name' => 'Summer Flower Seeds', 'slug' => 'summer-flower-seeds'],
                            ['name' => 'Rainy Season Flower Seeds', 'slug' => 'rainy-season-flower-seeds'],
                        ],
                    ],
                    ['name' => 'Bulbs', 'slug' => 'bulbs', 'emoji' => '🌷'],
                    ['name' => 'Microgreen Seeds', 'slug' => 'microgreen-seeds', 'emoji' => '🥬'],
                    ['name' => 'Fruit Seeds', 'slug' => 'fruits', 'emoji' => '🍉', 'description' => 'Fruit seeds for gardens and pots.'],
                    ['name' => 'Other Seeds', 'slug' => 'grains', 'emoji' => '🌾', 'description' => 'Grains and other field seeds.'],
                    ['name' => 'Seed Kits', 'slug' => 'seed-kits', 'emoji' => '🎁'],
                ],
            ],
            [
                'name' => 'Grow Bags',
                'slug' => 'grow-bags',
                'emoji' => '🪴',
                'description' => 'HDPE, rectangular and geo fabric grow bags.',
                'show_on_home' => true,
                'children' => [
                    ['name' => 'HDPE Grow Bags', 'slug' => 'hdpe-grow-bags'],
                    ['name' => 'Rectangular Grow Bags', 'slug' => 'rectangular-grow-bags'],
                    ['name' => 'Geo Fabric Grow Bags', 'slug' => 'geo-fabric-grow-bags'],
                ],
            ],
            [
                'name' => 'Live Plants',
                'slug' => 'live-plants',
                'emoji' => '🌳',
                'description' => 'Indoor and fruit plants ready to grow on.',
                'show_on_home' => true,
                'children' => [
                    ['name' => 'Indoor Plants', 'slug' => 'indoor-plants'],
                    ['name' => 'Fruit Plants', 'slug' => 'fruit-plants'],
                ],
            ],
            ['name' => 'Soil & Fertiliser', 'slug' => 'soil-fertiliser', 'emoji' => '🪨', 'description' => 'Potting mixes and plant nutrition.', 'show_on_home' => true],
            ['name' => 'Tools', 'slug' => 'tools', 'emoji' => '🛠️', 'description' => 'Garden tools for sowing and care.', 'show_on_home' => true],
            ['name' => 'Bundles', 'slug' => 'bundles', 'emoji' => '📦', 'description' => 'Value combinations for new growers.', 'show_on_home' => true],
        ];

        foreach ($tree as $order => $node) {
            $this->upsertNode($node, null, $order + 1);
        }

        foreach (config('seeds_bazar.categories', []) as $slug => $name) {
            Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'sort_order' => 99,
                    'is_active' => true,
                    'show_in_header' => true,
                    'show_on_home' => false,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function upsertNode(array $node, ?Category $parent, int $order): Category
    {
        $category = Category::updateOrCreate(
            ['slug' => $node['slug']],
            [
                'name' => $node['name'],
                'menu_label' => $node['menu_label'] ?? null,
                'emoji' => $node['emoji'] ?? null,
                'description' => $node['description'] ?? null,
                'parent_id' => $parent?->id,
                'sort_order' => $order,
                'is_active' => true,
                'show_in_header' => $node['show_in_header'] ?? true,
                'show_on_home' => $node['show_on_home'] ?? false,
            ]
        );

        foreach ($node['children'] ?? [] as $index => $child) {
            $this->upsertNode($child, $category, $index + 1);
        }

        return $category;
    }
}
