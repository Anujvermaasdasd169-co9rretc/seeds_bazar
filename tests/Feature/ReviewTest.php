<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_review_is_pending_and_not_published_until_approved(): void
    {
        $product = $this->product();

        $this->post(route('reviews.store'), [
            'product_id' => $product->id,
            'name' => 'A Customer',
            'rating' => 5,
            'comment' => 'Fresh seeds and strong growth.',
        ])->assertRedirect(route('shop.index'));

        $review = Review::first();
        $this->assertSame('pending', $review->status);
        $this->get(route('shop.index'))->assertDontSee('Fresh seeds and strong growth.');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.reviews.update', $review), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertSame('approved', $review->fresh()->status);
    }

    public function test_reviews_cannot_be_submitted_for_inactive_products(): void
    {
        $product = $this->product();
        $product->update(['is_active' => false]);

        $this->post(route('reviews.store'), [
            'product_id' => $product->id, 'name' => 'Customer', 'rating' => 4, 'comment' => 'Unavailable',
        ])->assertStatus(422);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_only_admin_can_moderate_reviews(): void
    {
        $review = Review::create([
            'product_id' => $this->product()->id, 'name' => 'Customer', 'rating' => 4,
            'comment' => 'Useful review', 'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.reviews.update', $review), ['status' => 'approved'])
            ->assertForbidden();
        $this->assertSame('pending', $review->fresh()->status);
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables', 'sort_order' => 1, 'is_active' => true]);

        return Product::create([
            'category_id' => $category->id, 'name' => 'Tomato Seeds', 'price' => 120,
            'stock_quantity' => 5, 'unit' => 'pack', 'emoji' => 'T', 'is_active' => true,
        ]);
    }
}
