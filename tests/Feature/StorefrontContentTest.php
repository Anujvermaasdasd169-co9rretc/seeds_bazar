<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_product_has_a_public_detail_page_with_related_products(): void
    {
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Tomato Seeds', 'price' => 120, 'stock_quantity' => 4, 'unit' => '50g pack', 'emoji' => '🌱', 'sowing_season' => 'July–September', 'sunlight' => 'Full sun', 'germination_days' => '6–10 days', 'is_active' => true]);
        Product::create(['category_id' => $category->id, 'name' => 'Chilli Seeds', 'price' => 90, 'stock_quantity' => 3, 'unit' => '25g pack', 'emoji' => '🌱', 'is_active' => true]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Tomato Seeds')
            ->assertSee('Chilli Seeds')
            ->assertSee('July–September')
            ->assertSee('Full sun')
            ->assertSee('Add to cart')
            ->assertSee('header-search', false)
            ->assertSee('cart-toggle', false)
            ->assertSee('Log in');
    }

    public function test_inactive_product_detail_page_is_not_public(): void
    {
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Hidden Seeds', 'price' => 120, 'stock_quantity' => 4, 'unit' => '50g pack', 'emoji' => '🌱', 'is_active' => false]);

        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_all_customer_policy_pages_are_available(): void
    {
        foreach (['policies.shipping', 'policies.returns', 'policies.privacy', 'policies.terms'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_public_sitemap_only_lists_active_products(): void
    {
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables', 'sort_order' => 1, 'is_active' => true]);
        $active = Product::create(['category_id' => $category->id, 'name' => 'Listed Seeds', 'price' => 80, 'stock_quantity' => 2, 'unit' => 'pack', 'emoji' => '🌱', 'is_active' => true]);
        $hidden = Product::create(['category_id' => $category->id, 'name' => 'Hidden Seeds', 'price' => 80, 'stock_quantity' => 2, 'unit' => 'pack', 'emoji' => '🌱', 'is_active' => false]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', $active), false)
            ->assertDontSee(route('products.show', $hidden), false);
    }

    public function test_admin_store_settings_update_the_public_storefront(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'tagline' => 'Seeds selected for thriving home gardens',
            'whatsapp_number' => '911234567890',
            'shipping_flat_rate' => 99,
            'free_shipping_threshold' => 1200,
            'shipping_estimate' => '3–5 business days',
            'shipping_method' => 'Express Garden Delivery',
        ])->assertRedirect();

        $this->assertSame('3–5 business days', Setting::get('shipping_estimate'));
        $this->get(route('shop.index'))->assertSee('Seeds selected for thriving home gardens');
    }
}
