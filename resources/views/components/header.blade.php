@php
    $waDigits = preg_replace('/\D/', '', (string) config('catalog.whatsapp_number', ''));
    $waHref = $waDigits !== '' ? 'https://wa.me/'.$waDigits : null;

    $navItems = [
        [
            'route' => 'products.index',
            'active' => ['products.*', 'categories.*'],
            'label' => 'Catálogo',
        ],
        [
            'route' => 'launches',
            'active' => ['launches'],
            'label' => 'Lançamentos',
        ],
        [
            'route' => 'premium',
            'active' => ['premium'],
            'label' => 'Linha premium',
        ],
        [
            'route' => 'about',
            'active' => ['about'],
            'label' => 'Sobre',
        ],
        [
            'route' => 'contact',
            'active' => ['contact'],
            'label' => 'Contato',
        ],
    ];
@endphp

<header
    x-data="{
        mobileNavOpen: false,
        init() {
            this.$watch('mobileNavOpen', (open) => {
                document.body.classList.toggle('overflow-hidden', open);
            });
        },
    }"
    @keydown.escape.window="mobileNavOpen = false"
    class="relative sticky top-0 z-50"
>
    {{-- Barra superior sempre acima do backdrop do menu mobile --}}
    <div class="pb-nav-glass relative z-[60] border-b">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:gap-6 lg:py-5">
            <div class="flex min-w-0 flex-1 items-center gap-2 lg:flex-none lg:gap-4">
                <button
                    type="button"
                    class="pb-focus-ring -ml-1 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-sm text-[var(--color-text-primary)] lg:hidden"
                    aria-controls="site-mobile-nav"
                    :aria-expanded="mobileNavOpen"
                    @click="mobileNavOpen = ! mobileNavOpen"
                >
                    <span class="sr-only" x-text="mobileNavOpen ? 'Fechar menu' : 'Abrir menu'"></span>
                    <svg x-show="! mobileNavOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg x-cloak x-show="mobileNavOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <a href="{{ route('home') }}" class="pb-focus-ring shrink-0" aria-label="Bella Criativa — início">
                    <img
                        src="/images/logo-rosa.png"
                        alt="Bella — design e personalização"
                        class="h-14 w-auto sm:h-16"
                    >
                </a>
            </div>

            <nav class="hidden items-center gap-6 lg:flex xl:gap-8" aria-label="Principal">
                @foreach ($navItems as $item)
                    @php $isActive = request()->routeIs(...$item['active']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'pb-focus-ring whitespace-nowrap text-xs uppercase tracking-[0.2em] transition-colors duration-200',
                            'text-[var(--color-text-primary)]' => $isActive,
                            'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]' => ! $isActive,
                        ])
                        @if ($isActive) aria-current="page" @endif
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                @if ($waHref)
                    <a
                        href="{{ $waHref }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="pb-btn-outline pb-focus-ring hidden px-3 py-2 text-xs uppercase tracking-[0.16em] sm:inline-flex sm:px-4 sm:tracking-[0.2em]"
                    >
                        WhatsApp
                    </a>
                    <a
                        href="{{ $waHref }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="pb-focus-ring inline-flex h-11 w-11 items-center justify-center rounded-sm border border-[var(--color-accent)] text-[var(--color-accent)] sm:hidden"
                        aria-label="WhatsApp"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Overlay: fecha ao tocar fora --}}
    <div
        x-cloak
        x-show="mobileNavOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-black/25 lg:hidden"
        style="display: none;"
        @click="mobileNavOpen = false"
        aria-hidden="true"
    ></div>

    {{-- Painel do menu mobile --}}
    <div
        id="site-mobile-nav"
        x-cloak
        x-show="mobileNavOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-2 opacity-0"
        class="absolute inset-x-0 top-full z-50 border-b border-[var(--color-border)] bg-[var(--color-bg)] shadow-[0_24px_48px_rgba(0,0,0,0.06)] lg:hidden"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-label="Menu do site"
    >
        <nav class="flex max-h-[min(70vh,calc(100dvh-5rem))] flex-col overflow-y-auto px-4 py-2 pb-6" aria-label="Principal">
            @foreach ($navItems as $item)
                @php $isActive = request()->routeIs(...$item['active']); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @click="mobileNavOpen = false"
                    @class([
                        'pb-focus-ring border-b border-[var(--color-border)] py-4 text-sm uppercase tracking-[0.2em] transition-colors last:border-b-0',
                        'text-[var(--color-text-primary)]' => $isActive,
                        'text-[var(--color-text-secondary)]' => ! $isActive,
                    ])
                    @if ($isActive) aria-current="page" @endif
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
