@extends('layouts.app')

@section('title', 'Seed Planta — Buy Quality Seeds Online')

@section('content')
<div class="shop" id="shop-app"
     data-whatsapp="{{ $whatsappNumber }}"
    data-currency="{{ $currency }}"
    data-shipping-estimate="{{ $shippingEstimate }}">
    <script type="application/json" id="shop-products">@json($products)</script>

    <header class="header">
        <div class="header__inner">
            <a href="{{ route('shop.index') }}" class="logo">
                <x-site-logo class="logo__icon" />
            </a>

            <nav class="header-nav" aria-label="Main">
                <a href="{{ route('shop.index') }}" class="header-nav__link" id="nav-home">Home</a>
                <a href="#grower-guide" class="header-nav__link">Growing guide</a>
                <div class="header-nav__item" id="seeds-menu">
                    <button type="button" class="header-nav__link header-nav__link--btn" id="seeds-toggle" aria-expanded="false" aria-controls="seeds-drop">
                        Seeds
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div class="header-nav__drop" id="seeds-drop" hidden>
                        <button type="button" class="header-nav__drop-item" data-nav-category="all">All Seeds</button>
                        @foreach (is_iterable($categories) ? $categories : [] as $key => $label)
                            <button type="button" class="header-nav__drop-item" data-nav-category="{{ $key }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            </nav>

            <div class="header-search" id="header-search">
                <div class="header-search__box">
                    <input type="search"
                           id="global-search"
                           class="header-search__input"
                           placeholder="Search seeds, plants &amp; more…"
                           autocomplete="off"
                           spellcheck="false"
                           aria-label="Search products"
                           aria-controls="search-results"
                           aria-expanded="false">
                        <span class="header-search__marquee" aria-hidden="true"><span>Search seeds, plants &amp; more…</span></span>
                        <svg class="header-search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="M20 20l-3.2-3.2"/>
                        </svg>
                    {{-- <kbd class="header-search__hint"></kbd> --}}
                    <button type="button" class="header-search__clear" id="search-clear" hidden aria-label="Clear search">&times;</button>
                </div>
                <div class="header-search__drop" id="search-results" hidden role="listbox" aria-label="Search results"></div>
            </div>

            <div class="header-actions">
                <button type="button" class="header-link" id="contact-open">Contact Us</button>
                <button type="button" class="cart-toggle cart-toggle--wish" id="wishlist-toggle" aria-label="Open wishlist">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <span class="cart-toggle__badge" id="wishlist-count">0</span>
                </button>
                <button type="button" class="cart-toggle" id="cart-toggle" aria-label="Open cart">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <span class="cart-toggle__badge" id="cart-count">0</span>
                </button>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="hero__bg" aria-hidden="true"></div>
        <div class="hero__inner">
            <div class="hero__content">
                <div class="hero__kicker">
                    <span class="hero__pill">Trusted seeds</span>
                    <span class="hero__pill hero__pill--light">Fast WhatsApp order</span>
                </div>

                <h1>
                    Grow your next
                    <span class="hero__highlight">best harvest</span>
                </h1>
                <div class="hero-marquee" aria-label="Message">
                    <div class="hero-marquee__track">
                        <p class="hero-marquee__text">
                            Premium vegetable, fruit, flower & grain seeds — order via WhatsApp in one click.
                        </p>

                    </div>
                </div>

                <div class="hero__actions">
                    <a class="hero-btn hero-btn--primary" href="#products-grid">
                        Shop now
                        <span aria-hidden="true">→</span>
                    </a>
                    <a class="hero-btn hero-btn--ghost"
                       href="https://wa.me/{{ preg_replace('/\\D+/', '', $whatsappNumber) }}?text={{ urlencode('Hi Seed Planta, I want to buy seeds. Please share availability & price list.') }}"
                       target="_blank" rel="noopener noreferrer">
                        WhatsApp
                    </a>
                </div>

             
            </div>

            <div class="hero__art" aria-hidden="true">
                <div class="hero__card hero__card--one">
                    <span class="hero__card-emoji">🌿</span>
                    <span class="hero__card-text">High germination</span>
                </div>
                <div class="hero__card hero__card--two">
                    <span class="hero__card-emoji">🌱</span>
                    <span class="hero__card-text">Premium quality</span>
                </div>
                <div class="hero__card hero__card--three">
                    <span class="hero__card-emoji">🚚</span>
                    <span class="hero__card-text">Quick dispatch</span>
                </div>
            </div>
        </div>
    </section>

    <section class="trust-strip" aria-label="Why shop with Seed Planta">
        <div class="trust-strip__inner">
            <div class="trust-strip__item"><span aria-hidden="true">✓</span><p><strong>Quality checked</strong><small>Seeds selected for reliable growth</small></p></div>
            <div class="trust-strip__item"><span aria-hidden="true">✦</span><p><strong>Fresh lots</strong><small>Stored carefully for better viability</small></p></div>
            <div class="trust-strip__item"><span aria-hidden="true">⌂</span><p><strong>For every garden</strong><small>From balcony pots to farm plots</small></p></div>
            <div class="trust-strip__item"><span aria-hidden="true">↗</span><p><strong>Grower support</strong><small>Ask us before you sow</small></p></div>
        </div>
    </section>

    <section class="market-intro" aria-labelledby="market-title">
        <div>
            <span class="section-eyebrow">The seed marketplace</span>
            <h2 id="market-title">Pick a season. Start something beautiful.</h2>
        </div>
        <p>Thoughtfully chosen seeds for kitchen gardens, flowering balconies, and productive fields—packed in practical quantities and ready to grow.</p>
    </section>

    <section class="collection-rail" aria-label="Shop by garden goal">
        <a href="#products-grid" class="collection-card collection-card--kitchen" data-category-link="vegetables">
            <span>01</span><strong>Kitchen garden</strong><small>Everyday harvests, close to home</small><b>Explore vegetables →</b>
        </a>
        <a href="#products-grid" class="collection-card collection-card--bloom" data-category-link="flowers">
            <span>02</span><strong>Bloom &amp; brighten</strong><small>Colour for borders, pots, and pollinators</small><b>Explore flowers →</b>
        </a>
        <a href="#products-grid" class="collection-card collection-card--field" data-category-link="grains">
            <span>03</span><strong>Field essentials</strong><small>Dependable varieties for larger growing</small><b>Explore grains →</b>
        </a>
    </section>

    <nav class="filters" aria-label="Product categories">
        <button type="button" class="filter-btn is-active" data-category="all">All</button>
        @foreach (is_iterable($categories) ? $categories : [] as $key => $label)
            <button type="button" class="filter-btn" data-category="{{ $key }}">{{ $label }}</button>
        @endforeach
    </nav>

    <div class="catalog-heading">
        <div>
            <span class="section-eyebrow">Fresh picks</span>
            <h2>Seeds worth growing</h2>
        </div>
        <p>Browse by category, search a variety, or open any pack for more detail.</p>
    </div>

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
            <article class="product-card" data-category="{{ $product['category'] }}" data-id="{{ $product['id'] }}" data-name="{{ $product['name'] }}">
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
    <p class="products-empty" id="products-empty" hidden>No products match your search.</p>

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
                <a href="#products-grid">All products</a>
                @foreach (is_iterable($categories) ? $categories : [] as $label)
                    <a href="#products-grid">{{ $label }}</a>
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

    {{-- Cart drawer --}}
    <div class="cart-overlay" id="cart-overlay" hidden></div>
    <aside class="cart-drawer" id="cart-drawer" aria-label="Shopping cart" hidden>
        <div class="cart-drawer__header">
            <h2>Your Cart</h2>
            <button type="button" class="cart-drawer__close" id="cart-close" aria-label="Close cart">&times;</button>
        </div>
        <div class="cart-drawer__items" id="cart-items">
            <p class="cart-empty" id="cart-empty">Your cart is empty. Add some seeds!</p>
        </div>
        <div class="cart-drawer__footer" id="cart-footer" hidden>
            <div class="cart-total">
                <span>Total</span>
                <strong id="cart-total">{{ $currency }}0</strong>
            </div>
            <button type="button" class="btn btn--whatsapp" id="btn-purchase">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Purchase on WhatsApp
            </button>
            <a href="{{ route('checkout') }}" class="btn btn--cart-full">Checkout</a>
            <button type="button" class="btn btn--ghost" id="btn-clear-cart">Clear Cart</button>
        </div>
    </aside>

    {{-- Wishlist drawer --}}
    <div class="cart-overlay" id="wishlist-overlay" hidden></div>
    <aside class="cart-drawer" id="wishlist-drawer" aria-label="Wishlist" hidden>
        <div class="cart-drawer__header">
            <h2>Your Wishlist</h2>
            <button type="button" class="cart-drawer__close" id="wishlist-close" aria-label="Close wishlist">&times;</button>
        </div>
        <div class="cart-drawer__items" id="wishlist-items">
            <p class="cart-empty" id="wishlist-empty">Your wishlist is empty. Tap the heart on a product.</p>
        </div>
        <div class="cart-drawer__footer" id="wishlist-footer" hidden>
            <button type="button" class="btn btn--cart-full" id="btn-wishlist-to-cart">Move all to cart</button>
            <button type="button" class="btn btn--ghost" id="btn-clear-wishlist">Clear Wishlist</button>
        </div>
    </aside>

    {{-- Product details popup --}}
    <div class="product-overlay" id="product-overlay" hidden></div>
    <div class="product-modal" id="product-modal" role="dialog" aria-modal="true" aria-labelledby="pd-name" hidden>
        <button type="button" class="product-modal__close" id="product-close" aria-label="Close details">&times;</button>
    <div class="product-modal__toolbar">
        <button type="button" class="product-modal__tool" id="pd-wishlist" aria-label="Add product to wishlist">♡</button>
        <button type="button" class="product-modal__tool" id="pd-share" aria-label="Share product">↗</button>
    </div>
        <div class="product-modal__grid">
            <div class="product-modal__media" id="pd-media"></div>
            <div class="product-modal__info">
                <span class="product-modal__cat" id="pd-cat"></span>
                <h2 id="pd-name"></h2>
                <div class="product-modal__rating" id="pd-rating"></div>
                <div class="product-modal__facts"><span id="pd-unit"></span><span id="pd-stock"></span></div>
                <div class="product-modal__price" id="pd-price"></div>
                <div class="product-modal__delivery"><strong>Standard Delivery</strong><span id="pd-delivery"></span></div>
                <p class="product-modal__desc" id="pd-desc"></p>
                <ul class="product-modal__points">
                    <li>High germination quality seeds</li>
                    <li>Packed fresh for your climate</li>
                    <li>Easy WhatsApp order &amp; dispatch</li>
                </ul>
                <div class="product-modal__buy">
                    <div class="cart-qty product-modal__qty">
                        <button type="button" class="cart-qty__btn" id="pd-qty-minus" aria-label="Decrease quantity">−</button>
                        <span class="cart-qty__value" id="pd-qty">1</span>
                        <button type="button" class="cart-qty__btn" id="pd-qty-plus" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" class="btn btn--cart-full" id="pd-add-cart">Add to Cart</button>
                </div>
                <div class="product-modal__reviews" id="pd-reviews"></div>
            </div>
        </div>
    </div>

    {{-- Contact popup --}}
    <div class="modal-overlay" id="contact-overlay" hidden></div>
    <div class="modal" id="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-title" hidden>
        <div class="modal__header">
            <div>
                <h2 id="contact-title">Contact Us</h2>
                <p class="modal__sub">Fill details — query is optional.</p>
            </div>
            <button type="button" class="modal__close" id="contact-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal__body">
            <div class="modal__alert modal__alert--success" id="contact-success" hidden></div>
            <div class="modal__alert modal__alert--error" id="contact-error" hidden></div>

            <form id="contact-form" method="POST" action="{{ route('contact.store', absolute: false) }}" class="modal-form" novalidate>
                <div class="modal-grid">
                    <label class="modal-field">
                        <span>Name *</span>
                        <input name="name" type="text" required maxlength="100" placeholder="Your name">
                    </label>
                    <label class="modal-field">
                        <span>Mobile *</span>
                        <input name="mobile" type="text" required maxlength="25" placeholder="Your mobile number">
                    </label>
                    <label class="modal-field modal-field--full">
                        <span>Email *</span>
                        <input name="email" type="email" required maxlength="255" placeholder="you@example.com">
                    </label>
                    <label class="modal-field modal-field--full">
                        <span>Address *</span>
                        <input name="address" type="text" required maxlength="255" placeholder="Your address">
                    </label>
                    <label class="modal-field modal-field--full">
                        <span>Query (optional)</span>
                        <textarea name="query" rows="3" maxlength="2000" placeholder="Write your message (optional)"></textarea>
                    </label>
                </div>

                <div class="modal__actions">
                    <span class="modal__note">Fields marked * are required.</span>
                    <button type="submit" class="modal__submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cart.js') }}?v={{ @filemtime(public_path('js/cart.js')) ?: time() }}" defer></script>
@endpush
