<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
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

        $products = Product::with(['category', 'reviews'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->slug,
                'category_name' => $product->category->name,
                'price' => (float) $product->price,
                'unit' => $product->unit,
                'description' => $product->description ?? '',
                'emoji' => $product->emoji,
                'image' => $product->image_url,
                'review_count' => $product->reviews->count(),
                'review_rating' => $product->reviews->count()
                    ? round($product->reviews->avg('rating'), 1)
                    : null,
            ]);

        $categoryLabels = $categories->pluck('name', 'slug')->all();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categoryLabels,
            'currency' => config('seeds_bazar.currency'),
            'tagline' => config('seeds_bazar.tagline'),
            'whatsappNumber' => config('seeds_bazar.whatsapp_number'),
            'reviews' => Review::with('product')
                ->whereHas('product', fn ($query) => $query->where('is_active', true))
                ->latest()
                ->get(),
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

        Review::create($validated);

        return redirect()
            ->route('shop.index')
            ->with('review_success', 'Thank you! Your review was added.');
    }
}
