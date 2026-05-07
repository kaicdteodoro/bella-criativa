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
<div x-data="catalogNav()">

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

    {{-- ─── STICKY NAV ────────────────────────────────────────────────────── --}}
    <div x-ref="navTrigger" class="h-px"></div>
    <div class="sticky top-0 z-20 border-b border-[var(--color-border)] bg-[var(--color-bg)]/90 backdrop-blur-md">
        <div
            :class="navVisible ? 'opacity-100' : 'opacity-0 pointer-events-none'"
            class="flex items-center justify-between px-6 py-3 transition duration-300"
        >
            <span class="pb-eyebrow-dense">Catálogo Bella Criativa</span>
            <span class="hidden text-xs text-[var(--color-text-secondary)] sm:block">Ver catálogo completo</span>
        </div>
    </div>

    {{-- ─── FILTROS ────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('products.index') }}">
        <div class="border-b border-[var(--color-border)] py-5">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input
                        type="text"
                        name="busca"
                        value="{{ $search }}"
                        placeholder="Buscar por nome ou SKU…"
                        class="pb-focus-ring w-full border border-[var(--color-border)] bg-transparent px-4 py-3 text-sm placeholder:text-[var(--color-text-secondary)]"
                    >
                </div>
                <button type="submit" class="pb-focus-ring border border-[var(--color-accent)] bg-[var(--color-accent)] px-5 py-3 text-xs uppercase tracking-[0.16em] text-white">
                    Buscar
                </button>
                @if($search || $activeCategory)
                <a href="{{ route('products.index') }}" class="pb-focus-ring border border-[var(--color-border)] px-5 py-3 text-xs uppercase tracking-[0.16em] text-[var(--color-text-secondary)] transition hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]">
                    Limpar
                </a>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2 py-4 border-b border-[var(--color-border)]">
            <a
                href="{{ route('products.index', array_filter(['busca' => $search ?: null])) }}"
                class="pb-focus-ring border px-4 py-2 text-xs uppercase tracking-[0.14em] transition {{ !$activeCategory ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-white' : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-text-primary)] hover:text-[var(--color-text-primary)]' }}"
            >
                Todos
            </a>
            @foreach ($categories as $item)
                @php($isActive = $activeCategory === $item->slug)
                <a
                    href="{{ route('products.index', array_filter(['categoria' => $isActive ? null : $item->slug, 'busca' => $search ?: null])) }}"
                    class="pb-focus-ring border px-4 py-2 text-xs uppercase tracking-[0.14em] transition {{ $isActive ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-white' : 'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-text-primary)] hover:text-[var(--color-text-primary)]' }}"
                >
                    {{ $item->filterDisplayName() }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- ─── GRID ──────────────────────────────────────────────────────────── --}}
    <div class="py-8">
        <livewire:catalog-grid :category="$activeCategory" :search="$search" />
    </div>

</div>

@endsection
