@extends('layouts.admin')

@section('title', 'Site Logo')

@section('content')
<div class="page-header">
    <h1>Site Logo</h1>
    <p>Upload a logo — it will show on the store and admin panel.</p>
</div>

<div class="card" style="max-width: 520px;">
    @if ($logoUrl)
        <div class="logo-preview">
            <p class="card__title">Current logo</p>
            <img src="{{ $logoUrl }}" alt="Site logo" class="logo-preview__img">
            <form method="POST" action="{{ route('admin.settings.logo.remove') }}" class="logo-preview__remove">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--sm btn--danger" onclick="return confirm('Remove logo?')">Remove Logo</button>
            </form>
        </div>
        <hr style="margin: 1.25rem 0; border: none; border-top: 1px solid #eee;">
    @endif

    <h2 class="card__title">{{ $logoUrl ? 'Replace logo' : 'Upload logo' }}</h2>
    <form method="POST" action="{{ route('admin.settings.logo') }}" enctype="multipart/form-data" class="admin-form">
        @csrf
        @method('PUT')
        <label class="form-field">
            <span>Logo image</span>
            <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif,image/svg+xml" required>
            <small class="field-hint">PNG, JPG, WEBP, GIF or SVG recommended.</small>
        </label>
        <div class="form-actions" style="margin-top: 1rem; padding-top: 0; border: none;">
            <button type="submit" class="btn btn--primary">Save Logo</button>
        </div>
    </form>
</div>
@endsection
