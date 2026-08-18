@php
    $product = $product ?? null;
@endphp

<div class="form-grid">
    <label class="form-field form-field--full">
        <span>Product name *</span>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" required maxlength="255">
    </label>

    <label class="form-field">
        <span>Category *</span>
        <select name="category_id" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-field form-field--full">
        <span>Product image</span>
        <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
        <small class="field-hint">JPG, PNG, WEBP or GIF. If empty, emoji icon shows on store.</small>
        @if ($product?->image)
            <div class="image-preview">
                <img src="{{ $product->image_url }}" alt="Current product image">
                <label class="checkbox-field image-preview__remove">
                    <input type="checkbox" name="remove_image" value="1">
                    <span>Remove current image</span>
                </label>
            </div>
        @endif
    </label>

    <label class="form-field">
        <span>Emoji icon (fallback)</span>
        <input type="text" name="emoji" value="{{ old('emoji', $product?->emoji ?? '🌱') }}" maxlength="16" placeholder="🌱">
    </label>

    <label class="form-field">
        <span>Price (₹) *</span>
        <input type="number" name="price" value="{{ old('price', $product?->price) }}" required min="0" step="0.01">
    </label>

    <label class="form-field">
        <span>Unit *</span>
        <input type="text" name="unit" value="{{ old('unit', $product?->unit) }}" required maxlength="100" placeholder="50g pack">
    </label>

    <label class="form-field form-field--full">
        <span>Description</span>
        <textarea name="description" rows="3" maxlength="1000">{{ old('description', $product?->description) }}</textarea>
    </label>

    <label class="form-field checkbox-field">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
        <span>Show on storefront</span>
    </label>
</div>
