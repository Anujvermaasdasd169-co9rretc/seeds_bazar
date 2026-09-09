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
    public function index(Request $request, ?Category $category = null): View
    {
        $navCategories = Category::headerTree();
        $homeCategories = Category::homeCategories();
        $activeCategory = $this->resolveActiveCategory($request, $category);
        $storefront = Setting::storefront();
        $childCategories = $activeCategory
            ? $activeCategory->children->where('is_active', true)->values()
            : collect();

        $filterCategories = $activeCategory
            ? $childCategories->mapWithKeys(fn (Category $child) => [$child->slug => $child->displayName()])->all()
            : $navCategories->mapWithKeys(fn (Category $item) => [$item->slug => $item->displayName()])->all();

        return view('shop.index', [
            'products' => $this->catalogProducts($activeCategory),
            'categories' => $filterCategories,
            'navCategories' => $navCategories,
            'homeCategories' => $homeCategories,
            'activeCategory' => $activeCategory,
            'childCategories' => $childCategories,
            'activeCategorySlug' => $activeCategory?->slug ?? 'all',
            'storefront' => $storefront,
            'currency' => config('seeds_bazar.currency'),
            'tagline' => Setting::get('tagline', config('seeds_bazar.tagline')),
            'whatsappNumber' => Setting::get('whatsapp_number', config('seeds_bazar.whatsapp_number')),
            'shippingEstimate' => Setting::get('shipping_estimate', config('seeds_bazar.shipping.estimate')),
            'reviews' => $activeCategory ? collect() : Review::with('product')
                ->where('status', 'approved')
                ->whereHas('product', fn ($query) => $query->where('is_active', true))
                ->latest()
                ->get(),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load([
            'category.parent.parent',
            'reviews' => fn ($query) => $query->where('status', 'approved')->latest(),
        ]);

        abort_unless($product->is_active && $product->category?->isVisibleOnStorefront(), 404);

        $related = Product::with(['category.parent.parent', 'reviews' => fn ($query) => $query->where('status', 'approved')])
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
            'searchProducts' => $this->catalogProducts(),
            'navCategories' => Category::headerTree(),
            'storefront' => Setting::storefront(),
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

    private function resolveActiveCategory(Request $request, ?Category $routeCategory = null): ?Category
    {
        if ($routeCategory instanceof Category) {
            abort_unless($routeCategory->isVisibleOnStorefront(), 404);

            return $routeCategory->loadMissing([
                'parent.parent',
                'children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
            ]);
        }

        $slug = $request->query('category');
        if (! is_string($slug) || $slug === '' || $slug === 'all') {
            return null;
        }

        $category = Category::query()->with('parent.parent')->where('slug', $slug)->first();

        if (! $category?->isVisibleOnStorefront()) {
            return null;
        }

        return $category;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function catalogProducts(?Category $within = null)
    {
        $query = Product::with([
            'category.parent.parent',
            'reviews' => fn ($inner) => $inner->where('status', 'approved'),
        ])
            ->where('is_active', true)
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));

        if ($within) {
            $query->whereIn('category_id', $within->descendantAndSelfIds());
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->catalogProduct($product));
    }

    /** @return array<string, mixed> */
    private function catalogProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'url' => route('products.show', $product),
            'category' => $product->category->slug,
            'category_name' => $product->category->name,
            'category_path' => $product->category->pathSlugs(),
            'category_path_names' => $product->category->pathNames(),
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
