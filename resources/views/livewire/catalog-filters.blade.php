<div class="flex flex-wrap items-center gap-3">

    {{-- Busca --}}
    <div class="relative min-w-48 flex-1">
        <input
            type="text"
            wire:model.live.debounce.500ms="search"
            placeholder="Buscar por nome ou SKU..."
            class="pb-focus-ring w-full border border-[var(--color-border)] bg-[var(--color-bg)] px-4 py-2.5 text-sm text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)]"
        >
    </div>

    {{-- Todos / limpar --}}
    <button
        type="button"
        wire:click="clearFilters"
        @class([
            'pb-focus-ring border px-4 py-2.5 text-xs uppercase tracking-[0.18em] transition',
            'border-[var(--color-accent)] bg-[var(--color-accent)] text-white' => !$category && !$search,
            'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]' => $category || $search,
        ])
    >
        Todos
    </button>

    {{-- Categorias --}}
    @foreach ($categories as $item)
        <button
            type="button"
            wire:click="toggleCategory('{{ $item->slug }}')"
            @class([
                'pb-focus-ring border px-4 py-2.5 text-xs uppercase tracking-[0.18em] transition',
                'border-[var(--color-accent)] bg-[var(--color-accent)] text-white' => $category === $item->slug,
                'border-[var(--color-border)] text-[var(--color-text-secondary)] hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]' => $category !== $item->slug,
            ])
        >
            {{ $item->name }}
        </button>
    @endforeach

</div>
