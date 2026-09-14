(() => {
    const overlay = document.getElementById('login-overlay');
    const modal = document.getElementById('login-modal');
    const openBtn = document.getElementById('login-open');
    const closeBtn = document.getElementById('login-close');

    if (!overlay || !modal) return;

    function openLogin() {
        overlay.hidden = false;
        modal.hidden = false;
        requestAnimationFrame(() => {
            overlay.classList.add('is-open');
            modal.classList.add('is-open');
        });
        document.body.style.overflow = 'hidden';
        modal.querySelector('#login-email')?.focus();
    }

    function closeLogin() {
        overlay.classList.remove('is-open');
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(() => {
            overlay.hidden = true;
            modal.hidden = true;
        }, 220);
    }

    openBtn?.addEventListener('click', openLogin);
    closeBtn?.addEventListener('click', closeLogin);
    overlay.addEventListener('click', closeLogin);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeLogin();
        }
    });

    if (modal.dataset.open === '1') {
        openLogin();
    }
})();
