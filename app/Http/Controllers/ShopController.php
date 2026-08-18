<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $products = Product::with('category')
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
            ]);

        $categoryLabels = $categories->pluck('name', 'slug')->all();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categoryLabels,
            'currency' => config('seeds_bazar.currency'),
            'tagline' => config('seeds_bazar.tagline'),
            'whatsappNumber' => config('seeds_bazar.whatsapp_number'),
        ]);
    }
}
