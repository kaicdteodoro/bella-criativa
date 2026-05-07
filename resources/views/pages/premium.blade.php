@extends('layouts.app')

@section('content')

{{-- ─── HERO DARK ───────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed -mt-10 bg-[var(--color-text-primary)] px-8 pb-14 pt-14 lg:px-16 lg:pb-16 lg:pt-20">
    <p class="mb-8 text-xs uppercase tracking-[0.24em] text-white/50">Linha Premium</p>
    <h1 class="max-w-2xl text-5xl leading-[1.03] text-white lg:text-7xl">
        Para quando o acabamento<br>é o detalhe que importa.
    </h1>
    <div class="mt-10 grid max-w-3xl gap-8 border-t border-white/10 pt-8 lg:grid-cols-2">
        <p class="text-lg leading-8 text-white/60">
            Produtos com materiais superiores e personalização de alta precisão.
            Para presentes executivos e brindes que precisam falar pela marca.
        </p>
        <div class="border-l-4 border-[var(--color-accent)] pl-6">
            <p class="text-base leading-relaxed italic text-white/70">
                "Cada item da linha Premium é escolhido pelo nível de acabamento final — não pelo preço."
            </p>
        </div>
    </div>
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
            <p class="text-[var(--color-text-secondary)]">Produtos premium chegando em breve.</p>
        </div>
    @endif
</section>

{{-- ─── CTA ────────────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed bg-[var(--color-accent)] px-8 py-14 lg:px-16 lg:py-18">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Vamos começar</p>
            <h2 class="max-w-md text-3xl leading-tight text-white">Tem um projeto premium em mente?</h2>
            <p class="max-w-sm text-white/70">Fala com a Bella — a gente indica o produto certo para o seu momento.</p>
        </div>
        <a
            href="https://wa.me/5516994492382"
            target="_blank"
            rel="noopener noreferrer"
            class="pb-focus-ring inline-flex shrink-0 items-center gap-3 border border-white/30 bg-white px-7 py-4 text-sm uppercase tracking-[0.2em] text-[var(--color-accent)] transition hover:bg-white/90"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Falar no WhatsApp
        </a>
    </div>
</section>

@endsection
