<section class="hero-slider" id="hero-slider" aria-label="Seed Planta highlights">
    <div class="hero-slider__track">
        <article class="hero-slide is-active" data-slide="0">
            <img src="{{ asset('banner/1.png') }}" alt="A productive garden filled with vegetables and plants" fetchpriority="high">
            <div class="hero-slide__shade"></div>
        </article>
        <article class="hero-slide" data-slide="1" aria-hidden="true">
            <img src="{{ asset('banner/2.png') }}" alt="Fresh fruit growing in a bright garden" loading="lazy">
            <div class="hero-slide__shade"></div>
        </article>
        <article class="hero-slide" data-slide="2" aria-hidden="true">
            <img src="{{ asset('banner/3.png') }}" alt="Colourful flowering plants in a garden nursery" loading="lazy">
            <div class="hero-slide__shade"></div>
        </article>
    </div>
    {{-- <div class="hero-slider__controls">
        <button type="button" class="hero-slider__arrow" data-slider-prev aria-label="Previous banner">←</button>
        <div class="hero-slider__dots" role="tablist" aria-label="Choose a banner">
            <button type="button" class="hero-slider__dot is-active" data-slider-dot="0" role="tab" aria-label="Show banner 1" aria-selected="true"></button>
            <button type="button" class="hero-slider__dot" data-slider-dot="1" role="tab" aria-label="Show banner 2" aria-selected="false"></button>
            <button type="button" class="hero-slider__dot" data-slider-dot="2" role="tab" aria-label="Show banner 3" aria-selected="false"></button>
        </div>
        <button type="button" class="hero-slider__arrow" data-slider-next aria-label="Next banner">→</button>
    </div> --}}
    <div class="hero-slider__progress" aria-hidden="true"><span></span></div>
</section>
<script src="{{ asset('js/banner-slider.js') }}?v={{ @filemtime(public_path('js/banner-slider.js')) ?: time() }}" defer></script>
