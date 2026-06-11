<div
    x-data
    class="relative"
    @keydown.escape.window="$store.search.open = false"
>
    <button
        type="button"
        @click="$store.search.open = true; $nextTick(() => $refs.searchInput.focus())"
        class="pb-focus-ring inline-flex h-11 w-11 items-center justify-center rounded-sm text-[var(--color-text-secondary)] transition hover:text-[var(--color-text-primary)]"
        aria-label="Buscar produtos"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="$store.search.open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="$store.search.open = false"
        class="fixed inset-0 z-50 bg-black/40 p-6 pt-24"
    >
        <div class="mx-auto max-w-xl border border-[var(--color-border)] bg-[var(--color-bg)] shadow-2xl">

            <div class="flex items-center gap-3 border-b border-[var(--color-border)] px-4">
                <svg class="h-4 w-4 shrink-0 text-[var(--color-text-secondary)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    x-ref="searchInput"
                    type="text"
                    wire:model.live.debounce.300ms="query"
                    placeholder="Buscar por nome ou SKU…"
                    class="w-full bg-transparent py-4 text-sm text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)] focus:outline-none"
                    autocomplete="off"
                    spellcheck="false"
                >
                <button
                    type="button"
                    @click="$store.search.open = false"
                    class="pb-focus-ring shrink-0 text-xs uppercase tracking-[0.16em] text-[var(--color-text-secondary)] transition hover:text-[var(--color-text-primary)]"
                >
                    Esc
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                @forelse ($results as $result)
                    <a
                        href="{{ route('products.show', $result->slug) }}"
                        @click="$store.search.open = false"
                        class="pb-focus-ring flex items-center justify-between border-b border-[var(--color-border)] px-4 py-3 transition last:border-b-0 hover:bg-[var(--color-bg-soft)]"
                    >
                        <div>
                            <p class="text-sm font-medium">{{ $result->title }}</p>
                            <p class="text-xs text-[var(--color-text-secondary)]">{{ $result->sku }}</p>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-[var(--color-text-secondary)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                @empty
                    @if (mb_strlen($query) >= 2)
                        <p class="px-4 py-6 text-sm text-[var(--color-text-secondary)]">Nenhum resultado para "{{ $query }}".</p>
                    @else
                        <p class="px-4 py-6 text-xs uppercase tracking-[0.18em] text-[var(--color-text-secondary)]">Digite pelo menos 2 caracteres</p>
                    @endif
                @endforelse
            </div>

        </div>
    </div>
</div>
