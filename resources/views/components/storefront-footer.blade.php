<footer class="footer footer--standalone">
    <div class="footer__inner">
        <div class="footer__brand">
            <a href="{{ route('shop.index') }}" class="footer__logo">
                <x-site-logo class="footer__logo-icon" />
                <span><strong>Seed Planta</strong><small>Quality seeds for every garden</small></span>
            </a>
            <p class="footer__about">Grow something good with carefully selected seeds and helpful support.</p>
        </div>

        <div class="footer__col">
            <h3>Shop</h3>
            <a href="{{ route('shop.index') }}#products-grid">All products</a>
            <a href="{{ route('contact.show') }}">Contact Us</a>
        </div>

        <div class="footer__col">
            <h3>Policies</h3>
            <a href="{{ route('policies.shipping') }}">Shipping &amp; delivery</a>
            <a href="{{ route('policies.returns') }}">Returns &amp; refunds</a>
            <a href="{{ route('policies.privacy') }}">Privacy Policy</a>
            <a href="{{ route('policies.terms') }}">Terms &amp; Conditions</a>
        </div>
    </div>
    <div class="footer__bottom">
        <p>&copy; {{ date('Y') }} Seed Planta. All rights reserved.</p>
        <p><a href="{{ route('policies.privacy') }}">Privacy</a> · <a href="{{ route('policies.terms') }}">Terms</a></p>
    </div>
</footer>
