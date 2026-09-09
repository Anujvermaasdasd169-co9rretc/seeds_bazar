<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Account') - {{ config('app.name', 'Seed Planta') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/seeds-bazar.css') }}">
</head>
<body class="auth-shell">
    <main class="auth-panel">
        <a class="auth-brand" href="{{ route('shop.index') }}">Seed Planta</a>
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>