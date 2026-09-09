@props(['currency' => '₹', 'includeProductModal' => true])

<div class="cart-overlay" id="cart-overlay" hidden></div>
<aside class="cart-drawer" id="cart-drawer" aria-label="Shopping cart" hidden>
    <div class="cart-drawer__header">
        <h2>Your Cart</h2>
        <button type="button" class="cart-drawer__close" id="cart-close" aria-label="Close cart">&times;</button>
    </div>
    <div class="cart-drawer__items" id="cart-items">
        <p class="cart-empty" id="cart-empty">Your cart is empty. Add some seeds!</p>
    </div>
    <div class="cart-drawer__footer" id="cart-footer" hidden>
        <div class="cart-total">
            <span>Total</span>
            <strong id="cart-total">{{ $currency }}0</strong>
        </div>
        <button type="button" class="btn btn--whatsapp" id="btn-purchase">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            Purchase on WhatsApp
        </button>
        <a href="{{ route('checkout') }}" class="btn btn--cart-full">Checkout</a>
        <button type="button" class="btn btn--ghost" id="btn-clear-cart">Clear Cart</button>
    </div>
</aside>

<div class="cart-overlay" id="wishlist-overlay" hidden></div>
<aside class="cart-drawer" id="wishlist-drawer" aria-label="Wishlist" hidden>
    <div class="cart-drawer__header">
        <h2>Your Wishlist</h2>
        <button type="button" class="cart-drawer__close" id="wishlist-close" aria-label="Close wishlist">&times;</button>
    </div>
    <div class="cart-drawer__items" id="wishlist-items">
        <p class="cart-empty" id="wishlist-empty">Your wishlist is empty. Tap the heart on a product.</p>
    </div>
    <div class="cart-drawer__footer" id="wishlist-footer" hidden>
        <button type="button" class="btn btn--cart-full" id="btn-wishlist-to-cart">Move all to cart</button>
        <button type="button" class="btn btn--ghost" id="btn-clear-wishlist">Clear Wishlist</button>
    </div>
</aside>

@if ($includeProductModal)
    <div class="product-overlay" id="product-overlay" hidden></div>
    <div class="product-modal" id="product-modal" role="dialog" aria-modal="true" aria-labelledby="pd-name" hidden>
        <button type="button" class="product-modal__close" id="product-close" aria-label="Close details">&times;</button>
        <div class="product-modal__toolbar">
            <button type="button" class="product-modal__tool" id="pd-wishlist" aria-label="Add product to wishlist">♡</button>
            <button type="button" class="product-modal__tool" id="pd-share" aria-label="Share product">↗</button>
        </div>
        <div class="product-modal__grid">
            <div class="product-modal__media" id="pd-media"></div>
            <div class="product-modal__info">
                <span class="product-modal__cat" id="pd-cat"></span>
                <h2 id="pd-name"></h2>
                <div class="product-modal__rating" id="pd-rating"></div>
                <div class="product-modal__facts"><span id="pd-unit"></span><span id="pd-stock"></span></div>
                <div class="product-modal__price" id="pd-price"></div>
                <div class="product-modal__delivery"><strong>Standard Delivery</strong><span id="pd-delivery"></span></div>
                <p class="product-modal__desc" id="pd-desc"></p>
                <ul class="product-modal__points">
                    <li>High germination quality seeds</li>
                    <li>Packed fresh for your climate</li>
                    <li>Easy WhatsApp order &amp; dispatch</li>
                </ul>
                <div class="product-modal__buy">
                    <div class="cart-qty product-modal__qty">
                        <button type="button" class="cart-qty__btn" id="pd-qty-minus" aria-label="Decrease quantity">−</button>
                        <span class="cart-qty__value" id="pd-qty">1</span>
                        <button type="button" class="cart-qty__btn" id="pd-qty-plus" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" class="btn btn--cart-full" id="pd-add-cart">Add to Cart</button>
                </div>
                <div class="product-modal__reviews" id="pd-reviews"></div>
            </div>
        </div>
    </div>
@endif

<div class="modal-overlay" id="contact-overlay" hidden></div>
<div class="modal" id="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-title" hidden>
    <div class="modal__header">
        <div>
            <h2 id="contact-title">Contact Us</h2>
            <p class="modal__sub">Fill details — query is optional.</p>
        </div>
        <button type="button" class="modal__close" id="contact-close" aria-label="Close">&times;</button>
    </div>

    <div class="modal__body">
        <div class="modal__alert modal__alert--success" id="contact-success" hidden></div>
        <div class="modal__alert modal__alert--error" id="contact-error" hidden></div>

        <form id="contact-form" method="POST" action="{{ route('contact.store', absolute: false) }}" class="modal-form" novalidate>
            <div class="modal-grid">
                <label class="modal-field">
                    <span>Name *</span>
                    <input name="name" type="text" required maxlength="100" placeholder="Your name">
                </label>
                <label class="modal-field">
                    <span>Mobile *</span>
                    <input name="mobile" type="text" required maxlength="25" placeholder="Your mobile number">
                </label>
                <label class="modal-field modal-field--full">
                    <span>Email *</span>
                    <input name="email" type="email" required maxlength="255" placeholder="you@example.com">
                </label>
                <label class="modal-field modal-field--full">
                    <span>Address *</span>
                    <input name="address" type="text" required maxlength="255" placeholder="Your address">
                </label>
                <label class="modal-field modal-field--full">
                    <span>Query (optional)</span>
                    <textarea name="query" rows="3" maxlength="2000" placeholder="Write your message (optional)"></textarea>
                </label>
            </div>

            <div class="modal__actions">
                <span class="modal__note">Fields marked * are required.</span>
                <button type="submit" class="modal__submit">Submit</button>
            </div>
        </form>
    </div>
</div>
