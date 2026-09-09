<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $products = Product::with(['category', 'reviews' => fn ($query) => $query->where('status', 'approved')])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->catalogProduct($product));

        $categoryLabels = $categories->pluck('name', 'slug')->all();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categoryLabels,
            'currency' => config('seeds_bazar.currency'),
            'tagline' => Setting::get('tagline', config('seeds_bazar.tagline')),
            'whatsappNumber' => Setting::get('whatsapp_number', config('seeds_bazar.whatsapp_number')),
            'shippingEstimate' => Setting::get('shipping_estimate', config('seeds_bazar.shipping.estimate')),
            'reviews' => Review::with('product')
                ->where('status', 'approved')
                ->whereHas('product', fn ($query) => $query->where('is_active', true))
                ->latest()
                ->get(),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load([
            'category',
            'reviews' => fn ($query) => $query->where('status', 'approved')->latest(),
        ]);

        abort_unless($product->is_active && $product->category?->is_active, 404);

        $related = Product::with(['category', 'reviews' => fn ($query) => $query->where('status', 'approved')])
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->orderBy('name')
            ->take(4)
            ->get()
            ->map(fn (Product $relatedProduct) => $this->catalogProduct($relatedProduct));

        return view('shop.show', [
            'product' => $this->catalogProduct($product),
            'relatedProducts' => $related,
            'currency' => config('seeds_bazar.currency'),
            'whatsappNumber' => Setting::get('whatsapp_number', config('seeds_bazar.whatsapp_number')),
            'shippingEstimate' => Setting::get('shipping_estimate', config('seeds_bazar.shipping.estimate')),
            'shippingMethod' => Setting::get('shipping_method', config('seeds_bazar.shipping.method')),
        ]);
    }

    public function storeReview(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'name' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        abort_unless(Product::query()->whereKey($validated['product_id'])->where('is_active', true)->exists(), 422, 'This product is not available for review.');

        Review::create($validated + [
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('shop.index')
            ->with('review_success', 'Thank you! Your review was submitted for moderation.');
    }

    /** @return array<string, mixed> */
    private function catalogProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->slug,
            'category_name' => $product->category->name,
            'price' => (float) $product->price,
            'stock_quantity' => $product->stock_quantity,
            'in_stock' => $product->stock_quantity > 0,
            'unit' => $product->unit,
            'description' => $product->description ?? '',
            'emoji' => $product->emoji,
            'image' => $product->image_url,
            'review_count' => $product->reviews->count(),
            'review_rating' => $product->reviews->count() ? round($product->reviews->avg('rating'), 1) : null,
            'reviews' => $product->reviews->map(fn ($review) => [
                'name' => $review->name,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
            ])->values()->all(),
            'cultivation' => collect([
                'Best sowing season' => $product->sowing_season,
                'Sunlight' => $product->sunlight,
                'Germination' => $product->germination_days,
                'First harvest' => $product->harvest_days,
                'Plant spacing' => $product->plant_spacing,
                'Sowing depth' => $product->sowing_depth,
                'Difficulty' => $product->growing_difficulty,
            ])->filter()->all(),
        ];
    }
}
