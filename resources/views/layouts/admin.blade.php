<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Seed Planta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: time() }}">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <x-site-logo class="admin-brand__logo" />
            {{-- <span>Seed Planta</span> --}}
        </a>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">Categories</a>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">Products</a>
            <a href="{{ route('admin.contacts.index') }}" class="{{ request()->routeIs('admin.contacts.*') ? 'is-active' : '' }}">Contacts</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">Logo</a>
            <a href="{{ route('shop.index') }}" target="_blank" rel="noopener">View Store</a>
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="admin-logout">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </aside>

    <div class="admin-shell">
        <header class="admin-topbar">
            <p class="admin-topbar__page">@yield('title', 'Admin')</p>
            <div class="admin-profile" id="admin-profile">
                <button type="button" class="admin-profile__btn" id="admin-profile-toggle" aria-expanded="false" aria-controls="admin-profile-menu">
                    <span class="admin-profile__avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="admin-profile__meta">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>Admin</small>
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="admin-profile__menu" id="admin-profile-menu" hidden>
                    <p class="admin-profile__email">{{ auth()->user()->email }}</p>
                    <a href="{{ route('admin.profile.edit') }}" class="{{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}">Profile</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="admin-main">
        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert--error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    </div>

    @stack('scripts')
    <script>
        (function () {
            var root = document.getElementById('admin-profile');
            var btn = document.getElementById('admin-profile-toggle');
            var menu = document.getElementById('admin-profile-menu');
            if (!root || !btn || !menu) return;
            function close() {
                menu.hidden = true;
                root.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
            }
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = menu.hidden;
                menu.hidden = !open;
                root.classList.toggle('is-open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            document.addEventListener('click', function (e) {
                if (!root.contains(e.target)) close();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') close();
            });
        })();
    </script>
</body>
</html>
