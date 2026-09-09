<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_nested_header_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $seeds = Category::create(['name' => 'Seeds', 'slug' => 'seeds', 'sort_order' => 1, 'is_active' => true, 'show_in_header' => true]);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Vegetable Seeds',
            'parent_id' => $seeds->id,
            'show_in_header' => '1',
        ])->assertRedirect();

        $vegetables = Category::where('slug', 'vegetable-seeds')->first();
        $this->assertNotNull($vegetables);
        $this->assertSame($seeds->id, $vegetables->parent_id);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Winter Vegetable Seeds',
            'parent_id' => $vegetables->id,
            'show_in_header' => '1',
        ])->assertRedirect();

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('header__top', false)
            ->assertSee('header-nav', false)
            ->assertSee('Seeds')
            ->assertSee('Vegetable Seeds')
            ->assertSee('Winter Vegetable Seeds')
            ->assertDontSee('Blog');
    }

    public function test_fourth_category_level_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $one = Category::create(['name' => 'Seeds', 'slug' => 'seeds', 'sort_order' => 1, 'is_active' => true]);
        $two = Category::create(['name' => 'Vegetable Seeds', 'slug' => 'vegetables', 'parent_id' => $one->id, 'sort_order' => 1, 'is_active' => true]);
        $three = Category::create(['name' => 'Winter', 'slug' => 'winter', 'parent_id' => $two->id, 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($admin)->from(route('admin.categories.index'))->post(route('admin.categories.store'), [
            'name' => 'Too Deep',
            'parent_id' => $three->id,
        ])->assertRedirect(route('admin.categories.index'))->assertSessionHasErrors('parent_id');
    }

    public function test_header_hides_inactive_categories_and_keeps_products_filterable_by_parent(): void
    {
        $seeds = Category::create(['name' => 'Seeds', 'slug' => 'seeds', 'sort_order' => 1, 'is_active' => true, 'show_in_header' => true, 'show_on_home' => false]);
        $vegetables = Category::create(['name' => 'Vegetable Seeds', 'slug' => 'vegetables', 'parent_id' => $seeds->id, 'sort_order' => 1, 'is_active' => true, 'show_in_header' => true, 'show_on_home' => true]);
        $hidden = Category::create(['name' => 'Hidden Branch', 'slug' => 'hidden-branch', 'parent_id' => $seeds->id, 'sort_order' => 2, 'is_active' => false, 'show_in_header' => true]);
        Product::create(['category_id' => $vegetables->id, 'name' => 'Tomato Seeds', 'price' => 120, 'stock_quantity' => 4, 'unit' => '50g pack', 'emoji' => '🌱', 'is_active' => true]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Seeds')
            ->assertSee('Vegetable Seeds')
            ->assertSee('Tomato Seeds')
            ->assertSee('Top Categories')
            ->assertDontSee('Hidden Branch');

        $this->get(route('shop.category', $seeds))
            ->assertOk()
            ->assertSee('Tomato Seeds');
    }

    public function test_inactive_category_page_is_not_public(): void
    {
        $category = Category::create(['name' => 'Hidden', 'slug' => 'hidden', 'sort_order' => 1, 'is_active' => false, 'show_in_header' => true]);

        $this->get(route('shop.category', $category))->assertNotFound();
    }

    public function test_category_page_shows_admin_description_and_menu_label(): void
    {
        $seeds = Category::create([
            'name' => 'Seeds Collection',
            'menu_label' => 'Seeds',
            'slug' => 'seeds',
            'description' => 'Every seed pack in one place.',
            'sort_order' => 1,
            'is_active' => true,
            'show_in_header' => true,
        ]);
        $vegetables = Category::create([
            'name' => 'Vegetable Seeds',
            'slug' => 'vegetables',
            'parent_id' => $seeds->id,
            'description' => 'Kitchen garden vegetables.',
            'sort_order' => 1,
            'is_active' => true,
            'show_in_header' => true,
        ]);
        Product::create(['category_id' => $vegetables->id, 'name' => 'Tomato Seeds', 'price' => 120, 'stock_quantity' => 4, 'unit' => '50g pack', 'emoji' => '🌱', 'is_active' => true]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Seeds')
            ->assertDontSee('Seeds Collection');

        $this->get(route('shop.category', $seeds))
            ->assertOk()
            ->assertSee('Seeds Collection')
            ->assertSee('Every seed pack in one place.')
            ->assertSee('Vegetable Seeds')
            ->assertSee('Tomato Seeds')
            ->assertDontSee('Grow your next');
    }

    public function test_growing_guide_link_can_be_hidden_from_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'tagline' => 'Quality seeds for your farm & garden',
            'whatsapp_number' => '919876543210',
            'shipping_flat_rate' => 79,
            'free_shipping_threshold' => 999,
            'shipping_estimate' => '5-7 business days',
            'shipping_method' => 'Standard Delivery',
            '_storefront' => '1',
            'header_guide_label' => '',
            'header_show_home' => '1',
            'header_show_contact' => '1',
            'header_show_account' => '1',
        ])->assertRedirect();

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertDontSee('Growing guide');
    }

    public function test_admin_can_reorder_sibling_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $first = Category::create(['name' => 'Seeds', 'slug' => 'seeds', 'sort_order' => 1, 'is_active' => true, 'show_in_header' => true]);
        $second = Category::create(['name' => 'Tools', 'slug' => 'tools', 'sort_order' => 2, 'is_active' => true, 'show_in_header' => true]);

        $this->actingAs($admin)->patch(route('admin.categories.move', $second), [
            'direction' => 'up',
        ])->assertRedirect();

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
    }
}
