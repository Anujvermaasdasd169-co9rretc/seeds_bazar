<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Page not found' }} - {{ config('app.name', 'Seed Planta') }}</title>
    <link rel="stylesheet" href="{{ asset('css/seeds-bazar.css') }}">
</head>
<body class="auth-shell">
    <main class="auth-panel">
        <a class="auth-brand" href="{{ route('shop.index') }}"><x-site-logo class="auth-brand__logo" /><span>Seed Planta</span></a>
        <section class="auth-card">
            <p class="auth-eyebrow">{{ $code ?? '404' }}</p>
            <h1>{{ $heading ?? 'We could not find that page' }}</h1>
            <p>{{ $message ?? 'The link may be outdated, or the product is no longer listed.' }}</p>
            <p class="auth-footer"><a href="{{ route('shop.index') }}">Back to the shop</a></p>
        </section>
    </main>
</body>
</html>
