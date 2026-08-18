<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Seeds Bazar</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-card__header">
            <span class="login-card__icon">🌱</span>
            <h1>Admin Login</h1>
            <p>Seeds Bazar management panel</p>
        </div>

        @if ($errors->any())
            <div class="alert alert--error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="login-form">
            @csrf
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="seeds@gmail.com">
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required placeholder="••••••••">
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>
            <button type="submit" class="btn btn--primary btn--block">Sign In</button>
        </form>

        <p class="login-card__footer">
            <a href="{{ route('shop.index') }}">← Back to store</a>
        </p>
    </div>
</body>
</html>
