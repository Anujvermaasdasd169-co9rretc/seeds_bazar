(function () {
    'use strict';

    const STORAGE_KEY = 'seeds_bazar_cart';
    const shop = document.getElementById('shop-app');
    if (!shop) return;

    const whatsappNumber = shop.dataset.whatsapp || '';
    const currency = shop.dataset.currency || '₹';
    let products = [];
    try {
        const productsEl = document.getElementById('shop-products');
        products = JSON.parse(productsEl ? productsEl.textContent : (shop.dataset.products || '[]'));
        if (!Array.isArray(products)) products = [];
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
    const wishlistToggle = document.getElementById('wishlist-toggle');
    const wishlistClose = document.getElementById('wishlist-close');
    const wishlistOverlay = document.getElementById('wishlist-overlay');
    const wishlistDrawer = document.getElementById('wishlist-drawer');
    const wishlistCount = document.getElementById('wishlist-count');
    const wishlistItems = document.getElementById('wishlist-items');
    const wishlistEmpty = document.getElementById('wishlist-empty');
    const wishlistFooter = document.getElementById('wishlist-footer');
    const btnWishlistToCart = document.getElementById('btn-wishlist-to-cart');
    const btnClearWishlist = document.getElementById('btn-clear-wishlist');

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
        const numId = Number(id);
        const fromList = products.find((p) => Number(p.id) === numId);
        if (fromList) return fromList;
        const btn = document.querySelector('[data-wishlist="' + numId + '"]');
        if (!btn) return null;
        return {
            id: numId,
            name: btn.dataset.name || 'Product',
            price: parseFloat(btn.dataset.price || '0'),
            unit: btn.dataset.unit || '',
            emoji: btn.dataset.emoji || '🌱',
            image: btn.dataset.image || '',
        };
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
        closeWishlist(true);
        cartOverlay.hidden = false;
        cartDrawer.hidden = false;
        requestAnimationFrame(() => {
            cartOverlay.classList.add('is-open');
            cartDrawer.classList.add('is-open');
        });
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        cartOverlay?.classList.remove('is-open');
        cartDrawer?.classList.remove('is-open');
        setTimeout(() => {
            if (cartOverlay) cartOverlay.hidden = true;
            if (cartDrawer) cartDrawer.hidden = true;
        }, 300);
        if (!wishlistDrawer?.classList.contains('is-open') && !document.getElementById('contact-modal')?.classList.contains('is-open')) {
            document.body.style.overflow = '';
        }
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

    function addToCart(id, name, price, unit, emoji, image, silent) {
        const cart = getCart();
        const existing = cart.find((item) => item.id === id);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({ id, name, price, unit, emoji, image: image || '', quantity: 1 });
        }
        saveCart(cart);
        renderCart();
        if (!silent) {
            showToast(`${name} added to cart`);
        }
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
        if (!cartCount || !cartItems) return;
        const cart = getCart();
        const count = getCartCount(cart);
        const total = getCartTotal(cart);

        cartCount.textContent = count;
        cartCount.dataset.count = count;

        if (count === 0) {
            if (cartEmpty) cartEmpty.hidden = false;
            if (cartFooter) cartFooter.hidden = true;
            cartItems.querySelectorAll('.cart-item').forEach((el) => el.remove());
            return;
        }

        if (cartEmpty) cartEmpty.hidden = true;
        if (cartFooter) cartFooter.hidden = false;
        if (cartTotal) cartTotal.textContent = currency + total.toLocaleString('en-IN');

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
            '🌱 *Seed Planta — New Order*',
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

    // Category filters + global search
    const searchRoot = document.getElementById('header-search');
    const searchInput = document.getElementById('global-search');
    const searchClear = document.getElementById('search-clear');
    const searchResults = document.getElementById('search-results');
    const productsEmpty = document.getElementById('products-empty');
    let searchActiveIndex = -1;

    function activeCategory() {
        return document.querySelector('.filter-btn.is-active')?.dataset.category || 'all';
    }

    function searchQuery() {
        return (searchInput?.value || '').trim().toLowerCase();
    }

    function productMatchesQuery(product, q) {
        if (!q) return true;
        const hay = [
            product.name,
            product.category,
            product.category_name,
            product.unit,
            product.description,
        ].join(' ').toLowerCase();
        return hay.includes(q);
    }

    function applyProductFilters() {
        const category = activeCategory();
        const q = searchQuery();
        let visible = 0;
        document.querySelectorAll('.product-card').forEach((card) => {
            const id = parseInt(card.dataset.id, 10);
            const product = findProduct(id);
            const catOk = category === 'all' || card.dataset.category === category;
            const searchOk = product ? productMatchesQuery(product, q) : (card.dataset.name || '').toLowerCase().includes(q);
            const show = catOk && searchOk;
            card.classList.toggle('is-hidden', !show);
            if (show) visible += 1;
        });
        if (productsEmpty) productsEmpty.hidden = visible > 0;
    }

    function highlightName(name, q) {
        const safe = escapeHtml(name);
        if (!q) return safe;
        const idx = name.toLowerCase().indexOf(q);
        if (idx < 0) return safe;
        const before = escapeHtml(name.slice(0, idx));
        const match = escapeHtml(name.slice(idx, idx + q.length));
        const after = escapeHtml(name.slice(idx + q.length));
        return `${before}<mark>${match}</mark>${after}`;
    }

    function closeSearchDrop() {
        if (!searchResults || !searchInput || !searchRoot) return;
        searchResults.hidden = true;
        searchRoot.classList.remove('is-open');
        searchInput.setAttribute('aria-expanded', 'false');
        searchActiveIndex = -1;
    }

    function renderSearchDrop() {
        if (!searchResults || !searchInput) return;
        const q = searchQuery();
        if (searchClear) searchClear.hidden = !q;
        if (!q) {
            closeSearchDrop();
            applyProductFilters();
            return;
        }
        const matches = products.filter((p) => productMatchesQuery(p, q)).slice(0, 8);
        if (matches.length === 0) {
            searchResults.innerHTML = `<div class="header-search__empty">No matches for “${escapeHtml(searchInput.value.trim())}”</div>`;
        } else {
            searchResults.innerHTML = matches.map((p, i) => `
                <button type="button" class="header-search__item" role="option" data-search-id="${p.id}" data-index="${i}">
                    <span class="header-search__thumb">${p.image ? `<img src="${escapeAttr(p.image)}" alt="">` : escapeHtml(p.emoji || '🌱')}</span>
                    <span class="header-search__meta">
                        <span class="header-search__name">${highlightName(p.name, q)}</span>
                        <span class="header-search__cat">${escapeHtml(p.category_name || p.category || '')}</span>
                    </span>
                    <span class="header-search__price">${currency}${Number(p.price).toLocaleString('en-IN')}</span>
                </button>
            `).join('');
        }
        searchResults.hidden = false;
        searchRoot.classList.add('is-open');
        searchInput.setAttribute('aria-expanded', 'true');
        searchActiveIndex = -1;
        applyProductFilters();
    }

    function goToProduct(id) {
        const card = document.querySelector(`.product-card[data-id="${id}"]`);
        closeSearchDrop();
        if (!card) return;
        document.querySelectorAll('.filter-btn').forEach((b) => b.classList.toggle('is-active', b.dataset.category === 'all'));
        if (searchInput) searchInput.value = card.dataset.name || searchInput.value;
        if (searchClear) searchClear.hidden = !searchQuery();
        applyProductFilters();
        card.classList.remove('is-hidden');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.classList.add('is-search-hit');
        setTimeout(() => card.classList.remove('is-search-hit'), 1400);
    }

    document.querySelectorAll('.filter-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            applyProductFilters();
        });
    });

    searchInput?.addEventListener('input', renderSearchDrop);
    searchInput?.addEventListener('focus', () => {
        if (searchQuery()) renderSearchDrop();
    });
    searchClear?.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        closeSearchDrop();
        applyProductFilters();
        searchInput?.focus();
        if (searchClear) searchClear.hidden = true;
    });
    searchResults?.addEventListener('click', (e) => {
        const item = e.target.closest('[data-search-id]');
        if (item) goToProduct(parseInt(item.dataset.searchId, 10));
    });
    searchInput?.addEventListener('keydown', (e) => {
        const items = [...(searchResults?.querySelectorAll('[data-search-id]') || [])];
        if (e.key === 'ArrowDown' && items.length) {
            e.preventDefault();
            searchActiveIndex = (searchActiveIndex + 1) % items.length;
        } else if (e.key === 'ArrowUp' && items.length) {
            e.preventDefault();
            searchActiveIndex = (searchActiveIndex - 1 + items.length) % items.length;
        } else if (e.key === 'Enter' && items.length) {
            e.preventDefault();
            const chosen = items[Math.max(searchActiveIndex, 0)];
            if (chosen) goToProduct(parseInt(chosen.dataset.searchId, 10));
            return;
        } else if (e.key === 'Escape') {
            closeSearchDrop();
            searchInput.blur();
            return;
        } else {
            return;
        }
        items.forEach((el, i) => el.classList.toggle('is-active', i === searchActiveIndex));
        items[searchActiveIndex]?.scrollIntoView({ block: 'nearest' });
    });
    document.addEventListener('click', (e) => {
        if (searchRoot && !searchRoot.contains(e.target)) closeSearchDrop();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
            e.preventDefault();
            searchInput?.focus();
        }
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
            closeWishlist();
            closeContact();
        }
    });

    renderCart();

    // Wishlist (saved in localStorage) — header heart, not cart
    const WISH_KEY = 'seeds_bazar_wishlist';
    function saveWishlist(ids) {
        try {
            localStorage.setItem(WISH_KEY, JSON.stringify(ids));
        } catch (e) {
            showToast('Could not save wishlist in this browser');
        }
    }
    function getWishlist() {
        try {
            const raw = JSON.parse(localStorage.getItem(WISH_KEY) || '[]');
            if (!Array.isArray(raw)) return [];
            return raw.map((item) => Number(typeof item === 'object' && item ? item.id : item))
                .filter((id) => !Number.isNaN(id));
        } catch {
            return [];
        }
    }
    function openWishlist() {
        closeCart();
        if (!wishlistOverlay || !wishlistDrawer) return;
        wishlistOverlay.hidden = false;
        wishlistDrawer.hidden = false;
        requestAnimationFrame(() => {
            wishlistOverlay.classList.add('is-open');
            wishlistDrawer.classList.add('is-open');
        });
        document.body.style.overflow = 'hidden';
    }
    function closeWishlist(immediate) {
        if (!wishlistOverlay || !wishlistDrawer) return;
        wishlistOverlay.classList.remove('is-open');
        wishlistDrawer.classList.remove('is-open');
        const hide = () => {
            wishlistOverlay.hidden = true;
            wishlistDrawer.hidden = true;
        };
        if (immediate) hide();
        else setTimeout(hide, 300);
        if (!cartDrawer?.classList.contains('is-open') && !document.getElementById('contact-modal')?.classList.contains('is-open')) {
            document.body.style.overflow = '';
        }
    }
    function wishlistProductPayload(product) {
        return [
            product.id,
            product.name,
            parseFloat(product.price),
            product.unit || '',
            product.emoji || '🌱',
            product.image || '',
        ];
    }
    function renderWishlist() {
        const ids = getWishlist();
        if (wishlistCount) {
            wishlistCount.textContent = ids.length;
            wishlistCount.dataset.count = String(ids.length);
        }
        document.querySelectorAll('[data-wishlist]').forEach((btn) => {
            const id = parseInt(btn.dataset.wishlist, 10);
            btn.classList.toggle('is-active', ids.includes(id));
        });
        if (!wishlistItems) return;
        wishlistItems.querySelectorAll('.cart-item').forEach((el) => el.remove());
        const items = ids.map((id) => findProduct(id)).filter(Boolean);
        if (wishlistEmpty) wishlistEmpty.hidden = items.length > 0;
        if (wishlistFooter) wishlistFooter.hidden = items.length === 0;
        items.forEach((product) => {
            const el = document.createElement('div');
            el.className = 'cart-item';
            const unitPrice = Number(product.price) || 0;
            el.innerHTML = `
                <div class="cart-item__thumb">${cartThumbHtml(product)}</div>
                <div class="cart-item__info">
                    <div class="cart-item__toprow">
                        <div class="cart-item__title">
                            <div class="cart-item__name">${escapeHtml(product.name)}</div>
                            <div class="cart-item__unit">${escapeHtml(product.unit || '')}</div>
                        </div>
                        <button type="button" class="cart-item__remove" data-wish-remove data-id="${product.id}" aria-label="Remove from wishlist">×</button>
                    </div>
                    <div class="cart-item__meta">
                        <strong class="cart-item__price">${currency}${unitPrice.toLocaleString('en-IN')}</strong>
                    </div>
                    <div class="cart-item__qtyrow">
                        <button type="button" class="cart-item__remove-text" data-wish-to-cart data-id="${product.id}">Add to cart</button>
                        <button type="button" class="cart-item__remove-text" data-wish-remove data-id="${product.id}">Remove</button>
                    </div>
                </div>
            `;
            wishlistItems.appendChild(el);
        });
    }
    function toggleWishlist(id) {
        let ids = getWishlist();
        const product = findProduct(id);
        if (ids.includes(id)) {
            ids = ids.filter((i) => i !== id);
            showToast(product ? `${product.name} removed from wishlist` : 'Removed from wishlist');
        } else {
            ids.push(id);
            showToast(product ? `${product.name} added to wishlist` : 'Added to wishlist');
        }
        saveWishlist(ids);
        renderWishlist();
    }
    function removeFromWishlist(id) {
        saveWishlist(getWishlist().filter((i) => i !== id));
        renderWishlist();
    }
    function moveWishlistItemToCart(id) {
        const product = findProduct(id);
        if (!product) return;
        addToCart(...wishlistProductPayload(product));
        removeFromWishlist(id);
    }
    function moveAllWishlistToCart() {
        const ids = getWishlist();
        if (!ids.length) {
            showToast('Your wishlist is empty');
            return;
        }
        ids.forEach((id) => {
            const product = findProduct(id);
            if (product) addToCart(...wishlistProductPayload(product), true);
        });
        saveWishlist([]);
        renderWishlist();
        showToast('Wishlist items moved to cart');
        closeWishlist();
        openCart();
    }

    shop.addEventListener('click', (e) => {
        const heart = e.target.closest('[data-wishlist]');
        if (heart) {
            e.preventDefault();
            e.stopPropagation();
            const id = parseInt(heart.dataset.wishlist, 10);
            if (!Number.isNaN(id)) toggleWishlist(id);
            return;
        }
        if (e.target.closest('#wishlist-toggle')) {
            e.preventDefault();
            openWishlist();
        }
    });
    wishlistClose?.addEventListener('click', () => closeWishlist());
    wishlistOverlay?.addEventListener('click', () => closeWishlist());
    btnClearWishlist?.addEventListener('click', () => {
        saveWishlist([]);
        renderWishlist();
        showToast('Wishlist cleared');
    });
    btnWishlistToCart?.addEventListener('click', moveAllWishlistToCart);
    wishlistItems?.addEventListener('click', (e) => {
        const toCart = e.target.closest('[data-wish-to-cart]');
        const remove = e.target.closest('[data-wish-remove]');
        if (toCart) moveWishlistItemToCart(parseInt(toCart.dataset.id, 10));
        else if (remove) removeFromWishlist(parseInt(remove.dataset.id, 10));
    });
    renderWishlist();

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
    document.getElementById('contact-open-footer')?.addEventListener('click', openContact);
    contactClose?.addEventListener('click', closeContact);
    contactOverlay?.addEventListener('click', closeContact);

    contactForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        contactError.hidden = true;
        contactSuccess.hidden = true;

        const formData = new FormData(contactForm);

        try {
            const res = await fetch(contactForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
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
