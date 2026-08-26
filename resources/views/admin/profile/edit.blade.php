@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<div class="page-header">
    <h1>Profile</h1>
    <p>Update your admin name, email, and password.</p>
</div>

<div class="card" style="max-width: 560px;">
    <form method="POST" action="{{ route('admin.profile.update') }}" class="admin-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <label class="form-field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100">
            </label>
            <label class="form-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
            </label>
            <label class="form-field form-field--full">
                <span>Current password</span>
                <input type="password" name="current_password" autocomplete="current-password">
                <small class="field-hint">Required only if you change the password.</small>
            </label>
            <label class="form-field">
                <span>New password</span>
                <input type="password" name="password" autocomplete="new-password">
            </label>
            <label class="form-field">
                <span>Confirm new password</span>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">Save Profile</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn--outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
