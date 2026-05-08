@extends('layouts.app')

@section('content')
@php
    $catalogCollectionLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $title ?? 'Catálogo Bella Criativa',
        'description' => $description ?? null,
        'url' => url()->current().(request()->query() ? '?'.http_build_query(request()->query()) : ''),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Bella Criativa',
            'url' => route('home'),
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('products.index').'?busca={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($catalogCollectionLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<div
    x-data="catalogFilters({
        initialSearch: @js($search),
        activeCategory: @js($activeCategory),
    })"
>

    {{-- ─── HERO ──────────────────────────────────────────────────────────── --}}
    <section x-ref="hero" class="border-b border-[var(--color-border)] py-10 lg:py-14">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pb-eyebrow mb-4">Catálogo completo</p>
                <h1 class="text-5xl leading-[1.0] lg:text-7xl">Produtos que levam<br>sua marca junto.</h1>
            </div>
            <p class="max-w-xs text-sm leading-7 text-[var(--color-text-secondary)] lg:text-right">
                Brindes, kits e personalizados para empresas que se importam com o acabamento.
            </p>
        </div>
    </section>

    {{-- ─── TOOLBAR STICKY ─────────────────────────────────────────────────── --}}
    <section class="pb-catalog-toolbar">
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between gap-3 lg:hidden">
                <div class="min-w-0">
                    <p class="pb-eyebrow-dense">Filtrar catálogo</p>
                    <p class="mt-1 truncate text-sm text-[var(--color-text-secondary)]">
                        @if ($activeCategory)
                            {{ $categories->firstWhere('slug', $activeCategory)?->filterDisplayName() ?? 'Categoria selecionada' }}
                        @elseif ($search !== '')
                            Busca ativa: {{ $search }}
                        @else
                            Busca e categorias sempre à mão
                        @endif
                    </p>
                </div>
                <button
                    type="button"
                    x-on:click="openMobileFilters()"
                    class="pb-focus-ring inline-flex shrink-0 items-center gap-2 border border-[var(--color-border)] px-4 py-3 text-xs uppercase tracking-[0.16em] text-[var(--color-text-primary)] transition hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 5h14M5.5 10h9M8 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                    Filtros
                </button>
            </div>

            <form method="GET" action="{{ route('products.index') }}" class="hidden lg:block">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,20rem)_auto] lg:items-center">
                    <div class="flex items-center gap-3">
                        <div class="relative min-w-0 flex-1">
                            <input
                                type="text"
                                name="busca"
                                value="{{ $search }}"
                                placeholder="Buscar por nome, SKU ou linha…"
                                class="pb-focus-ring w-full border border-[var(--color-border)] bg-transparent px-4 py-3 text-sm placeholder:text-[var(--color-text-secondary)]"
                            >
                        </div>
                        <button type="submit" class="pb-btn-primary pb-focus-ring inline-flex shrink-0 px-5 py-3 text-xs uppercase tracking-[0.16em]">
                            Buscar
                        </button>
                        @if($search || $activeCategory)
                            <a href="{{ route('products.index') }}" class="pb-focus-ring inline-flex shrink-0 border border-[var(--color-border)] px-5 py-3 text-xs uppercase tracking-[0.16em] text-[var(--color-text-secondary)] transition hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]">
                                Limpar
                            </a>
                        @endif
                    </div>

                    <div class="pb-chip-scroll flex items-center gap-2 overflow-x-auto pb-1">
                        <a
                            href="{{ route('products.index', array_filter(['busca' => $search ?: null])) }}"
                            class="pb-focus-ring whitespace-nowrap border px-4 py-2 text-xs uppercase tracking-[0.14em] transition {{ !$activeCategory ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-white' : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-text-primary)] hover:text-[var(--color-text-primary)]' }}"
                        >
                            Todos
                        </a>
                        @foreach ($categories as $item)
                            @php($isActive = $activeCategory === $item->slug)
                            <a
                                href="{{ route('products.index', array_filter(['categoria' => $isActive ? null : $item->slug, 'busca' => $search ?: null])) }}"
                                class="pb-focus-ring whitespace-nowrap border px-4 py-2 text-xs uppercase tracking-[0.14em] transition {{ $isActive ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-white' : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-text-primary)] hover:text-[var(--color-text-primary)]' }}"
                            >
                                {{ $item->filterDisplayName() }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- ─── MOBILE FILTERS ─────────────────────────────────────────────────── --}}
    <div
        x-cloak
        x-show="mobileFiltersOpen"
        x-transition.opacity
        class="fixed inset-0 z-[70] bg-black/30 lg:hidden"
        style="display: none;"
        x-on:click="closeMobileFilters()"
        aria-hidden="true"
    ></div>

    <div
        x-cloak
        x-show="mobileFiltersOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-4 opacity-0"
        class="fixed inset-x-0 bottom-0 z-[75] max-h-[82dvh] overflow-hidden rounded-t-[1.5rem] border border-[var(--color-border)] bg-[var(--color-bg)] shadow-[0_-24px_60px_rgba(0,0,0,0.14)] lg:hidden"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-label="Filtros do catálogo"
    >
        <form method="GET" action="{{ route('products.index') }}" class="flex h-full flex-col">
            <div class="flex items-center justify-between border-b border-[var(--color-border)] px-5 py-4">
                <div>
                    <p class="pb-eyebrow-dense">Filtros</p>
                    <h2 class="mt-1 text-lg">Refinar catálogo</h2>
                </div>
                <button
                    type="button"
                    x-on:click="closeMobileFilters()"
                    class="pb-focus-ring inline-flex h-10 w-10 items-center justify-center border border-[var(--color-border)] text-[var(--color-text-primary)]"
                    aria-label="Fechar filtros"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto px-5 py-5">
                <div class="space-y-3">
                    <label for="mobile-catalog-search" class="pb-eyebrow-dense block">Busca</label>
                    <input
                        id="mobile-catalog-search"
                        type="text"
                        name="busca"
                        x-model="draftSearch"
                        placeholder="Buscar por nome, SKU ou linha…"
                        class="pb-focus-ring w-full border border-[var(--color-border)] bg-transparent px-4 py-3 text-sm placeholder:text-[var(--color-text-secondary)]"
                    >
                </div>

                <div class="space-y-3">
                    <p class="pb-eyebrow-dense">Categorias</p>
                    <div class="grid gap-2">
                        <label class="flex items-center justify-between border border-[var(--color-border)] px-4 py-3 text-sm">
                            <span>Todos</span>
                            <input
                                type="radio"
                                name="categoria"
                                value=""
                                x-bind:checked="draftCategory === null || draftCategory === ''"
                                x-on:change="setDraftCategory('')"
                                class="h-4 w-4 border-[var(--color-border)] text-[var(--color-accent)] focus:ring-[var(--color-accent)]"
                            >
                        </label>
                        @foreach ($categories as $item)
                            <label class="flex items-center justify-between border border-[var(--color-border)] px-4 py-3 text-sm">
                                <span>{{ $item->filterDisplayName() }}</span>
                                <input
                                    type="radio"
                                    name="categoria"
                                    value="{{ $item->slug }}"
                                    x-bind:checked="draftCategory === '{{ $item->slug }}'"
                                    x-on:change="setDraftCategory('{{ $item->slug }}')"
                                    class="h-4 w-4 border-[var(--color-border)] text-[var(--color-accent)] focus:ring-[var(--color-accent)]"
                                >
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 border-t border-[var(--color-border)] px-5 py-4">
                <button
                    type="button"
                    x-on:click="clearDraftFilters()"
                    class="pb-focus-ring border border-[var(--color-border)] px-4 py-3 text-xs uppercase tracking-[0.16em] text-[var(--color-text-secondary)] transition hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]"
                >
                    Limpar
                </button>
                <button type="submit" class="pb-btn-primary pb-focus-ring px-4 py-3 text-xs uppercase tracking-[0.16em]">
                    Aplicar
                </button>
            </div>
        </form>
    </div>

    {{-- ─── GRID ──────────────────────────────────────────────────────────── --}}
    <div class="py-8">
        <livewire:catalog-grid :category="$activeCategory" :search="$search" />
    </div>

</div>

@endsection
