@extends('layouts.app')

@section('content')

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
