@extends('layouts.app')

@section('content')
@php
    $galleryItems = $product->media
        ->map(fn ($media) => [
            'url'       => \Illuminate\Support\Facades\Storage::url($media->file),
            'thumb'     => $media->thumb_url,
            'label'     => $product->title,
            'color_hex' => is_string($media->color_hex) ? strtoupper($media->color_hex) : null,
        ])
        ->values();

    $initialImage  = $product->featured_image_url ?: ($galleryItems->first()['url'] ?? null);
    $colorImageMap = $galleryItems
        ->filter(fn (array $item) => filled($item['color_hex']))
        ->mapWithKeys(fn (array $item) => [$item['color_hex'] => $item['url']])
        ->all();

    $productCategories = $product->categories->map(fn ($c) => $c->filterDisplayName())->unique();
@endphp

{{-- ─── BREADCRUMB ─────────────────────────────────────────────────────────── --}}
<div class="border-b border-[var(--color-border)] py-4">
    <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)]">
        <a
            id="catalog-back-link"
            href="{{ route('products.index') }}"
            onclick="try{sessionStorage.setItem('catalog:restore-once', '1');}catch(e){}"
            class="pb-focus-ring transition hover:text-[var(--color-text-primary)]"
        >
            Catálogo
        </a>
        @if ($productCategories->isNotEmpty())
            <span aria-hidden="true">›</span>
            <span>{{ $productCategories->first() }}</span>
        @endif
        <span aria-hidden="true">›</span>
        <span class="truncate max-w-xs text-[var(--color-text-primary)]">{{ $product->title }}</span>
    </div>
</div>

{{-- ─── PRODUTO ─────────────────────────────────────────────────────────────── --}}
<article
    x-data="{
        selectedImage: @js($initialImage),
        images: @js($galleryItems->pluck('url')->values()->all()),
        colorImageMap: @js($colorImageMap),
        lightboxOpen: false,
        lightboxIndex: 0,
        zoom: 1,
        panX: 0,
        panY: 0,
        dragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOriginX: 0,
        dragOriginY: 0,
        openLightbox() {
            this.lightboxIndex = Math.max(this.images.indexOf(this.selectedImage), 0);
            this.zoom = 1; this.panX = 0; this.panY = 0;
            this.lightboxOpen = true;
        },
        closeLightbox() {
            this.lightboxOpen = false;
            this.zoom = 1; this.panX = 0; this.panY = 0;
        },
        currentImage() { return this.images[this.lightboxIndex] ?? this.selectedImage; },
        nextImage() {
            if (!this.images.length) return;
            this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length;
            this.selectedImage = this.currentImage();
            this.zoom = 1; this.panX = 0; this.panY = 0;
        },
        prevImage() {
            if (!this.images.length) return;
            this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length;
            this.selectedImage = this.currentImage();
            this.zoom = 1; this.panX = 0; this.panY = 0;
        },
        zoomIn()  { this.zoom = Math.min(this.zoom + 0.25, 3); },
        zoomOut() {
            this.zoom = Math.max(this.zoom - 0.25, 1);
            if (this.zoom === 1) { this.panX = 0; this.panY = 0; }
        },
        toggleZoom(event) {
            if (this.zoom > 1) { this.zoom = 1; this.panX = 0; this.panY = 0; return; }
            this.zoom = 2; this.applyZoomFromPointer(event);
        },
        applyZoomFromPointer(event) {
            if (this.zoom <= 1) return;
            const rect = event.currentTarget.getBoundingClientRect();
            const relX = ((event.clientX - rect.left) / rect.width)  - 0.5;
            const relY = ((event.clientY - rect.top)  / rect.height) - 0.5;
            this.panX = relX * -80; this.panY = relY * -80;
        },
        wheelZoom(event) {
            event.preventDefault();
            const delta = event.deltaY > 0 ? -0.2 : 0.2;
            this.zoom = Number(Math.min(3, Math.max(1, this.zoom + delta)).toFixed(2));
            this.applyZoomFromPointer(event);
            if (this.zoom === 1) { this.panX = 0; this.panY = 0; }
        },
        startDrag(event) {
            if (this.zoom <= 1) return;
            this.dragging = true;
            this.dragStartX = event.clientX; this.dragStartY = event.clientY;
            this.dragOriginX = this.panX;    this.dragOriginY = this.panY;
        },
        onDrag(event) {
            if (!this.dragging) return;
            this.panX = Math.max(-120, Math.min(120, this.dragOriginX + (event.clientX - this.dragStartX) / 3));
            this.panY = Math.max(-120, Math.min(120, this.dragOriginY + (event.clientY - this.dragStartY) / 3));
        },
        endDrag() { this.dragging = false; },
        selectColor(colorHex) {
            const target = this.colorImageMap[colorHex];
            if (!target) return;
            this.selectedImage = target;
            this.lightboxIndex = Math.max(this.images.indexOf(target), 0);
        }
    }"
    @keydown.escape.window="closeLightbox()"
    class="mt-10 grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] lg:grid-cols-[1fr_1fr]"
