@extends('layouts.app')

@section('content')

{{-- ─── HERO ──────────────────────────────────────────────────────────────── --}}
<section class="border-b border-[var(--color-border)] pb-12 pt-10 lg:pt-16">
    <div class="flex items-start justify-between">
        <div>
            <p class="pb-eyebrow mb-5">Novidades</p>
            <h1 class="max-w-2xl text-5xl leading-[1.05] lg:text-7xl">O que chegou<br>novo no catálogo.</h1>
        </div>
        <div class="hidden shrink-0 text-right lg:block">
            <p class="text-xs uppercase tracking-[0.24em] text-[var(--color-text-secondary)]">Edição</p>
            <p class="mt-2 font-semibold tabular-nums text-[var(--color-accent)] text-4xl xl:text-5xl">{{ now()->format('m/Y') }}</p>
        </div>
    </div>
    <p class="mt-6 max-w-xl text-lg leading-8 text-[var(--color-text-secondary)]">
        Produtos recém-chegados — para quem gosta de ser o primeiro a usar.
    </p>
</section>

{{-- ─── GRID ──────────────────────────────────────────────────────────────── --}}
<section class="py-12">
    @if($products->isNotEmpty())
        <div class="grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <div class="bg-[var(--color-bg)]">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    @else
        <div class="border border-[var(--color-border)] px-8 py-16 text-center">
            <p class="pb-eyebrow mb-3">Em breve</p>
            <p class="text-[var(--color-text-secondary)]">Novidades chegando em breve ao catálogo.</p>
        </div>
    @endif
</section>

{{-- ─── CTA ────────────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed bg-[var(--color-accent)] px-8 py-14 lg:px-16 lg:py-18">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Ver tudo</p>
            <h2 class="max-w-md text-3xl leading-tight text-white">Quer explorar o catálogo completo?</h2>
        </div>
        <a href="{{ route('products.index') }}" class="pb-focus-ring inline-flex shrink-0 border border-white/30 bg-white px-7 py-4 text-sm uppercase tracking-[0.2em] text-[var(--color-accent)] transition hover:bg-white/90">
            Ver catálogo
        </a>
    </div>
</section>

@endsection
