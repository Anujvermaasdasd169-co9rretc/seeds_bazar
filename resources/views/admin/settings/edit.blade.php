@extends('layouts.admin')

@section('title', 'Store Settings')

@section('content')
<div class="page-header">
    <h1>Site Logo</h1>
    <p>Upload a logo — it will show on the store and admin panel.</p>
</div>

<div class="card" style="max-width: 520px;">
    <h2 class="card__title">Storefront &amp; shipping</h2>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <label class="form-field form-field--full"><span>Store tagline</span><input type="text" name="tagline" value="{{ old('tagline', $settings['tagline']) }}" required maxlength="160"></label>
            <label class="form-field form-field--full"><span>WhatsApp number</span><input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" required maxlength="30"><small class="field-hint">Include country code. Example: 919876543210</small></label>
            <label class="form-field"><span>Shipping charge (₹)</span><input type="number" name="shipping_flat_rate" value="{{ old('shipping_flat_rate', $settings['shipping_flat_rate']) }}" required min="0" step="0.01"></label>
            <label class="form-field"><span>Free shipping above (₹)</span><input type="number" name="free_shipping_threshold" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold']) }}" required min="0" step="0.01"></label>
            <label class="form-field"><span>Delivery estimate</span><input type="text" name="shipping_estimate" value="{{ old('shipping_estimate', $settings['shipping_estimate']) }}" required maxlength="100"></label>
            <label class="form-field"><span>Shipping method</span><input type="text" name="shipping_method" value="{{ old('shipping_method', $settings['shipping_method']) }}" required maxlength="50"></label>
            <label class="form-field form-field--full"><span>Shipping policy content</span><textarea name="policy_shipping" rows="5" maxlength="10000" placeholder="Optional: replacing the default shipping policy page content.">{{ old('policy_shipping', $settings['policy_shipping']) }}</textarea></label>
            <label class="form-field form-field--full"><span>Returns & refunds policy content</span><textarea name="policy_returns" rows="5" maxlength="10000" placeholder="Optional: replacing the default returns policy page content.">{{ old('policy_returns', $settings['policy_returns']) }}</textarea></label>
            <label class="form-field form-field--full"><span>Privacy policy content</span><textarea name="policy_privacy" rows="5" maxlength="10000" placeholder="Optional: replacing the default privacy policy page content.">{{ old('policy_privacy', $settings['policy_privacy']) }}</textarea></label>
            <label class="form-field form-field--full"><span>Terms & conditions content</span><textarea name="policy_terms" rows="5" maxlength="10000" placeholder="Optional: replacing the default terms page content.">{{ old('policy_terms', $settings['policy_terms']) }}</textarea></label>
        </div>
        <div class="form-actions" style="margin-top: 1rem; padding-top: 0; border: none;"><button type="submit" class="btn btn--primary">Save Store Settings</button></div>
    </form>

    <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #eee;">
    <h2 class="card__title">Site logo</h2>
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
