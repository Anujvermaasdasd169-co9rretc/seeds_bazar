@props(['image' => null, 'emoji' => '🌱', 'class' => 'product-card__media'])

<div {{ $attributes->merge(['class' => $class . ($image ? ' product-card__media--photo' : ' product-card__media--emoji')]) }}>
  <div class="product-card__media-inner">
    @if ($image)
      <img src="{{ $image }}" alt="" class="product-visual__img" loading="lazy">
    @else
      <span class="product-visual__emoji">{{ $emoji }}</span>
    @endif
  </div>
</div>
