import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('catalogNav', () => {
    let observer = null;
    let topHoverZone = null;

    return {
        navVisible: false,
        isPinned: false,
        isHoveringTop: false,

        init() {
            observer = new IntersectionObserver(
                ([entry]) => {
                    this.isPinned = !entry.isIntersecting;
                    this._syncNav();
                },
                {
                    threshold: 0,
                    rootMargin: '-1px 0px 0px 0px',
                }
            );

            observer.observe(this.$refs.navTrigger);

            topHoverZone = (event) => {
                this.isHoveringTop = event.clientY <= 96;
                this._syncNav();
            };

            window.addEventListener('mousemove', topHoverZone, { passive: true });
        },

        destroy() {
            observer?.disconnect();

            if (topHoverZone) {
                window.removeEventListener('mousemove', topHoverZone);
            }
        },

        _syncNav() {
            this.navVisible = this.isPinned || this.isHoveringTop;
        },
    };
});

Alpine.store('search', { open: false });

function saveCatalogReturnState() {
    try {
        sessionStorage.setItem('catalog:return-state', JSON.stringify({
            url: window.location.pathname + window.location.search,
            y: window.scrollY,
        }));
    } catch (_) {
        // ignore storage errors
    }
}

function markCatalogRestoreOnce() {
    try {
        sessionStorage.setItem('catalog:restore-once', '1');
    } catch (_) {
        // ignore storage errors
    }
}

function hydrateCatalogBackLink() {
    try {
        const raw = sessionStorage.getItem('catalog:return-state');
        if (!raw) return;
        const state = JSON.parse(raw);
        if (!state || typeof state.url !== 'string') return;
        const link = document.getElementById('catalog-back-link');
        if (link) link.setAttribute('href', state.url);
    } catch (_) {
        // ignore storage errors
    }
}

function restoreCatalogScrollIfNeeded() {
    try {
        const shouldRestore = sessionStorage.getItem('catalog:restore-once') === '1';
        if (!shouldRestore) return;
        sessionStorage.removeItem('catalog:restore-once');
        const raw = sessionStorage.getItem('catalog:return-state');
        if (!raw) return;
        const state = JSON.parse(raw);
        if (!state || typeof state.url !== 'string' || typeof state.y !== 'number') return;
        const current = window.location.pathname + window.location.search;
        if (state.url !== current) return;
        window.requestAnimationFrame(() => window.scrollTo({ top: state.y, behavior: 'auto' }));
    } catch (_) {
        // ignore storage errors
    }
}

document.addEventListener('click', (event) => {
    const saveTrigger = event.target.closest('[data-catalog-save-return-state="1"]');
    if (saveTrigger) {
        saveCatalogReturnState();
    }

    const restoreTrigger = event.target.closest('[data-catalog-restore-once="1"]');
    if (restoreTrigger) {
        markCatalogRestoreOnce();
    }
});

hydrateCatalogBackLink();
restoreCatalogScrollIfNeeded();

Alpine.start();
