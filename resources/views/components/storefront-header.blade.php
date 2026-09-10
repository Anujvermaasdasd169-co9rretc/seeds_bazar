@php
    $storefront = $storefront ?? \App\Models\Setting::storefront();
    $navCategories = $navCategories ?? collect();
    $searchPlaceholder = $storefront['search_placeholder'];
    $guideLabel = trim((string) $storefront['guide_label']);
    $guideUrl = $storefront['guide_url'];
    $onHomepage = request()->routeIs('shop.index');
    if ($guideUrl && str_starts_with($guideUrl, '#')) {
        $guideHref = $onHomepage ? $guideUrl : route('shop.index').$guideUrl;
    } else {
        $guideHref = $guideUrl ?: route('shop.index').'#grower-guide';
    }
@endphp

<header class="header">
    <div class="header__top">
        <div class="header__top-inner">
            <a href="{{ route('shop.index') }}" class="logo">
                <x-site-logo class="logo__icon" />
            </a>

            <div class="header-search" id="header-search">
                <div class="header-search__box">
                    <input type="search"
                           id="global-search"
                           class="header-search__input"
                           placeholder="{{ $searchPlaceholder }}"
                           autocomplete="off"
                           spellcheck="false"
                           aria-label="Search products"
                           aria-controls="search-results"
                           aria-expanded="false">
                    <span class="header-search__marquee" aria-hidden="true"><span>{{ $searchPlaceholder }}</span></span>
                    <svg class="header-search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-3.2-3.2"/>
                    </svg>
                    <button type="button" class="header-search__clear" id="search-clear" hidden aria-label="Clear search">&times;</button>
                </div>
                <div class="header-search__drop" id="search-results" hidden role="listbox" aria-label="Search results"></div>
            </div>

            <div class="header-actions">
                @if ($storefront['show_contact'])
                    <button type="button" class="header-link" id="contact-open">{{ $storefront['contact_label'] }}</button>
                @endif
                @if ($storefront['show_account'])
                    @auth
                        <a href="{{ route('account') }}" class="header-link">Account</a>
                    @else
                        <a href="{{ route('login') }}" class="header-link">Log in</a>
                    @endauth
                @endif
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
                <button type="button" class="header-nav__burger" id="nav-toggle" aria-expanded="false" aria-controls="main-nav">
                    <span class="header-nav__burger-lines" aria-hidden="true"></span>
                    <span class="visually-hidden">Open menu</span>
                </button>
            </div>
        </div>
    </div>

    <nav class="header-nav" id="main-nav" aria-label="Main">
        <div class="header-nav__inner">
            @if ($storefront['show_home'])
                <a href="{{ route('shop.index') }}" class="header-nav__link {{ request()->routeIs('shop.index') ? 'is-active' : '' }}" id="nav-home">{{ $storefront['home_label'] }}</a>
            @endif
            @foreach ($navCategories as $category)
                <x-nav-category :category="$category" :level="1" />
            @endforeach
            @if ($guideLabel !== '')
                <a href="{{ $guideHref }}" class="header-nav__link">{{ $guideLabel }}</a>
            @endif
        </div>
    </nav>
</header>
