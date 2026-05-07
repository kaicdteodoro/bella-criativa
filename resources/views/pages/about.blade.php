@extends('layouts.app')

@section('content')

{{-- ─── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden">
    <div class="grid gap-0 lg:grid-cols-[1fr_auto]">
        <div class="py-16 pr-0 lg:py-24 lg:pr-16">
            <p class="pb-eyebrow mb-6">Nossa história</p>
            <h1 class="max-w-2xl text-5xl leading-[1.05] lg:text-7xl">
                Dez anos<br>personalizando<br>marcas.
            </h1>
            <p class="mt-8 max-w-lg text-lg leading-8 text-[var(--color-text-secondary)]">
                Da primeira tag artesanal ao brinde corporativo que vai parar na mesa de um diretor — o
                princípio é o mesmo desde 2014: nenhum pedido é genérico.
            </p>
        </div>

        {{-- Bloco de cor da marca --}}
        <div class="hidden lg:flex w-56 xl:w-72 items-end bg-[var(--color-accent)] p-8">
            <img src="/images/logo-branco.png" alt="Bella" class="w-full">
        </div>
    </div>

    {{-- Linha decorativa --}}
    <div class="mt-0 h-px bg-[var(--color-border)]"></div>
</section>

{{-- ─── STATS ─────────────────────────────────────────────────────────────── --}}
<section class="grid grid-cols-2 gap-px bg-[var(--color-border)] border border-[var(--color-border)] lg:grid-cols-4">
    @foreach ([
        ['10+',       'anos no mercado'],
        ['Brasil',    'atendimento remoto, nacional'],
        ['5',         'técnicas de personalização'],
        ['sem mín.',  'em vários produtos'],
    ] as [$num, $label])
    <div class="bg-[var(--color-bg)] px-8 py-8 lg:py-10">
        <p class="text-3xl font-semibold text-[var(--color-accent)] lg:text-4xl">{{ $num }}</p>
        <p class="mt-2 text-sm leading-5 text-[var(--color-text-secondary)]">{{ $label }}</p>
    </div>
    @endforeach
</section>

{{-- ─── HISTÓRIA ───────────────────────────────────────────────────────────── --}}
<section class="grid gap-12 py-16 lg:grid-cols-[1fr_1fr] lg:py-24">
    <div class="space-y-6">
        <p class="pb-eyebrow">A origem</p>
        <h2 class="text-4xl leading-tight">Uma ideia simples que virou empresa.</h2>
        <div class="space-y-5 text-[var(--color-text-secondary)] leading-8">
            <p>
                Juliana já trabalhava com sabonetes e tags artesanais quando conheceu Verônica
                Pauludeto. Em 2014, as duas criaram a Bella Criativa — no começo, voltada para
                festas infantis. Por sete anos, foi esse o foco.
            </p>
            <p>
                A virada veio quando uma cliente abriu um caminho diferente: o mercado corporativo.
                Brindes, kits, produtos com a identidade de uma empresa gravada ou impressa com
                cuidado. Não era uma gráfica de volume — era personalização de verdade.
            </p>
            <p>
                Hoje a Bella atende empresas de todo o Brasil, de São Paulo ao Amazonas, com o mesmo
                princípio de sempre: você fala com quem executa, o produto sai como combinado.
            </p>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        {{-- Destaque editorial --}}
        <div class="flex-1 flex flex-col justify-center border-l-4 border-[var(--color-accent)] bg-[var(--color-bg-soft)] p-10">
            <p class="text-xs uppercase tracking-[0.24em] text-[var(--color-accent)] mb-4">Propósito</p>
            <blockquote class="text-2xl leading-snug lg:text-3xl">
                "Nenhum pedido é genérico. Cada produto tem a marca de quem pediu."
            </blockquote>
        </div>

        {{-- Linha do tempo compacta --}}
        <div class="grid grid-cols-3 gap-px bg-[var(--color-border)] border border-[var(--color-border)]">
            @foreach ([
                ['2014', 'Fundação — festas infantis'],
                ['2021', 'Expansão para o corporativo'],
                ['Hoje', 'Nacional, +10 anos'],
            ] as [$ano, $desc])
            <div class="bg-[var(--color-bg)] p-5">
                <p class="text-xl font-semibold text-[var(--color-accent)]">{{ $ano }}</p>
                <p class="mt-1 text-xs leading-5 text-[var(--color-text-secondary)]">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── TÉCNICAS ───────────────────────────────────────────────────────────── --}}
