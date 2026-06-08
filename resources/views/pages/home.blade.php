@extends('layouts.app')

@push('head-preload')
    <link rel="preload" as="image" href="/images/home/home-hero-sm.webp" media="(max-width: 640px)" fetchpriority="high">
    <link rel="preload" as="image" href="/images/home/home-hero.webp" media="(min-width: 641px)" fetchpriority="high">
@endpush

@section('content')
@php
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
        ],
    ];

    $faqLd = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Qual o pedido mínimo para brindes personalizados?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'O pedido mínimo varia por produto e técnica de personalização. Em linhas selecionadas, trabalhamos sem pedido mínimo. Entre em contato pelo WhatsApp para orientação sobre o seu projeto.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Vocês entregam brindes para todo o Brasil?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Sim, a Bella Criativa atende empresas em todo o território nacional com entrega por transportadora.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Quais técnicas de personalização estão disponíveis?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Trabalhamos com laser, DTF, silk, transfer e sublimação. Cada técnica é indicada para diferentes materiais — metal, madeira, tecido, cerâmica e mais.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Como solicitar um orçamento de brinde corporativo?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Envie uma mensagem no WhatsApp com o produto desejado, a quantidade estimada e o logo da sua empresa. Respondemos rapidamente com valores e prazo de produção.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Vocês atendem empresas de qualquer porte?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Sim. Atendemos desde pequenas empresas a grandes corporações. Temos opções para pedidos pontuais e contratos de fornecimento recorrente.',
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

