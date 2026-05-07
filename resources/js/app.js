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

Alpine.start();
