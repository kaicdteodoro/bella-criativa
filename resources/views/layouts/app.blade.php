<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Bella Criativa' }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/foto-perfil.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--color-bg)] text-[var(--color-text-primary)]">
    <a href="#main-content" class="pb-skip-link pb-focus-ring">Ir para o conteúdo</a>
    <x-header />

    <main id="main-content" class="mx-auto max-w-7xl px-6 pt-10">
        @yield('content')
    </main>
    @livewireScripts
 </body>
</html>
