(() => {
    const heroSlider = document.getElementById('hero-slider');
    if (!heroSlider) return;

    const slides = [...heroSlider.querySelectorAll('.hero-slide')];
    const dots = [...heroSlider.querySelectorAll('[data-slider-dot]')];
    if (!slides.length) return;

    let currentSlide = 0;
    let autoplayTimer;

    function showSlide(index) {
        currentSlide = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            const active = slideIndex === currentSlide;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach((dot, dotIndex) => {
            const active = dotIndex === currentSlide;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    }

    function startAutoplay() {
        clearInterval(autoplayTimer);
        heroSlider.classList.add('is-playing');
        autoplayTimer = setInterval(() => showSlide(currentSlide + 1), 3000);
    }

    function pauseAutoplay() {
        clearInterval(autoplayTimer);
        heroSlider.classList.remove('is-playing');
    }

    heroSlider.querySelector('[data-slider-prev]')?.addEventListener('click', () => {
        showSlide(currentSlide - 1);
        startAutoplay();
    });
    heroSlider.querySelector('[data-slider-next]')?.addEventListener('click', () => {
        showSlide(currentSlide + 1);
        startAutoplay();
    });
    dots.forEach((dot) => dot.addEventListener('click', () => {
        showSlide(Number(dot.dataset.sliderDot));
        startAutoplay();
    }));
    heroSlider.addEventListener('mouseenter', pauseAutoplay);
    heroSlider.addEventListener('mouseleave', startAutoplay);
    heroSlider.addEventListener('focusin', pauseAutoplay);
    heroSlider.addEventListener('focusout', (event) => {
        if (!heroSlider.contains(event.relatedTarget)) startAutoplay();
    });
    heroSlider.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') showSlide(currentSlide - 1);
        if (event.key === 'ArrowRight') showSlide(currentSlide + 1);
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') startAutoplay();
    });

    showSlide(0);
    startAutoplay();
})();