{{-- ─── HERO ──────────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed -mt-10 overflow-hidden border-b border-[var(--color-border)]">
    <div class="flex flex-col items-stretch lg:min-h-[620px] lg:flex-row">

        {{-- Mobile: entrada visual mais comercial, sem alterar o hero desktop aprovado --}}
        <div class="pb-mobile-hero relative min-h-[calc(100svh-5.5rem)] overflow-hidden lg:hidden">
            <picture>
                <source srcset="/images/home/home-hero-sm.webp" media="(max-width: 640px)" type="image/webp">
                <source srcset="/images/home/home-hero.webp" type="image/webp">
                <img
                    src="/images/home/home-hero-sm.webp"
                    alt="Kit personalizado com brinde corporativo — Bella Criativa"
                    loading="eager"
                    fetchpriority="high"
                    decoding="sync"
                    class="absolute inset-0 h-full w-full object-cover object-[58%_center]"
                >
            </picture>
            <div class="absolute inset-0 bg-gradient-to-t from-black/94 via-black/54 to-black/10"></div>
            <div class="absolute inset-x-0 bottom-0 px-6 pb-8 pt-24">
                <div class="max-w-[21rem]">
                <p class="mb-4 text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-white/78">Brindes corporativos desde 2014</p>
                <h1 class="text-[2.35rem] font-semibold leading-[1.02] tracking-tight text-white drop-shadow-[0_2px_16px_rgba(0,0,0,0.42)]">
                    {!! nl2br(e($page?->sections?->where('type','hero')->first()?->heading ?? "Brindes que ficam.\nNão que vão pra gaveta.")) !!}
                </h1>
                <p class="mt-5 text-[0.95rem] leading-6 text-white/86 drop-shadow-[0_1px_10px_rgba(0,0,0,0.4)]">
                    Kits e produtos personalizados com acabamento de presente, prontos para levar sua marca junto.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="pb-focus-ring inline-flex bg-white px-5 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-[var(--color-text-primary)] shadow-[0_14px_34px_rgba(0,0,0,0.18)]">
                        Ver catálogo
                    </a>
                    <a href="{{ route('contact') }}" class="pb-focus-ring inline-flex border border-white/45 bg-black/18 px-5 py-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white backdrop-blur-sm">
                        Orçar
                    </a>
                </div>
                </div>
            </div>
        </div>

        {{-- Texto: padding esquerdo alinhado com o container do site --}}
        <div class="pb-hero-text-pad hidden flex-1 flex-col justify-center py-14 pr-8 lg:flex lg:py-20 lg:pr-16 xl:pr-24">
            <p class="pb-eyebrow mb-6">Brindes corporativos personalizados desde 2014</p>
            <h1 class="text-4xl leading-[1.1] tracking-tight lg:text-5xl xl:text-6xl">
                {!! nl2br(e($page?->sections?->where('type','hero')->first()?->heading ?? "Brindes que ficam.\nNão que vão pra gaveta.")) !!}
            </h1>
            <div class="mt-8 max-w-2xl border-t border-[var(--color-border)] pt-8">
                <p class="text-pretty text-[1.05rem] leading-8 text-[var(--color-text-secondary)]">
                    {{ $page?->sections?->where('type','hero')->first()?->content['text']
                        ?? 'Kits, produtos e brindes com a identidade da sua empresa. Você fala com quem executa — direto, sem fila.' }}
                </p>
                <div class="mt-7 flex flex-wrap gap-4">
                    <a href="{{ route('products.index') }}" class="pb-btn-primary pb-focus-ring inline-flex px-7 py-4 text-sm uppercase tracking-[0.18em]">
                        Ver catálogo
                    </a>
                    <a href="{{ route('about') }}" class="pb-focus-ring inline-flex items-center gap-2 border border-[var(--color-border)] px-7 py-4 text-sm uppercase tracking-[0.18em] text-[var(--color-text-secondary)] transition hover:border-[var(--color-text-primary)] hover:text-[var(--color-text-primary)]">
                        Conhecer a Bella
                    </a>
                </div>
            </div>
        </div>

        {{-- Imagem: sangra até a borda direita do viewport --}}
        <div class="relative hidden w-[42%] shrink-0 overflow-hidden lg:block xl:w-[46%]">
            {{-- Fade suave na borda esquerda (transição entre texto e foto) --}}
            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-28 bg-gradient-to-r from-[var(--color-bg)] to-transparent"></div>
            <picture>
                <source srcset="/images/home/home-hero.webp" type="image/webp">
                <img
                    src="/images/home/home-hero.webp"
                    alt="Kit personalizado com brinde corporativo — Bella Criativa"
                    loading="eager"
                    fetchpriority="high"
                    decoding="sync"
                    class="h-full w-full object-cover object-center"
                >
            </picture>
        </div>

    </div>
</section>

{{-- ─── STATS ──────────────────────────────────────────────────────────────── --}}
<section class="pb-mobile-stats -mx-6 grid grid-cols-2 gap-px bg-[var(--color-border)] border-y border-[var(--color-border)] my-0 lg:mx-0 lg:my-12 lg:grid-cols-4 lg:border">
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
        <p class="mt-1 text-sm leading-5 text-[var(--color-text-secondary)]">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</section>

{{-- ─── PORTFÓLIO / LINHAS ──────────────────────────────────────────────────── --}}
<section class="-mx-6 bg-[var(--color-surface-warm-start)] px-6 py-10 lg:mx-0 lg:bg-transparent lg:px-0 lg:py-12 lg:border-t lg:border-[var(--color-border)]">
    <div class="mb-6 flex items-end justify-between gap-6 lg:mb-8">
        <div>
            <p class="pb-eyebrow mb-2">Portfólio</p>
            <h2 class="max-w-[16rem] text-3xl leading-tight lg:max-w-none">Personalizado do seu jeito.</h2>
        </div>
        <a href="{{ route('products.index') }}" class="hidden text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)] transition hover:text-[var(--color-accent)] sm:block">
            Ver catálogo →
        </a>
    </div>

    <div class="pb-mobile-portfolio -mx-6 flex snap-x snap-mandatory gap-3 overflow-x-auto px-6 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-px sm:overflow-visible sm:px-0 lg:bg-[var(--color-border)] lg:border lg:border-[var(--color-border)]">
        @foreach ([
            [
                'img'   => '/images/home/linhas-01-kits-corporativos.webp',
                'title' => 'Kits corporativos',
                'desc'  => 'Garrafa, brinde, kit completo com a marca da sua empresa.',
                'alt'   => 'Kit corporativo personalizado com garrafa térmica, notebook e fone — Bella Criativa',
            ],
            [
                'img'   => '/images/home/linhas-02-brindes-criativos.webp',
                'title' => 'Brindes criativos',
                'desc'  => 'Sacolas, livros e peças únicas para quem quer ser lembrado.',
                'alt'   => 'Kit brinde criativo com sacola de tela e livro de colorir personalizado — Bella Criativa',
            ],
            [
                'img'   => '/images/home/linhas-03-linha-executiva.webp',
                'title' => 'Linha executiva',
                'desc'  => 'Caderno, caneta, caixa premium. Acabamento que comunica valor.',
                'alt'   => 'Kit executivo personalizado com caderno de couro e caneta — Bella Criativa',
            ],
            [
                'img'   => '/images/home/linhas-04-presentes-gourmet.webp',
                'title' => 'Presentes gourmet',
                'desc'  => 'Para o cliente que merece mais do que uma caneta com logo.',
                'alt'   => 'Kit presente premium gourmet com whisky e café especial — Bella Criativa',
            ],
        ] as $card)
        <a
            href="{{ route('products.index') }}"
            class="pb-focus-ring group relative block w-[82vw] shrink-0 snap-start overflow-hidden bg-[var(--color-bg-soft)] sm:w-auto"
        >
            <div class="aspect-[3/4] sm:aspect-[4/3]">
                <img
                    src="{{ $card['img'] }}"
                    alt="{{ $card['alt'] }}"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.04]"
                >
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent transition duration-300 group-hover:from-black/80"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 lg:p-8">
                <p class="mb-2 text-[10px] uppercase tracking-[0.22em] text-white/50">Portfólio</p>
                <p class="text-lg font-semibold leading-snug text-white">{{ $card['title'] }}</p>
                <p class="mt-1.5 max-w-xs text-sm leading-5 text-white/70 line-clamp-2 lg:line-clamp-none">{{ $card['desc'] }}</p>
            </div>
        </a>
        @endforeach
    </div>
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

{{-- ─── FAQ ─────────────────────────────────────────────────────────────────── --}}
<section class="py-12 border-t border-[var(--color-border)]">
    <div class="mb-8">
        <p class="pb-eyebrow mb-2">Dúvidas frequentes</p>
        <h2 class="text-3xl leading-tight">Perguntas de quem quer pedir certo</h2>
    </div>
    <div class="divide-y divide-[var(--color-border)] border-t border-[var(--color-border)]">
        @foreach ([
            [
                'q' => 'Qual o pedido mínimo para brindes personalizados?',
                'a' => 'Varia por produto e técnica. Em linhas selecionadas, trabalhamos sem pedido mínimo. Para kits e impressão, entre em contato — orientamos conforme o seu projeto.',
            ],
            [
                'q' => 'Vocês entregam para todo o Brasil?',
                'a' => 'Sim. Atendemos empresas em todo o território nacional com entrega via transportadora.',
            ],
            [
                'q' => 'Quais técnicas de personalização estão disponíveis?',
                'a' => 'Laser, DTF, Silk, Transfer e Sublimação. Cada técnica é indicada para um tipo de material — metal, madeira, tecido, cerâmica e outros.',
            ],
            [
                'q' => 'Como solicitar um orçamento?',
                'a' => 'Pelo WhatsApp: produto desejado, quantidade estimada e logo da empresa. Respondemos com valor e prazo em até 1 dia útil.',
            ],
            [
                'q' => 'Vocês atendem empresas de qualquer porte?',
                'a' => 'Sim. De pequenas empresas a grandes corporações, com opções para pedidos pontuais ou fornecimento recorrente.',
            ],
        ] as $item)
        <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-5 text-base font-medium text-[var(--color-text-primary)] transition hover:text-[var(--color-accent)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]">
                <span>{{ $item['q'] }}</span>
                <span class="shrink-0 transition-transform duration-300 group-open:rotate-45" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </span>
            </summary>
            <p class="pb-5 text-sm leading-7 text-[var(--color-text-secondary)]">{{ $item['a'] }}</p>
        </details>
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