>

    {{-- ─── COLUNA IMAGEM ──────────────────────────────────────────────────── --}}
    <div class="bg-[var(--color-bg-soft)]">

        {{-- Imagem principal --}}
        <div class="aspect-[4/5] overflow-hidden">
            @if ($initialImage)
                <button type="button" x-on:click="openLightbox()" class="group h-full w-full">
                    <img
                        x-bind:src="selectedImage"
                        alt="{{ $product->title }}"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                        loading="eager"
                    >
                </button>
            @else
                <div class="flex h-full items-end p-8">
                    <span class="text-xs uppercase tracking-[0.22em] text-[var(--color-text-secondary)]">{{ $product->sku }}</span>
                </div>
            @endif
        </div>

        {{-- Thumbnails --}}
        @if ($galleryItems->count() > 1)
            <div class="flex gap-px border-t border-[var(--color-border)] bg-[var(--color-border)]">
                @foreach ($galleryItems->take(6) as $item)
                    <button
                        type="button"
                        x-on:click="selectedImage = @js($item['url'])"
                        x-bind:class="selectedImage === @js($item['url']) ? 'ring-2 ring-inset ring-[var(--color-accent)]' : ''"
                        class="relative flex-1 overflow-hidden bg-[var(--color-bg-soft)] pb-focus-ring transition"
                    >
                        <div class="aspect-square">
                            <img
                                src="{{ $item['thumb'] ?? $item['url'] }}"
                                alt="{{ $item['label'] }}"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

    </div>

    {{-- ─── COLUNA DETALHES ────────────────────────────────────────────────── --}}
    <div class="flex flex-col bg-[var(--color-bg)]">

        {{-- Categoria + título + SKU --}}
        <div class="border-b border-[var(--color-border)] p-8 space-y-3 lg:p-10">
            <p class="pb-eyebrow">{{ $productCategories->join(' · ') }}</p>
            <h1 class="text-4xl leading-[1.05] lg:text-5xl">{{ $product->title }}</h1>
            <p class="text-xs uppercase tracking-[0.22em] text-[var(--color-text-secondary)]">SKU {{ $product->sku }}</p>
        </div>

        {{-- Descrição curta --}}
        @if ($product->short_description)
            <div class="border-b border-[var(--color-border)] p-8 lg:p-10">
                <p class="text-lg leading-8 text-[var(--color-text-secondary)]">{{ $product->short_description }}</p>
            </div>
        @endif

        {{-- Cores --}}
        @if (!empty($product->available_colors))
            <div class="border-b border-[var(--color-border)] p-8 space-y-4 lg:p-10">
                <p class="text-xs uppercase tracking-[0.22em] text-[var(--color-text-secondary)]">Cores disponíveis</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($product->available_colors as $color)
                        @php
                            $isHex    = is_string($color) && preg_match('/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color);
                            $normalized = $isHex ? strtoupper($color) : '';
                            $colorName  = $isHex ? (config('catalog.color_names')[$normalized] ?? 'Cor') : 'Cor';
                        @endphp
                        <button
                            type="button"
                            x-on:click="selectColor(@js($normalized))"
                            class="group flex items-center gap-2 pb-focus-ring"
                            title="{{ $colorName }}"
                        >
                            <span
                                class="block h-6 w-6 rounded-full border border-[var(--color-border)] transition group-hover:scale-110"
                                style="{{ $isHex ? 'background-color: '.$color.';' : '' }}"
                            ></span>
                            <span class="text-sm text-[var(--color-text-secondary)]">{{ $colorName }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Descrição técnica --}}
        <div class="border-b border-[var(--color-border)] p-8 lg:p-10">
            <div class="prose max-w-none text-sm prose-p:text-[var(--color-text-secondary)] prose-p:leading-7 prose-headings:text-[var(--color-text-primary)]">
                {!! $product->technical_description ?? '<p>Detalhes técnicos e variações disponíveis sob consulta.</p>' !!}
            </div>
        </div>

        {{-- CTA WhatsApp --}}
        <div class="mt-auto p-8 lg:p-10">
            <a
                href="https://wa.me/{{ config('catalog.whatsapp_number') }}?text={{ urlencode('Olá! Tenho interesse no produto ' . $product->title . ' (' . $product->sku . ').') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="pb-btn-primary pb-focus-ring inline-flex w-full items-center justify-center gap-3 px-6 py-4 text-sm uppercase tracking-[0.18em]"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Solicitar atendimento
            </a>
        </div>

    </div>

    {{-- ─── LIGHTBOX ───────────────────────────────────────────────────────── --}}
    <div
        x-cloak
        x-show="lightboxOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-[var(--color-bg)]/90 p-4 backdrop-blur-sm lg:col-span-2"
        style="display: none;"
    >
        <button
            type="button"
            x-on:click="closeLightbox()"
            class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center border border-[var(--color-border)] bg-[var(--color-bg)] text-xl text-[var(--color-text-primary)] transition hover:bg-[var(--color-bg-soft)]"
        >&times;</button>

        <button
            type="button"
            x-show="images.length > 1"
            x-on:click="prevImage()"
            class="absolute left-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center border border-[var(--color-border)] bg-[var(--color-bg)] text-[var(--color-text-primary)] transition hover:bg-[var(--color-bg-soft)]"
        >&#8249;</button>

        <div
            class="relative flex h-[88vh] w-[90vw] items-center justify-center overflow-hidden"
            x-on:wheel="wheelZoom($event)"
            x-on:mousedown="startDrag($event)"
            x-on:mousemove="onDrag($event)"
            x-on:mouseup="endDrag()"
            x-on:mouseleave="endDrag()"
            x-on:dblclick="toggleZoom($event)"
        >
            <img
                x-bind:src="currentImage()"
                alt="{{ $product->title }}"
                class="relative z-10 max-h-full max-w-full select-none object-contain transition-transform duration-150"
                x-bind:class="zoom > 1 ? 'cursor-grab' : 'cursor-zoom-in'"
                x-bind:style="'transform: translate(' + panX + 'px,' + panY + 'px) scale(' + zoom + ')'"
                draggable="false"
            >
        </div>

        <button
            type="button"
            x-show="images.length > 1"
            x-on:click="nextImage()"
            class="absolute right-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center border border-[var(--color-border)] bg-[var(--color-bg)] text-[var(--color-text-primary)] transition hover:bg-[var(--color-bg-soft)]"
        >&#8250;</button>

        <div class="absolute bottom-5 left-1/2 flex -translate-x-1/2 items-center border border-[var(--color-border)] bg-[var(--color-bg)]">
            <button type="button" x-on:click="zoomOut()" class="border-r border-[var(--color-border)] px-3 py-2 text-sm text-[var(--color-text-primary)] transition hover:bg-[var(--color-bg-soft)]">−</button>
            <span class="min-w-14 px-2 text-center text-xs uppercase tracking-[0.12em] text-[var(--color-text-secondary)]" x-text="Math.round(zoom * 100) + '%'"></span>
            <button type="button" x-on:click="zoomIn()" class="border-l border-[var(--color-border)] px-3 py-2 text-sm text-[var(--color-text-primary)] transition hover:bg-[var(--color-bg-soft)]">+</button>
        </div>
    </div>