<section class="border-t border-[var(--color-border)] py-16 lg:py-24">
    <div class="mb-12 grid gap-4 lg:grid-cols-[1fr_1fr] lg:items-end">
        <div>
            <p class="pb-eyebrow mb-4">Processo</p>
            <h2 class="text-4xl leading-tight">Como personalizamos.</h2>
        </div>
        <p class="max-w-md text-[var(--color-text-secondary)] leading-7 lg:text-right">
            Cada técnica serve um propósito diferente. A Bella trabalha com as principais
            e escolhe a certa para o material e o resultado que você precisa.
        </p>
    </div>

    <div class="grid gap-px bg-[var(--color-border)] border border-[var(--color-border)] sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['01', 'Laser',       'Gravação permanente em metal, madeira e inox. Resultado limpo, duradouro.'],
            ['02', 'DTF',         'Impressão direta em tecido e poliéster. Detalhes finos, cores vibrantes.'],
            ['03', 'Silk',        'Serigrafia em superfícies rígidas. Perfeito para volume com consistência.'],
            ['04', 'Transfer',    'Aplicação de imagem em diferentes substratos. Versátil e preciso.'],
            ['05', 'Sublimação',  'Cor total em cerâmica e poliéster. Fotos, gradientes, ilustrações.'],
        ] as [$num, $name, $desc])
        <div class="bg-[var(--color-bg)] p-6 lg:p-8 group hover:bg-[var(--color-accent)] transition-colors duration-200">
            <p class="text-xs tracking-[0.28em] text-[var(--color-border)] group-hover:text-white/50 transition-colors">{{ $num }}</p>
            <p class="mt-4 text-lg font-semibold group-hover:text-white transition-colors">{{ $name }}</p>
            <p class="mt-2 text-sm leading-6 text-[var(--color-text-secondary)] group-hover:text-white/80 transition-colors">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ─── DIFERENCIAIS ───────────────────────────────────────────────────────── --}}
<section class="py-16 lg:py-24">
    <div class="mb-12">
        <p class="pb-eyebrow mb-4">Por que a Bella</p>
        <h2 class="text-4xl leading-tight">O que muda quando você trabalha com a gente.</h2>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Sem mínimo em vários produtos',   'Você não precisa pedir 200 unidades para ter um brinde com a sua marca.'],
            ['Artesanal com escala',             'A qualidade de acabamento de um produto feito com atenção — sem abrir mão de prazo.'],
            ['10 anos no mercado',               'Experiência real com o que funciona em personalização e o que não funciona.'],
            ['Atendimento direto',               'Você fala com quem executa. Sem intermediários, sem fila.'],
        ] as [$title, $body])
        <div class="border border-[var(--color-border)] p-7 space-y-3">
            <div class="h-px w-8 bg-[var(--color-accent)]"></div>
            <h3 class="text-base font-semibold leading-snug">{{ $title }}</h3>
            <p class="text-sm leading-6 text-[var(--color-text-secondary)]">{{ $body }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ─── CTA FINAL ──────────────────────────────────────────────────────────── --}}
<section class="pb-full-bleed bg-[var(--color-accent)] px-8 py-16 lg:px-16 lg:py-20">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-3">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Vamos começar</p>
            <h2 class="max-w-lg text-3xl leading-tight text-white lg:text-4xl">
                Tem um projeto em mente?<br>Manda uma mensagem.
            </h2>
            <p class="max-w-md text-white/70 leading-7">
                Sem formulário complicado. O jeito mais rápido é pelo WhatsApp — direto com quem vai executar.
            </p>
        </div>
        <a
            href="https://wa.me/5516994492382"
            target="_blank"
            rel="noopener noreferrer"
            class="pb-focus-ring inline-flex shrink-0 items-center gap-3 border border-white/30 bg-white px-7 py-4 text-sm uppercase tracking-[0.2em] text-[var(--color-accent)] transition-colors hover:bg-white/90"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.881 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            Falar no WhatsApp
        </a>
    </div>
</section>

@endsection
