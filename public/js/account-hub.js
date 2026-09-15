(function () {
    'use strict';

    const overlay = document.getElementById('account-overlay');
    const hub = document.getElementById('account-hub');
    const track = document.getElementById('account-hub-track');
    const closeBtn = document.getElementById('account-hub-close');
    if (!hub || !track || !overlay) return;

    const shop = document.getElementById('shop-app');
    const panes = Array.from(track.querySelectorAll('[data-pane]'));
    const tabs = Array.from(hub.querySelectorAll('.account-hub__tab'));
    const routes = {
        login: hub.dataset.routeLogin,
        register: hub.dataset.routeRegister,
        forgot: hub.dataset.routeForgot,
        profile: hub.dataset.routeProfile,
        password: hub.dataset.routePassword,
        addresses: hub.dataset.routeAddresses,
        verify: hub.dataset.routeVerify,
    };
    const pageKind = shop?.dataset.page || '';
    let lastShopUrl = window.location.pathname + window.location.search + window.location.hash;

    function paneIndex(name) {
        return Math.max(0, panes.findIndex((pane) => pane.dataset.pane === name));
    }

    function panelFromPath(path, hash) {
        const url = String(path || '');
        if (url.indexOf('/register') !== -1) return 'register';
        if (url.indexOf('/forgot-password') !== -1) return 'forgot';
        if (url.indexOf('/addresses') !== -1) return 'addresses';
        if (url.indexOf('/email/verify') !== -1) return 'verify';
        if (url.indexOf('/login') !== -1) return 'login';
        if (url.indexOf('/account') !== -1) return hash === '#password' ? 'password' : 'profile';
        return null;
    }

    function setPanel(name, push) {
        if (!panes.some((pane) => pane.dataset.pane === name)) return;
        hub.dataset.activePanel = name;
        track.style.transform = 'translateX(-' + (paneIndex(name) * 100) + '%)';
        tabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.accountPanel === name));
        if (push && routes[name]) {
            history.pushState({ accountPanel: name }, '', routes[name]);
        }
    }

    function openHub(name, push) {
        if (!hub.classList.contains('is-open') && !panelFromPath(window.location.pathname, window.location.hash)) {
            lastShopUrl = window.location.pathname + window.location.search + window.location.hash;
        }
        const panel = name || hub.dataset.activePanel || (panes[0] && panes[0].dataset.pane);
        overlay.hidden = false;
        hub.hidden = false;
        requestAnimationFrame(() => {
            overlay.classList.add('is-open');
            hub.classList.add('is-open');
            setPanel(panel, push);
        });
        document.body.style.overflow = 'hidden';
    }

    function closeHub(restore) {
        overlay.classList.remove('is-open');
        hub.classList.remove('is-open');
        setTimeout(() => {
            overlay.hidden = true;
            hub.hidden = true;
        }, 280);
        if (!document.getElementById('cart-drawer')?.classList.contains('is-open')) {
            document.body.style.overflow = '';
        }
        if (!restore) return;
        if (pageKind === 'checkout' || pageKind === 'orders') return;
        if (pageKind === 'account') {
            window.location.href = hub.dataset.routeHome || '/';
            return;
        }
        const stay = lastShopUrl && !panelFromPath(lastShopUrl, '');
        history.pushState({ accountPanel: null }, '', stay ? lastShopUrl : (hub.dataset.routeHome || '/'));
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('.js-account-link');
        if (!link) return;
        const panel = link.dataset.accountPanel;
        if (!panel || !panes.some((pane) => pane.dataset.pane === panel)) return;
        event.preventDefault();
        openHub(panel, true);
    });

    closeBtn?.addEventListener('click', () => closeHub(true));
    overlay.addEventListener('click', () => closeHub(true));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && hub.classList.contains('is-open')) closeHub(true);
    });

    let touchStartX = 0;
    track.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0].screenX;
    }, { passive: true });
    track.addEventListener('touchend', (event) => {
        const dx = event.changedTouches[0].screenX - touchStartX;
        if (Math.abs(dx) < 48) return;
        const current = paneIndex(hub.dataset.activePanel);
        const next = dx < 0 ? Math.min(panes.length - 1, current + 1) : Math.max(0, current - 1);
        const pane = panes[next];
        if (pane) openHub(pane.dataset.pane, true);
    }, { passive: true });

    window.addEventListener('popstate', () => {
        const panel = (history.state && history.state.accountPanel) || panelFromPath(window.location.pathname, window.location.hash);
        if (panel && panes.some((pane) => pane.dataset.pane === panel)) openHub(panel, false);
        else if (pageKind !== 'account' && pageKind !== 'checkout') closeHub(false);
    });

    const pathPanel = panelFromPath(window.location.pathname, window.location.hash);
    const initial = shop?.dataset.accountPanel || pathPanel;
    const shouldOpen = shop?.dataset.accountOpen === '1' || Boolean(pathPanel);
    if (initial) setPanel(initial, false);
    if (shouldOpen) openHub(initial, false);
})();
