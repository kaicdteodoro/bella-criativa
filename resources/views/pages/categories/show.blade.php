@extends('layouts.app')

@section('content')
@php
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Catálogo', 'item' => route('products.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $category->filterDisplayName(), 'item' => route('categories.show', $category->slug)],
        ],
    ];

    $collectionLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $category->filterDisplayName().' | Bella Criativa',
        'description' => $description ?? null,
        'url' => route('categories.show', $category->slug),
    ];

    $itemListLd = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Produtos da categoria '.$category->filterDisplayName(),
        'itemListElement' => $productsForSeo
            ->values()
            ->map(fn ($product, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => route('products.show', $product->slug),
                'name' => $product->title,
            ])
            ->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($collectionLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@if (!empty($itemListLd['itemListElement']))
    <script type="application/ld+json">{!! json_encode($itemListLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif

{{-- ─── HERO ──────────────────────────────────────────────────────────────── --}}
<section class="border-b border-[var(--color-border)] pb-10 pt-10 lg:pt-16">
    <p class="pb-eyebrow mb-4">Categoria</p>
    <h1 class="max-w-3xl text-5xl leading-[1.05] lg:text-7xl">{{ $category->filterDisplayName() }}</h1>
    @if ($category->description)
        <p class="mt-5 max-w-xl text-lg leading-8 text-[var(--color-text-secondary)]">{{ $category->description }}</p>
    @endif
</section>

{{-- ─── FILTROS ────────────────────────────────────────────────────────────── --}}
<div class="border-b border-[var(--color-border)] py-4">
    <livewire:catalog-filters :category="$category->slug" />
</div>

{{-- ─── GRID ──────────────────────────────────────────────────────────────── --}}
<div class="py-10">
    <livewire:catalog-grid :category="$category->slug" />
</div>

@endsection
