@extends('layouts.app')

@section('content')

{{-- ─── HERO ──────────────────────────────────────────────────────────────── --}}
<section class="border-b border-[var(--color-border)] pb-14 pt-12 lg:pt-20">
    <p class="pb-eyebrow mb-8">Produtos personalizados desde 2014</p>
    <h1 class="text-[3.75rem] leading-[0.95] tracking-tight lg:text-[6rem] xl:text-[7.5rem]">
        {!! nl2br(e($page?->sections?->where('type','hero')->first()?->heading ?? "Cada detalhe,\ndo seu jeito.")) !!}
    </h1>
    <div class="mt-8 grid gap-6 border-t border-[var(--color-border)] pt-8 lg:grid-cols-[1fr_auto] lg:items-end">
        <p class="max-w-lg text-lg leading-8 text-[var(--color-text-secondary)]">
            {{ $page?->sections?->where('type','hero')->first()?->content['text']
                ?? 'Brindes, kits e produtos personalizados para empresas que valorizam acabamento, atendimento direto e opções sem quantidade mínima em linhas selecionadas.' }}
        </p>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('products.index') }}" class="pb-btn-primary pb-focus-ring inline-flex px-7 py-4 text-sm uppercase tracking-[0.18em]">
                Ver catálogo
            </a>
            <a href="{{ route('about') }}" class="pb-focus-ring inline-flex items-center gap-2 border border-[var(--color-border)] px-7 py-4 text-sm uppercase tracking-[0.18em] text-[var(--color-text-secondary)] transition hover:border-[var(--color-text-primary)] hover:text-[var(--color-text-primary)]">
                Conhecer a Bella
            </a>
        </div>
    </div>
</section>

{{-- ─── STATS ──────────────────────────────────────────────────────────────── --}}
<section class="grid grid-cols-2 gap-px bg-[var(--color-border)] border border-[var(--color-border)] my-12 lg:grid-cols-4">
    @foreach ([
        ['value' => '10+', 'label' => 'anos no mercado', 'count' => 10, 'suffix' => '+'],
        ['value' => 'Brasil', 'label' => 'atendimento em todo o país'],
        ['value' => '5', 'label' => 'técnicas de personalização', 'count' => 5],
        ['value' => 'Sem mínimo', 'label' => 'em linhas selecionadas'],
    ] as $stat)
    <div class="bg-[var(--color-bg)] px-8 py-8">
        @if (isset($stat['count']))
            <p
                x-data="statCounter({ target: {{ $stat['count'] }}, suffix: '{{ $stat['suffix'] ?? '' }}' })"
                x-text="formatted()"
                class="text-3xl font-semibold text-[var(--color-accent)]"
            >{{ $stat['value'] }}</p>
        @else
            <p class="text-3xl font-semibold text-[var(--color-accent)]">{{ $stat['value'] }}</p>
        @endif
        <p class="mt-1 text-sm text-[var(--color-text-secondary)]">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</section>

