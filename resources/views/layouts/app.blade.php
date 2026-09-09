<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Seed Planta'))</title>
    <meta name="description" content="@yield('meta_description', 'Seed Planta — quality seeds for home gardens, farms, and growing spaces.')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', config('app.name', 'Seed Planta'))">
    <meta property="og:description" content="@yield('meta_description', 'Seed Planta — quality seeds for home gardens, farms, and growing spaces.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/seeds-bazar.css') }}?v={{ @filemtime(public_path('css/seeds-bazar.css')) ?: time() }}">
    @stack('head')
</head>
<body>
    @yield('content')

    <div id="toast" class="toast" role="status" aria-live="polite"></div>

    @stack('scripts')
</body>
</html>