</article>

{{-- ─── RELACIONADOS ───────────────────────────────────────────────────────── --}}
@if ($related->isNotEmpty())
    <section class="py-12 border-t border-[var(--color-border)]">
        <div class="mb-8 flex items-end justify-between">
            <div>
                <p class="pb-eyebrow mb-2">Sugestões</p>
                <h2 class="text-3xl leading-tight">Produtos relacionados</h2>
            </div>
            <a href="{{ route('products.index') }}" class="hidden text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)] transition hover:text-[var(--color-accent)] sm:block">
                Ver catálogo →
            </a>
        </div>
        <div class="grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($related as $item)
                <div class="bg-[var(--color-bg)]">
                    <x-product-card :product="$item" />
                </div>
            @endforeach
        </div>
    </section>
@endif

{{-- ─── CTA ────────────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed bg-[var(--color-accent)] mt-12 px-8 py-16 lg:px-16 lg:py-20">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-3">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Ficou com dúvida?</p>
            <h2 class="max-w-lg text-3xl leading-tight text-white lg:text-4xl">Manda uma mensagem.<br>A Bella responde.</h2>
            <p class="max-w-md text-white/70 leading-7">Sem formulário complicado. Direto com quem vai executar.</p>
        </div>
        <a
            href="https://wa.me/{{ config('catalog.whatsapp_number') }}"
            target="_blank"
            rel="noopener noreferrer"
            class="pb-focus-ring inline-flex shrink-0 items-center gap-3 border border-white/30 bg-white px-7 py-4 text-sm uppercase tracking-[0.2em] text-[var(--color-accent)] transition hover:bg-white/90"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Falar no WhatsApp
        </a>
    </div>
</section>

<script>
    (() => {
        try {
            const raw = sessionStorage.getItem('catalog:return-state');
            if (!raw) return;
            const state = JSON.parse(raw);
            if (!state || typeof state.url !== 'string') return;
            const link = document.getElementById('catalog-back-link');
            if (link) link.setAttribute('href', state.url);
        } catch (e) {}
    })();
</script>

@endsection
