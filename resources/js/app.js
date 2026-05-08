import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

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

Alpine.data('catalogFilters', ({ initialSearch = '', activeCategory = null } = {}) => ({
    mobileFiltersOpen: false,
    draftSearch: initialSearch,
    draftCategory: activeCategory,

    init() {
        this.$watch('mobileFiltersOpen', (open) => {
            document.body.classList.toggle('overflow-hidden', open);
        });
    },

    openMobileFilters() {
        this.mobileFiltersOpen = true;
    },

    closeMobileFilters() {
        this.mobileFiltersOpen = false;
    },

    setDraftCategory(value) {
        this.draftCategory = value || null;
    },

    clearDraftFilters() {
        this.draftSearch = '';
        this.draftCategory = null;
    },
}));

Alpine.store('search', { open: false });

Alpine.data('scrollTopControl', () => {
    let scrollHandler = null;

    return {
        visible: false,
        isScrolling: false,

        init() {
            scrollHandler = () => {
                const nextVisible = window.scrollY > 420;

                if (nextVisible !== this.visible) {
                    this.visible = nextVisible;
                }
            };

            scrollHandler();
            window.addEventListener('scroll', scrollHandler, { passive: true });
        },

        destroy() {
            if (scrollHandler) {
                window.removeEventListener('scroll', scrollHandler);
            }
        },

        scrollToTop() {
            if (this.isScrolling) return;

            this.isScrolling = true;

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            window.scrollTo({
                top: 0,
                behavior: reduceMotion ? 'auto' : 'smooth',
            });

            window.setTimeout(() => {
                this.isScrolling = false;
            }, reduceMotion ? 0 : 700);
        },
    };
});

Alpine.data('statCounter', ({ target = 0, suffix = '', duration = 1200 } = {}) => {
    let observer = null;

    return {
        current: 0,
        hasAnimated: false,

        init() {
            observer = new IntersectionObserver(
                ([entry]) => {
                    if (!entry?.isIntersecting || this.hasAnimated) {
                        return;
                    }

                    this.hasAnimated = true;
                    this.animate();
                    observer?.disconnect();
                },
                { threshold: 0.45 }
            );

            observer.observe(this.$el);
        },

        destroy() {
            observer?.disconnect();
        },

        animate() {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (reduceMotion) {
                this.current = target;
                return;
            }

            const startedAt = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - startedAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);

                this.current = Math.round(target * eased);

                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                }
            };

            window.requestAnimationFrame(tick);
        },

        formatted() {
            return `${this.current}${suffix}`;
        },
    };
});

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

Livewire.start();
