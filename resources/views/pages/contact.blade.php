@extends('layouts.app')

@section('content')

{{-- ─── HERO ──────────────────────────────────────────────────────────────── --}}
<section class="border-b border-[var(--color-border)] pb-12 pt-10 lg:pt-16">
    <p class="pb-eyebrow mb-8">Fale com a Bella</p>
    <a
        href="https://wa.me/5516994492382"
        target="_blank"
        rel="noopener noreferrer"
        class="group block"
    >
        <p class="mb-3 text-xs uppercase tracking-[0.22em] text-[var(--color-text-secondary)] transition-colors group-hover:text-[var(--color-accent)]">WhatsApp</p>
        <h1 class="text-[3rem] leading-[1.0] transition-colors group-hover:text-[var(--color-accent)] lg:text-[5rem] xl:text-[6.5rem]">
            (16)&nbsp;99449&#8209;2382
        </h1>
    </a>
    <p class="mt-8 max-w-lg text-lg leading-8 text-[var(--color-text-secondary)]">
        O jeito mais rápido é pelo WhatsApp — direto com quem vai executar o seu pedido.
    </p>
</section>

{{-- ─── CANAIS ─────────────────────────────────────────────────────────────── --}}
<section class="grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] my-12 sm:grid-cols-2">

    {{-- WhatsApp --}}
    <a
        href="https://wa.me/5516994492382"
        target="_blank"
        rel="noopener noreferrer"
        class="group bg-[var(--color-bg)] p-8 lg:p-10 transition hover:bg-[var(--color-accent)]"
    >
        <div class="flex items-start justify-between">
            <p class="text-xs uppercase tracking-[0.24em] text-[var(--color-text-secondary)] group-hover:text-white/60 transition-colors">Principal</p>
            <svg class="h-5 w-5 text-[var(--color-accent)] group-hover:text-white transition-colors" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </div>
        <p class="mt-6 text-3xl font-semibold group-hover:text-white transition-colors">(16) 99449-2382</p>
        <p class="mt-2 text-sm text-[var(--color-text-secondary)] group-hover:text-white/70 transition-colors">WhatsApp — resposta mais rápida</p>
    </a>

    {{-- Instagram --}}
    <a
        href="https://instagram.com/bella_dpfc"
        target="_blank"
        rel="noopener noreferrer"
        class="group bg-[var(--color-bg-soft)] p-8 lg:p-10 transition hover:bg-[var(--color-bg-soft)]"
    >
        <div class="flex items-start justify-between">
            <p class="text-xs uppercase tracking-[0.24em] text-[var(--color-text-secondary)]">Instagram</p>
            <svg class="h-5 w-5 text-[var(--color-text-secondary)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
        </div>
        <p class="mt-6 text-3xl font-semibold">@bella_dpfc</p>
        <p class="mt-2 text-sm text-[var(--color-text-secondary)]">Portfólio e novidades</p>
    </a>
</section>

{{-- ─── NOTA OPERACIONAL ───────────────────────────────────────────────────── --}}
<section class="border-t border-[var(--color-border)] py-10">
    <div class="grid gap-8 lg:grid-cols-3">
        @foreach ([
            ['Atendimento remoto', 'A Bella não faz atendimentos presenciais. Atende clientes de todo o Brasil — de SP ao Amazonas — de forma 100% remota.'],
            ['Sem mínimo em vários produtos', 'Você não precisa pedir grandes quantidades para ter um produto com a sua marca. Consulte disponibilidade pelo WhatsApp.'],
            ['Direto com quem executa', 'Você fala com a Juliana — não com um atendente. O que você combina é o que sai.'],
        ] as [$titulo, $texto])
        <div class="space-y-3">
            <div class="h-px w-8 bg-[var(--color-accent)]"></div>
            <h3 class="text-base font-semibold">{{ $titulo }}</h3>
            <p class="text-sm leading-6 text-[var(--color-text-secondary)]">{{ $texto }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ─── CTA ────────────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed bg-[var(--color-accent)] px-8 py-14 lg:px-16 lg:py-18">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Começar agora</p>
            <h2 class="max-w-md text-3xl leading-tight text-white">Manda uma mensagem. A Bella responde.</h2>
        </div>
        <a
            href="https://wa.me/5516994492382"
            target="_blank"
            rel="noopener noreferrer"
            class="pb-focus-ring inline-flex shrink-0 border border-white/30 bg-white px-7 py-4 text-sm uppercase tracking-[0.2em] text-[var(--color-accent)] transition hover:bg-white/90"
        >
            Abrir WhatsApp
        </a>
    </div>
</section>

@endsection
