<!doctype html>
<html lang="pt-BR">
<head>
    @php
        $siteName = 'Bella Criativa';
        $defaultDescription = 'Brindes, kits e produtos personalizados para empresas. Atendimento direto, catálogo atualizado e produção com acabamento profissional.';
        $defaultImage = asset('images/foto-perfil.png');
        // Filtered catalog pages get noindex — canonical points to the clean URL
        $canonical = request()->routeIs('products.index') && request()->query()
            ? url()->current()
            : url()->full();

        $seoTitle = $title ?? $siteName;
        $seoDescription = $description ?? $defaultDescription;
        $seoImage = $defaultImage;
        $seoType = 'website';
        $robots = $robots ?? 'index,follow';
        $organizationLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => $defaultImage,
            'sameAs' => ['https://instagram.com/bella_dpfc'],
        ];

        if (isset($product)) {
            $seoTitle = "{$product->title} | {$siteName}";
            $seoDescription = $product->short_description
                ? \Illuminate\Support\Str::limit(strip_tags($product->short_description), 160)
                : $defaultDescription;
            $seoImage = $product->og_image_url ?? $product->featured_image_url ?? $defaultImage;
            $seoType = 'product';
        } elseif (isset($category)) {
            $seoTitle = "{$category->filterDisplayName()} | {$siteName}";
            $seoDescription = $category->description
                ? \Illuminate\Support\Str::limit(strip_tags($category->description), 160)
                : $defaultDescription;
            $seoImage = (isset($categoryOgImage) && $categoryOgImage) ? $categoryOgImage : $defaultImage;
        } elseif (isset($page)) {
            $seoTitle = $page->title === $siteName ? $siteName : "{$page->title} | {$siteName}";
            $seoDescription = $page->meta_description
                ? \Illuminate\Support\Str::limit(strip_tags($page->meta_description), 160)
                : $defaultDescription;
            $seoImage = $page->og_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($page->og_image) : $defaultImage;
        }

        if ($seoTitle === "{$siteName} | {$siteName}") {
            $seoTitle = $siteName;
        }

        if (request()->routeIs('products.index') && request()->query() !== []) {
            $robots = 'noindex,follow';
        }

        if (\Illuminate\Support\Str::startsWith($seoImage, '/')) {
            $seoImage = url($seoImage);
        }
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/foto-perfil.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=optional" onload="this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=optional"></noscript>
    <script type="application/ld+json">{!! json_encode($organizationLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @php
        $viteManifestExists = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
    @endphp
    @stack('head-preload')
    @if ($viteManifestExists)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[var(--color-bg)] text-[var(--color-text-primary)]">
    @unless ($viteManifestExists)
        <div style="margin:16px;padding:12px 16px;border:1px solid #f3c7a4;background:#fff4ea;color:#6f2f12;font:14px/1.4 sans-serif">
            Recursos de frontend indisponiveis no deploy atual. Falta publicar <code>public/build</code>.
        </div>
    @endunless
    <a href="#main-content" class="pb-skip-link pb-focus-ring">Ir para o conteúdo</a>
    <x-header />

    <main id="main-content" class="mx-auto max-w-7xl px-6 pt-10">
        @yield('content')
    </main>

    <button
        type="button"
        x-data="scrollTopControl()"
        x-cloak
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-220"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-3 opacity-0"
        x-on:click="scrollToTop()"
        class="pb-scroll-top pb-focus-ring"
        aria-label="Voltar ao topo"
    >
        <span aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none">
                <path d="M10 15V5M10 5 5.75 9.25M10 5l4.25 4.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    </button>

    @livewireScriptConfig
 </body>
</html>
