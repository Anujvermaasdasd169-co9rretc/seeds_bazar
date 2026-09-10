@extends('layouts.app')

@section('title', $product['name'].' — Seed Planta')
@section('meta_description', Illuminate\Support\Str::limit($product['description'] ?: 'Buy '.$product['name'].' from Seed Planta.', 155))

@php
    $structuredProductData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'description' => $product['description'] ?: null,
        'image' => $product['image'] ? [url($product['image'])] : [],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'INR',
            'price' => $product['price'],
            'availability' => $product['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('products.show', $product['id']),
        ],
    ];
@endphp
@push('head')
<script type="application/ld+json">@json($structuredProductData)</script>
@endpush

@section('content')
<div class="shop product-page" id="shop-app" data-currency="{{ $currency }}" data-whatsapp="{{ $whatsappNumber }}" data-shipping-estimate="{{ $shippingEstimate }}" data-page="product">
    <script type="application/json" id="shop-products">@json($searchProducts ?? collect([$product])->merge($relatedProducts)->values())</script>

    <x-storefront-header :nav-categories="$navCategories" :storefront="$storefront ?? null" />
        <x-storefront-banner />

    <main class="product-page__main">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('shop.index') }}">Home</a>
            @foreach ($product['category_path'] ?? [] as $index => $slug)
                <span>/</span>
                <a href="{{ route('shop.category', $slug) }}">{{ $product['category_path_names'][$index] ?? $slug }}</a>
            @endforeach
            <span>/</span>
            <span aria-current="page">{{ $product['name'] }}</span>
        </nav>

        <section class="product-detail" aria-labelledby="product-title">
            <div class="product-detail__gallery">
                <div class="product-detail__visual {{ $product['image'] ? 'product-detail__visual--photo' : '' }}">
                    @if ($product['image'])
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }} seed pack" fetchpriority="high">
                    @else
                        <span>{{ $product['emoji'] }}</span>
                    @endif
                </div>
                <div class="product-detail__gallery-note"><span>Seed Planta selection</span><span>•</span><span>Packaged with care</span></div>
            </div>

            <div class="product-detail__content">
                <p class="product-detail__category">{{ $product['category_name'] }}</p>
                <h1 id="product-title">{{ $product['name'] }}</h1>
                <div class="product-detail__rating" aria-label="{{ $product['review_rating'] ?? 0 }} out of 5 stars">
                    <span aria-hidden="true">{{ $product['review_rating'] ? '★★★★★' : '☆☆☆☆☆' }}</span>
                    <a href="#product-reviews">{{ $product['review_count'] ? $product['review_count'].' customer review'.($product['review_count'] === 1 ? '' : 's') : 'Be the first to review' }}</a>
                </div>
                <div class="product-detail__price"><strong>{{ $currency }}{{ number_format($product['price'], 2) }}</strong><span>Inclusive of applicable taxes</span></div>
                <p class="product-detail__description">{{ $product['description'] ?: 'A carefully selected seed pack for your next growing season.' }}</p>

                <div class="product-detail__pack"><span>Pack size</span><strong>{{ $product['unit'] }}</strong><em>{{ $product['in_stock'] ? $product['stock_quantity'].' packs available' : 'Currently out of stock' }}</em></div>
                <button type="button" class="btn btn--cart-full product-detail__add" data-add-to-cart data-id="{{ $product['id'] }}" data-name="{{ $product['name'] }}" data-price="{{ $product['price'] }}" data-stock="{{ $product['stock_quantity'] }}" data-unit="{{ $product['unit'] }}" data-emoji="{{ $product['emoji'] }}" data-image="{{ $product['image'] ?? '' }}" @disabled(! $product['in_stock'])>
                    {{ $product['in_stock'] ? 'Add to cart' : 'Out of stock' }}
                </button>
                <p class="product-detail__support">Questions before you sow? <a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsappNumber) }}?text={{ urlencode('Hi Seed Planta, I have a question about '.$product['name'].'.') }}" target="_blank" rel="noopener noreferrer">Chat with us on WhatsApp</a></p>

                <div class="product-detail__assurances">
                    <div><span>◌</span><p><strong>Growing support</strong><small>Ask us about suitability before ordering.</small></p></div>
                    <div><span>↗</span><p><strong>{{ $shippingMethod }}</strong><small>{{ $shippingEstimate }}</small></p></div>
                    <div><span>✓</span><p><strong>Secure checkout</strong><small>Pay securely when you are ready.</small></p></div>
                </div>
            </div>
        </section>

        <section class="product-detail__guide" aria-labelledby="pack-guide-title">
            <div><span class="section-eyebrow">Growing guide</span><h2 id="pack-guide-title">Everything you need to plan your sowing.</h2><p class="product-detail__guide-copy">The growing information below is maintained by our Seed Planta team for this specific product.</p></div>
            @if (count($product['cultivation']))
                <dl class="product-detail__guide-grid">@foreach ($product['cultivation'] as $label => $value)<div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>@endforeach</dl>
            @else
                <div class="product-detail__guide-empty"><strong>Growing information is being prepared.</strong><p>Contact us before ordering and we will help you choose the right conditions for this variety.</p></div>
            @endif
        </section>

        <section class="product-detail__reviews" id="product-reviews" aria-labelledby="review-title">
            <div><span class="section-eyebrow">Grower notes</span><h2 id="review-title">Reviews for {{ $product['name'] }}</h2></div>
            <div class="product-detail__review-list">
                @forelse ($product['reviews'] as $review)
                    <article><div><strong>{{ $review['name'] }}</strong><span aria-label="{{ $review['rating'] }} out of 5 stars">{{ str_repeat('★', $review['rating']) }}{{ str_repeat('☆', 5 - $review['rating']) }}</span></div><p>{{ $review['comment'] }}</p></article>
                @empty
                    <p class="product-detail__no-reviews">No reviews yet. Buy, grow, and share your experience with the next grower.</p>
                @endforelse
            </div>
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section class="related-products" aria-labelledby="related-title"><div class="catalog-heading"><div><span class="section-eyebrow">Keep exploring</span><h2 id="related-title">More from {{ $product['category_name'] }}</h2></div><a href="{{ route('shop.category', $product['category']) }}" class="text-link">View all in {{ $product['category_name'] }} →</a></div><div class="related-products__grid">@foreach ($relatedProducts as $related)<article><a href="{{ route('products.show', $related['id']) }}" class="related-products__image">@if ($related['image'])<img src="{{ $related['image'] }}" alt="{{ $related['name'] }} seed pack" loading="lazy">@else<span>{{ $related['emoji'] }}</span>@endif</a><p>{{ $related['category_name'] }}</p><h3><a href="{{ route('products.show', $related['id']) }}">{{ $related['name'] }}</a></h3><div><strong>{{ $currency }}{{ number_format($related['price'], 2) }}</strong><button type="button" data-add-to-cart data-id="{{ $related['id'] }}" data-name="{{ $related['name'] }}" data-price="{{ $related['price'] }}" data-stock="{{ $related['stock_quantity'] }}" data-unit="{{ $related['unit'] }}" data-emoji="{{ $related['emoji'] }}" data-image="{{ $related['image'] ?? '' }}" @disabled(! $related['in_stock'])>+</button></div></article>@endforeach</div></section>
        @endif
    </main>

    <x-storefront-drawers :currency="$currency" :include-product-modal="false" />
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cart.js') }}?v={{ @filemtime(public_path('js/cart.js')) ?: time() }}" defer></script>
@endpush