{{-- ─── CATEGORIAS ─────────────────────────────────────────────────────────── --}}
@if($sectionCategories->isNotEmpty())
<section class="py-4 border-t border-[var(--color-border)]">
    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="pb-eyebrow mb-2">Catálogo</p>
            <h2 class="text-3xl leading-tight">O que você pode personalizar</h2>
        </div>
        <a href="{{ route('products.index') }}" class="hidden text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)] transition hover:text-[var(--color-accent)] sm:block">
            Ver tudo →
        </a>
    </div>

    @if($categorySlides->isNotEmpty())
        <div
            x-data="{
                index: 0,
                total: {{ $categorySlides->count() }},
                timer: null,
                dragging: false,
                dragStartX: 0,
                dragDeltaX: 0,
                start() {
                    if (this.total <= 1 || this.timer) return;
                    this.timer = setInterval(() => {
                        this.next();
                    }, 4200);
                },
                stop() {
                    if (!this.timer) return;
                    clearInterval(this.timer);
                    this.timer = null;
                },
                restart() {
                    this.stop();
                    this.start();
                },
                goTo(nextIndex) {
                    this.index = nextIndex;
                    this.restart();
                },
                next() {
                    this.index = (this.index + 1) % this.total;
                },
                prev() {
                    this.index = (this.index - 1 + this.total) % this.total;
                },
                pointerDown(event) {
                    this.dragging = true;
                    this.dragStartX = event.clientX ?? (event.touches?.[0]?.clientX ?? 0);
                    this.dragDeltaX = 0;
                    this.stop();
                },
                pointerMove(event) {
                    if (!this.dragging) return;
                    const point = event.clientX ?? (event.touches?.[0]?.clientX ?? this.dragStartX);
                    this.dragDeltaX = point - this.dragStartX;
                },
                pointerUp() {
                    if (!this.dragging) return;
                    if (this.dragDeltaX <= -48) this.next();
                    if (this.dragDeltaX >= 48) this.prev();
                    this.dragging = false;
                    this.dragDeltaX = 0;
                    this.start();
                },
            }"
            x-init="start()"
            x-on:mouseenter="stop()"
            x-on:mouseleave="start()"
            x-on:touchstart.passive="pointerDown($event)"
            x-on:touchmove.passive="pointerMove($event)"
            x-on:touchend="pointerUp()"
            x-on:mousedown="pointerDown($event)"
            x-on:mousemove="pointerMove($event)"
            x-on:mouseup="pointerUp()"
            x-on:mouseleave="pointerUp()"
            class="select-none"
        >
            <div class="mb-4 flex items-center justify-end gap-2">
                <button
                    type="button"
                    x-on:click="prev(); restart();"
                    class="pb-focus-ring inline-flex h-10 w-10 items-center justify-center border border-[var(--color-border)] bg-white text-lg text-[var(--color-text-primary)] transition hover:border-[var(--color-text-primary)]"
                    aria-label="Slide anterior"
                >&#8249;</button>
                <button
                    type="button"
                    x-on:click="next(); restart();"
                    class="pb-focus-ring inline-flex h-10 w-10 items-center justify-center border border-[var(--color-border)] bg-white text-lg text-[var(--color-text-primary)] transition hover:border-[var(--color-text-primary)]"
                    aria-label="Próximo slide"
                >&#8250;</button>
            </div>

            <div class="overflow-hidden border border-[var(--color-border)] cursor-grab active:cursor-grabbing">
                <div
                    class="flex transition-transform duration-500 ease-out will-change-transform"
                    x-bind:style="`transform: translateX(calc(-${index * 100}% + ${dragDeltaX}px));`"
                >
                    @foreach ($categorySlides as $slide)
                        <div class="w-full shrink-0">
                            <a
                                href="{{ route('products.index', ['categoria' => $slide['slug']]) }}"
                                class="pb-focus-ring group relative block min-h-[26rem] overflow-hidden bg-[var(--color-bg-soft)]"
                            >
                                @if (!empty($slide['image']))
                                    <img
                                        src="{{ $slide['image'] }}"
                                        alt="{{ $slide['name'] }}"
                                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                                        decoding="async"
                                        class="absolute right-0 top-0 h-full w-full object-contain p-3 transition duration-700 group-hover:scale-[1.03] md:w-[70%] md:p-6"
                                    >
                                @else
                                    <div class="absolute inset-0 bg-[var(--color-bg-soft)]"></div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/70 to-transparent md:from-white/88 md:via-white/40 md:to-transparent"></div>
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_78%_40%,rgba(255,255,255,0.3),transparent_50%)]"></div>

                                <div class="absolute inset-x-0 bottom-0 top-0 flex items-end p-6 md:w-[44%] md:items-center md:p-10">
                                    <div>
                                    <p class="text-[10px] uppercase tracking-[0.24em] text-[var(--color-text-secondary)]">Categoria</p>
                                    <h3 class="mt-2 text-3xl leading-tight text-[var(--color-text-primary)]">{{ $slide['name'] }}</h3>
                                    @if(!empty($slide['description']))
                                        <p class="mt-3 max-w-xl text-sm leading-7 text-[var(--color-text-secondary)] line-clamp-2">
                                            {{ $slide['description'] }}
                                        </p>
                                    @endif
                                    <div class="mt-5 flex items-center justify-between gap-4">
                                        <span class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)] group-hover:text-[var(--color-accent)] transition-colors">
                                            Ver produtos da categoria →
                                        </span>
                                        <span class="text-[11px] uppercase tracking-[0.16em] text-[var(--color-text-secondary)]">
                                            {{ $slide['products_count'] }} itens
                                        </span>
                                    </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($categorySlides->count() > 1)
                <div class="mt-4 flex items-center justify-center gap-2">
                    @foreach ($categorySlides as $i => $slide)
                        <button
                            type="button"
                            x-on:click="goTo({{ $i }})"
                            class="pb-focus-ring h-2.5 w-2.5 rounded-full border transition"
                            x-bind:class="index === {{ $i }} ? 'border-[var(--color-accent)] bg-[var(--color-accent)]' : 'border-[var(--color-border)] bg-white'"
                            aria-label="Ir para {{ $slide['name'] }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</section>
@endif

@if($featuredProducts->isNotEmpty())
<section class="py-12 border-t border-[var(--color-border)]">
    <div class="mb-8 flex items-end justify-between gap-6">
        <div>
            <p class="pb-eyebrow mb-2">Seleção da Bella</p>
            <h2 class="text-3xl leading-tight">Produtos em destaque para começar a explorar</h2>
        </div>
        <a href="{{ route('products.index') }}" class="hidden text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)] transition hover:text-[var(--color-accent)] sm:block">
            Ver catálogo completo →
        </a>
    </div>

    <div class="grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($featuredProducts as $product)
            <div class="bg-[var(--color-bg)]">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ─── TÉCNICAS ───────────────────────────────────────────────────────────── --}}
<section class="py-12 border-t border-[var(--color-border)]">
    <div class="mb-8">
        <p class="pb-eyebrow mb-2">Processo</p>
        <h2 class="text-3xl leading-tight">Como personalizamos</h2>
    </div>
    <div class="grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            ['Laser',      'Metal, madeira e inox'],
            ['DTF',        'Tecido e poliéster'],
            ['Silk',       'Superfícies rígidas'],
            ['Transfer',   'Múltiplos substratos'],
            ['Sublimação', 'Cerâmica e poliéster'],
        ] as [$nome, $desc])
        <div class="bg-[var(--color-bg)] p-6 group hover:bg-[var(--color-accent)] transition-colors duration-200">
            <p class="text-lg font-semibold group-hover:text-white transition-colors">{{ $nome }}</p>
            <p class="mt-1 text-sm text-[var(--color-text-secondary)] group-hover:text-white/70 transition-colors">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ─── CTA ────────────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed bg-[var(--color-accent)] mt-12 px-8 py-16 lg:px-16 lg:py-20">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-3">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Vamos começar</p>
            <h2 class="max-w-lg text-3xl leading-tight text-white lg:text-4xl">Tem um projeto em mente?<br>Manda uma mensagem.</h2>
            <p class="max-w-md text-white/70 leading-7">Sem formulário complicado. Direto com quem vai executar.</p>
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
