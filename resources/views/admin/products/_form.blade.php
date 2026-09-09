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
                    {{ str_repeat('— ', max(0, (int) ($category->depth ?? 1) - 1)) }}{{ $category->name }}
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
        <span>Stock quantity *</span>
        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}" required min="0" max="4294967295" step="1">
    </label>

    <label class="form-field">
        <span>Unit *</span>
        <input type="text" name="unit" value="{{ old('unit', $product?->unit) }}" required maxlength="100" placeholder="50g pack">
    </label>

    <label class="form-field form-field--full">
        <span>Description</span>
        <textarea name="description" rows="3" maxlength="1000">{{ old('description', $product?->description) }}</textarea>
    </label>

    <div class="form-field form-field--full">
        <span>Growing information <small class="field-hint">Shown automatically on the product detail page. Leave a field empty if it does not apply.</small></span>
        <div class="form-grid" style="margin-top: .65rem;">
            <label class="form-field"><span>Best sowing season</span><input type="text" name="sowing_season" value="{{ old('sowing_season', $product?->sowing_season) }}" maxlength="100" placeholder="July–September"></label>
            <label class="form-field"><span>Sunlight</span><input type="text" name="sunlight" value="{{ old('sunlight', $product?->sunlight) }}" maxlength="100" placeholder="Full sun"></label>
            <label class="form-field"><span>Germination</span><input type="text" name="germination_days" value="{{ old('germination_days', $product?->germination_days) }}" maxlength="100" placeholder="6–10 days"></label>
            <label class="form-field"><span>First harvest</span><input type="text" name="harvest_days" value="{{ old('harvest_days', $product?->harvest_days) }}" maxlength="100" placeholder="60–75 days"></label>
            <label class="form-field"><span>Plant spacing</span><input type="text" name="plant_spacing" value="{{ old('plant_spacing', $product?->plant_spacing) }}" maxlength="100" placeholder="45 cm apart"></label>
            <label class="form-field"><span>Sowing depth</span><input type="text" name="sowing_depth" value="{{ old('sowing_depth', $product?->sowing_depth) }}" maxlength="100" placeholder="0.5 cm deep"></label>
            <label class="form-field"><span>Difficulty</span><select name="growing_difficulty"><option value="">Select difficulty</option>@foreach (['Easy', 'Moderate', 'Advanced'] as $level)<option value="{{ $level }}" @selected(old('growing_difficulty', $product?->growing_difficulty) === $level)>{{ $level }}</option>@endforeach</select></label>
        </div>
    </div>

    <label class="form-field checkbox-field">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
        <span>Show on storefront</span>
    </label>
</div>
