<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $homeReviews = $activeCategory ? collect() : Review::with(['product', 'user'])
            ->where('status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->get();

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
            'reviews' => $activeCategory ? collect() : $homeReviews,
            'reviewStats' => $activeCategory ? null : $this->reviewStats($homeReviews),
            'verifiedPurchases' => $activeCategory ? [] : $this->verifiedPurchaseKeys($homeReviews),
            'freeShipping' => Setting::get('free_shipping_threshold', (string) config('seeds_bazar.shipping.free_threshold')),
            'shippingFlat' => Setting::get('shipping_flat_rate', (string) config('seeds_bazar.shipping.flat_rate')),
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
            'reviewStats' => $this->reviewStats($product->reviews),
            'verifiedPurchases' => $this->verifiedPurchaseKeys($product->reviews),
            'freeShipping' => Setting::get('free_shipping_threshold', (string) config('seeds_bazar.shipping.free_threshold')),
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

        $product = Product::query()->whereKey($validated['product_id'])->where('is_active', true)->first();
        abort_unless($product, 422, 'This product is not available for review.');

        $name = $request->user()?->name ?: $validated['name'];

        Review::create([
            'product_id' => $product->id,
            'user_id' => $request->user()?->id,
            'name' => $name,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending',
        ]);

        if ($request->input('redirect_to') === 'product') {
            return redirect()
                ->route('products.show', $product)
                ->withFragment('product-reviews')
                ->with('review_success', 'Thank you! Your review was submitted for moderation.');
        }

        return redirect()
            ->route('shop.index')
            ->withFragment('reviews')
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
                'date' => optional($review->created_at)->diffForHumans(),
                'user_id' => $review->user_id,
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

    /**
     * @param  Collection<int, Review>  $reviews
     * @return array{count:int,avg:?float,histogram:array<int,int>}
     */
    private function reviewStats(Collection $reviews): array
    {
        $histogram = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $review) {
            $rating = (int) $review->rating;
            if (isset($histogram[$rating])) {
                $histogram[$rating]++;
            }
        }

        return [
            'count' => $reviews->count(),
            'avg' => $reviews->count() ? round((float) $reviews->avg('rating'), 1) : null,
            'histogram' => $histogram,
        ];
    }

    /**
     * @param  Collection<int, Review>|\Illuminate\Database\Eloquent\Collection<int, Review>  $reviews
     * @return array<string, bool>
     */
    private function verifiedPurchaseKeys($reviews): array
    {
        $userIds = collect($reviews)->pluck('user_id')->filter()->unique()->all();
        $productIds = collect($reviews)->pluck('product_id')->filter()->unique()->all();
        if ($userIds === [] || $productIds === []) {
            return [];
        }

        $rows = Order::query()
            ->whereIn('user_id', $userIds)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->whereHas('items', fn ($query) => $query->whereIn('product_id', $productIds))
            ->with('items:id,order_id,product_id')
            ->get(['id', 'user_id']);

        $keys = [];
        foreach ($rows as $order) {
            foreach ($order->items as $item) {
                $keys[$order->user_id.':'.$item->product_id] = true;
            }
        }

        return $keys;
    }
}
