<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account') - {{ config('app.name', 'Seed Planta') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/seeds-bazar.css') }}?v={{ @filemtime(public_path('css/seeds-bazar.css')) ?: time() }}">
</head>
<body>
@php
    $accountPanel = $accountPanel ?? 'login';
    $accountOpen = $accountOpen ?? true;
    $shopPage = $shopPage ?? 'account';
    $freeShip = \App\Models\Setting::get('free_shipping_threshold', (string) config('seeds_bazar.shipping.free_threshold'));
    $flatShip = \App\Models\Setting::get('shipping_flat_rate', (string) config('seeds_bazar.shipping.flat_rate'));
@endphp
<div class="shop account-shell" id="shop-app"
     data-page="{{ $shopPage }}"
     data-account-panel="{{ session('account_panel', $accountPanel) }}"
     data-account-open="{{ ($accountOpen || session('account_panel') || $errors->any() || session('status')) ? '1' : '0' }}"
     data-currency="{{ config('seeds_bazar.currency') }}"
     data-whatsapp="{{ \App\Models\Setting::get('whatsapp_number', config('seeds_bazar.whatsapp_number')) }}"
     data-free-shipping="{{ $freeShip }}"
     data-shipping-flat="{{ $flatShip }}">
    <script type="application/json" id="shop-products">[]</script>
    <x-storefront-header />
    <div class="account-shell__page">
        @yield('page')
    </div>
    <x-storefront-drawers :include-product-modal="false" />
</div>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="{{ asset('js/cart.js') }}?v={{ @filemtime(public_path('js/cart.js')) ?: time() }}" defer></script>
<script src="{{ asset('js/account-hub.js') }}?v={{ @filemtime(public_path('js/account-hub.js')) ?: time() }}" defer></script>
@stack('scripts')
</body>
</html>
