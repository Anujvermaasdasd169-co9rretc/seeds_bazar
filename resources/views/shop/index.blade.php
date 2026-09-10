@extends('layouts.app')

@php
    $storefront = $storefront ?? \App\Models\Setting::storefront();
    $activeCategory = $activeCategory ?? null;
    $childCategories = $childCategories ?? collect();
@endphp

@section('title', $activeCategory ? $activeCategory->displayName().' — Seed Planta' : 'Seed Planta — Buy Quality Seeds Online')
@section('meta_description', $activeCategory?->description ?: 'Seed Planta — quality seeds for home gardens, farms, and growing spaces.')

@section('content')
<div class="shop" id="shop-app"
     data-whatsapp="{{ $whatsappNumber }}"
    data-currency="{{ $currency }}"
    data-shipping-estimate="{{ $shippingEstimate }}"
    data-active-category="{{ $activeCategorySlug ?? 'all' }}"
    data-page="{{ $activeCategory ? 'category' : 'home' }}">
    <script type="application/json" id="shop-products">@json($products)</script>

    <x-storefront-header :nav-categories="$navCategories" :storefront="$storefront" />

    @unless ($activeCategory)
    <section class="hero-slider" id="hero-slider" aria-label="Seed Planta highlights">
        <div class="hero-slider__track">
            <article class="hero-slide is-active" data-slide="0">
                <img src="{{ asset('banner/1.png') }}" alt="A productive garden filled with vegetables and plants" fetchpriority="high">
                <div class="hero-slide__shade"></div>
            </article>
            <article class="hero-slide" data-slide="1" aria-hidden="true">
                <img src="{{ asset('banner/2.png') }}" alt="Fresh fruit growing in a bright garden" loading="lazy">
                <div class="hero-slide__shade"></div>
            </article>
            <article class="hero-slide" data-slide="2" aria-hidden="true">
                <img src="{{ asset('banner/3.png') }}" alt="Colourful flowering plants in a garden nursery" loading="lazy">
                <div class="hero-slide__shade"></div>
            </article>
        </div>
        <div class="hero-slider__controls">
            <button type="button" class="hero-slider__arrow" data-slider-prev aria-label="Previous banner">←</button>
            <div class="hero-slider__dots" role="tablist" aria-label="Choose a banner">
                <button type="button" class="hero-slider__dot is-active" data-slider-dot="0" role="tab" aria-label="Show banner 1" aria-selected="true"></button>
                <button type="button" class="hero-slider__dot" data-slider-dot="1" role="tab" aria-label="Show banner 2" aria-selected="false"></button>
                <button type="button" class="hero-slider__dot" data-slider-dot="2" role="tab" aria-label="Show banner 3" aria-selected="false"></button>
            </div>
            <button type="button" class="hero-slider__arrow" data-slider-next aria-label="Next banner">→</button>
        </div>
        <div class="hero-slider__progress" aria-hidden="true"><span></span></div>
    </section>

    @if (($homeCategories ?? collect())->isNotEmpty())
    <section class="market-intro" aria-labelledby="market-title">
        <div>
            <span class="section-eyebrow">{{ $storefront['top_eyebrow'] }}</span>
            <h2 id="market-title">{{ $storefront['top_title'] }}</h2>
        </div>
        <p>{{ $storefront['top_intro'] }}</p>
    </section>

    <section class="collection-rail" aria-label="{{ $storefront['top_title'] }}">
        @foreach ($homeCategories as $index => $homeCategory)
            <a href="{{ route('shop.category', $homeCategory) }}"
               class="collection-card collection-card--dynamic collection-card--tone-{{ $index % 6 }}"
               @if ($homeCategory->image_url) style="background-image: linear-gradient(160deg, rgba(26,61,46,.55), rgba(26,61,46,.2)), url('{{ $homeCategory->image_url }}'); background-size: cover; background-position: center;" @endif>
                <span>{{ $homeCategory->emoji ?: str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <strong>{{ $homeCategory->displayName() }}</strong>
                <small>{{ $homeCategory->description ?: implode(' / ', $homeCategory->pathNames()) }}</small>
                <b>Explore {{ $homeCategory->displayName() }} →</b>
            </a>
        @endforeach
    </section>
    @endif
    @endunless

    @if ($activeCategory)
    <section class="category-hero" aria-labelledby="category-title">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('shop.index') }}">Home</a>
            @foreach ($activeCategory->pathCrumbs() as $crumb)
                <span>/</span>
                @if ($crumb->id === $activeCategory->id)
                    <span aria-current="page">{{ $crumb->name }}</span>
                @else
                    <a href="{{ route('shop.category', $crumb) }}">{{ $crumb->name }}</a>
                @endif
            @endforeach
        </nav>
        <h1 id="category-title">{{ $activeCategory->name }}</h1>
        @if ($activeCategory->description)
            <p class="category-hero__desc">{{ $activeCategory->description }}</p>
        @endif
        @if ($childCategories->isNotEmpty())
            <div class="category-children" aria-label="Sub-categories">
                @foreach ($childCategories as $child)
                    <a href="{{ route('shop.category', $child) }}" class="category-chip">{{ $child->emoji ? $child->emoji.' ' : '' }}{{ $child->displayName() }}</a>
                @endforeach
            </div>
        @endif
    </section>
    @endif

    @unless ($activeCategory)
    <nav class="filters" aria-label="Product categories">
        <button type="button" class="filter-btn is-active" data-category="all">All</button>
        @foreach (is_iterable($categories) ? $categories : [] as $key => $label)
            <a href="{{ route('shop.category', $key) }}" class="filter-btn">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="catalog-heading">
        <div>
            <span class="section-eyebrow">{{ $storefront['catalog_eyebrow'] }}</span>
            <h2>{{ $storefront['catalog_title'] }}</h2>
        </div>
        <p>{{ $storefront['catalog_intro'] }}</p>
    </div>
    @endunless

    <main class="products-grid products-grid--collapsed" id="products-grid">
        @foreach (is_iterable($products) ? $products : [] as $product)
            @php
                $mrp = (int) ceil($product['price'] * 1.4);
                $discount = $mrp > $product['price']
                    ? (int) round((($mrp - $product['price']) / $mrp) * 100)
                    : 0;
                $rating = $product['review_rating'] ? number_format($product['review_rating'], 1) : 'New';
                $reviews = $product['review_count'];
                $badges = ['Best Seller', 'Trending', 'Fresh Stock', 'Top Rated'];
                $badge = $badges[$product['id'] % count($badges)];
                $badgeClass = ($product['id'] % 4 === 3) ? 'product-badge--gold' : '';
            @endphp
            <article class="product-card" data-category="{{ $product['category'] }}" data-category-path="{{ implode(' ', $product['category_path'] ?? []) }}" data-id="{{ $product['id'] }}" data-name="{{ $product['name'] }}">
                <div class="product-card__top">
                    <span class="product-badge {{ $badgeClass }}">{{ $badge }}</span>
                    <button type="button"
                            class="wishlist-btn"
                            data-wishlist="{{ $product['id'] }}"
                            data-id="{{ $product['id'] }}"
                            data-name="{{ $product['name'] }}"
                            data-price="{{ $product['price'] }}"
                            data-stock="{{ $product['stock_quantity'] }}"
                            data-unit="{{ $product['unit'] }}"
                            data-emoji="{{ $product['emoji'] }}"
                            data-image="{{ $product['image'] ?? '' }}"
                            aria-label="Add to wishlist">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                    <button type="button"
                            class="product-card__open"
                            data-product-detail="{{ $product['id'] }}"
                            aria-label="View details for {{ $product['name'] }}">
                        <x-product-visual
                            :image="$product['image'] ?? null"
                            :emoji="$product['emoji'] ?? '🌱'"
                        />
                    </button>
                </div>
                <div class="product-card__body">
                    <span class="product-card__category">{{ $product['category_name'] ?? ($categories[$product['category']] ?? '') }}</span>
                    <h2 class="product-card__name" title="{{ $product['name'] }}"><a href="{{ route('products.show', $product['id']) }}">{{ $product['name'] }}</a></h2>
                    <div class="product-card__rating">
                        <span class="stars" aria-hidden="true">{{ $product['review_rating'] ? '★★★★★' : '☆☆☆☆☆' }}</span>
                        <span class="product-card__rating-text">{{ $rating }}{{ $reviews ? ' | '.$reviews : '' }}</span>
                    </div>
                    <p class="product-card__unit-line">{{ $product['unit'] }}</p>
                    <div class="product-card__pricing">
                        <div class="product-card__price-row">
                            <strong class="product-card__price-now">{{ $currency }}{{ number_format($product['price'], 2) }}</strong>
                            @if ($discount > 0)
                                <span class="product-card__price-mrp">{{ $currency }}{{ number_format($mrp) }}</span>
                                <span class="product-card__discount">-{{ $discount }}% Off</span>
                            @endif
                        </div>
                    </div>
                        <button type="button"
                            class="btn btn--cart-full"
                            data-add-to-cart
                            data-id="{{ $product['id'] }}"
                            data-name="{{ $product['name'] }}"
                            data-price="{{ $product['price'] }}"
                            data-stock="{{ $product['stock_quantity'] }}"
                            data-unit="{{ $product['unit'] }}"
                            data-emoji="{{ $product['emoji'] }}"
                            data-image="{{ $product['image'] ?? '' }}"
                            @disabled(! $product['in_stock'])>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        {{ $product['in_stock'] ? 'Add to Cart' : 'Out of stock' }}
                    </button>
                </div>
            </article>
        @endforeach
    </main>
    @if (count($products) > 8)
        <div class="products-more">
            <button type="button" class="products-more__button" id="view-all-products">View all products</button>
        </div>
    @endif
    <p class="products-empty" id="products-empty" @if (count($products) > 0) hidden @endif>{{ $activeCategory ? 'No products in this category yet.' : 'No products match your search.' }}</p>

    @unless ($activeCategory)
    <section class="grower-guide" id="grower-guide" aria-labelledby="guide-title">
        <div class="grower-guide__intro">
            <span class="section-eyebrow">Grow with confidence</span>
            <h2 id="guide-title">A good harvest starts before planting.</h2>
            <p>Keep these simple habits close and your seeds will have the best possible start.</p>
            <a href="{{ route('contact.show') }}" class="text-link">Need help choosing a variety? Talk to a grower →</a>
        </div>
        <ol class="grower-steps">
            <li><span>01</span><h3>Read the season</h3><p>Match your variety to local weather, sunlight, and the space you have.</p></li>
            <li><span>02</span><h3>Prepare gently</h3><p>Use loose, clean growing medium and water until evenly moist—not wet.</p></li>
            <li><span>03</span><h3>Sow with patience</h3><p>Give seedlings warmth, light, and time. Consistency matters more than overwatering.</p></li>
        </ol>
    </section>

    <section class="faq-section" aria-labelledby="faq-title">
        <div class="faq-section__intro">
            <span class="section-eyebrow">Before you order</span>
            <h2 id="faq-title">A few useful answers.</h2>
        </div>
        <div class="faq-list">
            <details open><summary>How do I choose the right seed pack?</summary><p>Start with your available sunlight, space, and growing season. Each product quick-view shows its pack size and basics; contact us if you would like a recommendation.</p></details>
            <details><summary>When will my order be dispatched?</summary><p>Orders are prepared after confirmation. The checkout flow shows the current delivery estimate before you place an order.</p></details>
            <details><summary>Can I order through WhatsApp?</summary><p>Yes. Add products to your cart and use the WhatsApp order button, or message us directly for availability and guidance.</p></details>
        </div>
    </section>

    <section class="reviews-section" id="reviews" aria-labelledby="reviews-title">
        <div class="reviews-section__intro">
            <span class="section-eyebrow">Customer feedback</span>
            <h2 id="reviews-title">Share your experience</h2>
            <p>Tell other growers which seeds worked well for you.</p>
        </div>

        @if (session('review_success'))
            <p class="review-alert review-alert--success">{{ session('review_success') }}</p>
        @endif

        <form method="POST" action="{{ route('reviews.store') }}" class="review-form">
            @csrf
            <label class="review-field">
                <span>Product</span>
                <select name="product_id" required>
                    <option value="">Choose a product</option>
                    @foreach (is_iterable($products) ? $products : [] as $product)
                        <option value="{{ $product['id'] }}" @selected(old('product_id') == $product['id'])>{{ $product['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="review-field">
                <span>Your name</span>
                <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required>
            </label>
            <label class="review-field">
                <span>Rating</span>
                <select name="rating" required>
                    <option value="">Choose rating</option>
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5</option>
                    @endfor
                </select>
            </label>
            <label class="review-field review-field--wide">
                <span>Your review</span>
                <textarea name="comment" rows="3" maxlength="1000" required>{{ old('comment') }}</textarea>
            </label>
            <button type="submit" class="review-submit">Submit review</button>
        </form>

        @if ($errors->any())
            <p class="review-alert review-alert--error">{{ $errors->first() }}</p>
        @endif

        <div class="reviews-list">
            @forelse (is_iterable($reviews) ? $reviews : [] as $review)
                <article class="review-item">
                    <div class="review-item__top">
                        <strong>{{ $review->name }}</strong>
                        <span class="review-item__rating" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    </div>
                    <p class="review-item__product">{{ $review->product->name }}</p>
                    <p>{{ $review->comment }}</p>
                </article>
            @empty
                <p class="reviews-empty">No reviews yet. Be the first to share your experience.</p>
            @endforelse
        </div>
    </section>
    @endunless

    <footer class="footer">
        <div class="footer__inner">
            <div class="footer__brand">
                <a href="{{ route('shop.index') }}" class="footer__logo">
                    <x-site-logo class="footer__logo-icon" />
                    <span>
                        <strong>Seed Planta</strong>
                        <small>{{ $tagline }}</small>
                    </span>
                </a>
                <p class="footer__about">Premium vegetable, fruit, flower &amp; grain seeds. Order in one click on WhatsApp.</p>
            </div>

            <div class="footer__col">
                <h3>Shop</h3>
                <a href="{{ route('shop.index') }}#products-grid">All products</a>
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('shop.category', $navCategory) }}">{{ $navCategory->displayName() }}</a>
                @endforeach
            </div>

            <div class="footer__col">
                <h3>Help</h3>
                <button type="button" class="footer__text-btn" id="contact-open-footer">Contact Us</button>
                <a href="{{ route('policies.shipping') }}">Shipping &amp; delivery</a>
                <a href="{{ route('policies.returns') }}">Returns &amp; refunds</a>
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsappNumber) }}?text={{ urlencode('Hi Seed Planta, I want to buy seeds. Please share availability & price list.') }}"
                   target="_blank" rel="noopener noreferrer">WhatsApp order</a>
                {{-- <a href="{{ route('admin.login') }}">Admin login</a> --}}
            </div>

            <div class="footer__col footer__col--cta">
                <h3>Order on WhatsApp</h3>
                <p>Fast confirmation. No checkout hassle.</p>
                <a class="footer__wa"
                   href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsappNumber) }}?text={{ urlencode('Hi Seed Planta, I want to buy seeds.') }}"
                   target="_blank" rel="noopener noreferrer">
                    Chat now
                </a>
            </div>
        </div>
        <div class="footer__bottom">
            <p>&copy; {{ date('Y') }} Seed Planta. All rights reserved.</p>
            <p><a href="{{ route('policies.privacy') }}">Privacy</a> · <a href="{{ route('policies.terms') }}">Terms</a></p>
        </div>
    </footer>

    <x-storefront-drawers :currency="$currency" />
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cart.js') }}?v={{ @filemtime(public_path('js/cart.js')) ?: time() }}" defer></script>
@endpush
