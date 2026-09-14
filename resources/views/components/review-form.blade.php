@props([
    'products' => [],
    'selectedProductId' => null,
    'redirectTo' => 'home',
    'defaultName' => '',
])

<form method="POST" action="{{ route('reviews.store') }}" class="review-form review-form--peak">
    @csrf
    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
    @if ($selectedProductId)
        <input type="hidden" name="product_id" value="{{ $selectedProductId }}">
    @else
        <label class="review-field">
            <span>Product</span>
            <select name="product_id" required>
                <option value="">Choose a product you grew</option>
                @foreach ($products as $product)
                    <option value="{{ is_array($product) ? $product['id'] : $product->id }}" @selected(old('product_id') == (is_array($product) ? $product['id'] : $product->id))>{{ is_array($product) ? $product['name'] : $product->name }}</option>
                @endforeach
            </select>
        </label>
    @endif
    <label class="review-field">
        <span>Your name</span>
        <input type="text" name="name" value="{{ old('name', $defaultName) }}" maxlength="100" required placeholder="How should we credit you?">
    </label>
    <fieldset class="review-field review-stars">
        <legend>Your rating</legend>
        <div class="star-picker">
            @for ($rating = 5; $rating >= 1; $rating--)
                <input type="radio" name="rating" id="{{ $redirectTo }}-star-{{ $rating }}" value="{{ $rating }}" @checked(old('rating', 5) == $rating) required>
                <label for="{{ $redirectTo }}-star-{{ $rating }}" title="{{ $rating }} stars">★</label>
            @endfor
        </div>
    </fieldset>
    <label class="review-field review-field--wide">
        <span>How did it grow?</span>
        <textarea name="comment" rows="3" maxlength="1000" required placeholder="Germination, flavour, garden tips…">{{ old('comment') }}</textarea>
    </label>
    <button type="submit" class="review-submit">Share review</button>
</form>
