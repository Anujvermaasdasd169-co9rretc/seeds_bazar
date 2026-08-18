(function () {
    'use strict';

    const STORAGE_KEY = 'seeds_bazar_cart';
    const shop = document.getElementById('shop-app');
    if (!shop) return;

    const whatsappNumber = shop.dataset.whatsapp || '';
    const currency = shop.dataset.currency || '₹';
    let products = [];
    try {
        products = JSON.parse(shop.dataset.products || '[]');
    } catch (e) {
        products = [];
    }

    const cartToggle = document.getElementById('cart-toggle');
    const cartClose = document.getElementById('cart-close');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartCount = document.getElementById('cart-count');
    const cartItems = document.getElementById('cart-items');
    const cartEmpty = document.getElementById('cart-empty');
    const cartFooter = document.getElementById('cart-footer');
    const cartTotal = document.getElementById('cart-total');
    const btnPurchase = document.getElementById('btn-purchase');
    const btnClearCart = document.getElementById('btn-clear-cart');
    const toast = document.getElementById('toast');

    function getCart() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    }

    function findProduct(id) {
        return products.find((p) => p.id === id);
    }

    function getCartCount(cart) {
        return cart.reduce((sum, item) => sum + item.quantity, 0);
    }

    function getCartTotal(cart) {
        return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    }

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 2500);
    }

    function openCart() {
        cartOverlay.hidden = false;
        cartDrawer.hidden = false;
        requestAnimationFrame(() => {
            cartOverlay.classList.add('is-open');
            cartDrawer.classList.add('is-open');
        });
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        cartOverlay.classList.remove('is-open');
        cartDrawer.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(() => {
            cartOverlay.hidden = true;
            cartDrawer.hidden = true;
        }, 300);
    }

    function cartThumbHtml(item) {
        if (item.image) {
            return `<img src="${escapeAttr(item.image)}" alt="">`;
        }
        return escapeHtml(item.emoji || '🌱');
    }

    function escapeAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;');
    }

    function addToCart(id, name, price, unit, emoji, image) {
        const cart = getCart();
        const existing = cart.find((item) => item.id === id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({ id, name, price, unit, emoji, image: image || '', quantity: 1 });
        }
        saveCart(cart);
        renderCart();
        showToast(`${name} added to cart`);
    }

    function updateQuantity(id, delta) {
        const cart = getCart();
        const item = cart.find((i) => i.id === id);
        if (!item) return;
        item.quantity += delta;
        if (item.quantity <= 0) {
            saveCart(cart.filter((i) => i.id !== id));
        } else {
            saveCart(cart);
        }
        renderCart();
    }

    function removeFromCart(id) {
        saveCart(getCart().filter((i) => i.id !== id));
        renderCart();
    }

    function clearCart() {
        saveCart([]);
        renderCart();
        showToast('Cart cleared');
    }

    function renderCart() {
        const cart = getCart();
        const count = getCartCount(cart);
        const total = getCartTotal(cart);

        cartCount.textContent = count;
        cartCount.dataset.count = count;

        if (count === 0) {
            cartEmpty.hidden = false;
            cartFooter.hidden = true;
            cartItems.querySelectorAll('.cart-item').forEach((el) => el.remove());
            return;
        }

        cartEmpty.hidden = true;
        cartFooter.hidden = false;
        cartTotal.textContent = currency + total.toLocaleString('en-IN');

        // Backfill image URLs for older cart entries (saved before image support)
        let shouldResave = false;
        cart.forEach((item) => {
            if (!item.image) {
                const p = findProduct(item.id);
                if (p?.image) {
                    item.image = p.image;
                    shouldResave = true;
                }
            }
        });
        if (shouldResave) saveCart(cart);

        cartItems.querySelectorAll('.cart-item').forEach((el) => el.remove());

        cart.forEach((item) => {
            const lineTotal = item.price * item.quantity;
            const unitPrice = item.price;
            const el = document.createElement('div');
            el.className = 'cart-item';
            el.innerHTML = `
                <div class="cart-item__thumb">${cartThumbHtml(item)}</div>
                <div class="cart-item__info">
                    <div class="cart-item__toprow">
                        <div class="cart-item__title">
                            <div class="cart-item__name">${escapeHtml(item.name)}</div>
                            <div class="cart-item__unit">${escapeHtml(item.unit)}</div>
                        </div>
                        <button type="button" class="cart-item__remove" data-remove data-id="${item.id}" aria-label="Remove item">×</button>
                    </div>

                    <div class="cart-item__meta">
                        <span class="cart-item__unitprice">${currency}${unitPrice.toLocaleString('en-IN')} each</span>
                        <strong class="cart-item__price">${currency}${lineTotal.toLocaleString('en-IN')}</strong>
                    </div>

                    <div class="cart-item__qtyrow">
                        <div class="cart-qty">
                            <button type="button" class="cart-qty__btn" data-qty-minus data-id="${item.id}" aria-label="Decrease quantity">−</button>
                            <span class="cart-qty__value">${item.quantity}</span>
                            <button type="button" class="cart-qty__btn" data-qty-plus data-id="${item.id}" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="button" class="cart-item__remove-text" data-remove data-id="${item.id}">Remove</button>
                    </div>
                </div>
            `;
            cartItems.appendChild(el);
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function buildWhatsAppMessage(cart) {
        const total = getCartTotal(cart);
        let lines = [
            '🌱 *Seeds Bazar — New Order*',
            '',
            '*Order Details:*',
        ];

        cart.forEach((item, index) => {
            const sub = item.price * item.quantity;
            lines.push(
                `${index + 1}. ${item.name}`,
                `   Qty: ${item.quantity} × ${currency}${item.price} = ${currency}${sub}`,
                `   (${item.unit})`,
            );
        });

        lines.push(
            '',
            `*Grand Total: ${currency}${total.toLocaleString('en-IN')}*`,
            '',
            'Please confirm availability and delivery details.',
            'Thank you!',
        );

        return lines.join('\n');
    }

    function purchaseOnWhatsApp() {
        const cart = getCart();
        if (cart.length === 0) {
            showToast('Your cart is empty');
            openCart();
            return;
        }

        if (!whatsappNumber) {
            showToast('WhatsApp number not configured');
            return;
        }

        const message = buildWhatsAppMessage(cart);
        const phone = whatsappNumber.replace(/\D/g, '');
        const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

        window.open(url, '_blank', 'noopener,noreferrer');
    }

    // Category filters
    document.querySelectorAll('.filter-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            const category = btn.dataset.category;
            document.querySelectorAll('.product-card').forEach((card) => {
                const match = category === 'all' || card.dataset.category === category;
                card.classList.toggle('is-hidden', !match);
            });
        });
    });

    // Add to cart buttons
    document.querySelectorAll('[data-add-to-cart]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = parseInt(btn.dataset.id, 10);
            addToCart(
                id,
                btn.dataset.name,
                parseFloat(btn.dataset.price),
                btn.dataset.unit,
                btn.dataset.emoji,
                btn.dataset.image || '',
            );
            btn.classList.add('added');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = btn.querySelector('svg')
                ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> Added'
                : 'Added ✓';
            setTimeout(() => {
                btn.classList.remove('added');
                btn.innerHTML = originalHtml;
            }, 1500);
        });
    });

    // Cart drawer events
    cartToggle?.addEventListener('click', openCart);
    cartClose?.addEventListener('click', closeCart);
    cartOverlay?.addEventListener('click', closeCart);
    btnPurchase?.addEventListener('click', purchaseOnWhatsApp);
    btnClearCart?.addEventListener('click', clearCart);

    cartItems?.addEventListener('click', (e) => {
        const minus = e.target.closest('[data-qty-minus]');
        const plus = e.target.closest('[data-qty-plus]');
        const remove = e.target.closest('[data-remove]');
        if (minus) updateQuantity(parseInt(minus.dataset.id, 10), -1);
        if (plus) updateQuantity(parseInt(plus.dataset.id, 10), 1);
        if (remove) removeFromCart(parseInt(remove.dataset.id, 10));
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCart();
            closeContact();
        }
    });

    renderCart();

    // Wishlist toggle (saved in localStorage)
    const WISH_KEY = 'seeds_bazar_wishlist';
    function getWishlist() {
        try {
            return JSON.parse(localStorage.getItem(WISH_KEY) || '[]');
        } catch {
            return [];
        }
    }
    function saveWishlist(ids) {
        localStorage.setItem(WISH_KEY, JSON.stringify(ids));
    }
    function syncWishlistUi() {
        const ids = getWishlist();
        document.querySelectorAll('[data-wishlist]').forEach((btn) => {
            const id = parseInt(btn.dataset.wishlist, 10);
            btn.classList.toggle('is-active', ids.includes(id));
        });
    }
    document.querySelectorAll('[data-wishlist]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = parseInt(btn.dataset.wishlist, 10);
            let ids = getWishlist();
            if (ids.includes(id)) {
                ids = ids.filter((i) => i !== id);
            } else {
                ids.push(id);
            }
            saveWishlist(ids);
            syncWishlistUi();
        });
    });
    syncWishlistUi();

    // Contact modal (popup)
    const contactOpen = document.getElementById('contact-open');
    const contactOverlay = document.getElementById('contact-overlay');
    const contactModal = document.getElementById('contact-modal');
    const contactClose = document.getElementById('contact-close');
    const contactForm = document.getElementById('contact-form');
    const contactSuccess = document.getElementById('contact-success');
    const contactError = document.getElementById('contact-error');

    function openContact() {
        if (!contactOverlay || !contactModal) return;
        contactOverlay.hidden = false;
        contactModal.hidden = false;
        requestAnimationFrame(() => {
            contactOverlay.classList.add('is-open');
            contactModal.classList.add('is-open');
        });
        document.body.style.overflow = 'hidden';
        contactError.hidden = true;
        contactSuccess.hidden = true;
        contactForm?.querySelector('input[name="name"]')?.focus();
    }

    function closeContact() {
        if (!contactOverlay || !contactModal) return;
        contactOverlay.classList.remove('is-open');
        contactModal.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(() => {
            contactOverlay.hidden = true;
            contactModal.hidden = true;
        }, 220);
    }

    contactOpen?.addEventListener('click', openContact);
    contactClose?.addEventListener('click', closeContact);
    contactOverlay?.addEventListener('click', closeContact);

    contactForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        contactError.hidden = true;
        contactSuccess.hidden = true;

        const formData = new FormData(contactForm);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch(contactForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!res.ok) {
                const data = await res.json().catch(() => null);
                const msg = data?.message || 'Please check your details and try again.';
                const errs = data?.errors ? Object.values(data.errors).flat().join(' ') : '';
                contactError.textContent = (errs ? errs : msg);
                contactError.hidden = false;
                return;
            }

            const data = await res.json().catch(() => ({ ok: true }));
            contactSuccess.textContent = data?.message || 'Submitted successfully.';
            contactSuccess.hidden = false;
            contactForm.reset();
            showToast('Contact submitted');
        } catch {
            contactError.textContent = 'Network error. Please try again.';
            contactError.hidden = false;
        }
    });
})();
