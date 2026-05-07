<div>
    @forelse ($products as $product)
        @if ($loop->first)
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @endif

        <div>
            <x-product-card :product="$product" />
        </div>

        @if ($loop->last)
        </div>
        @endif
    @empty
        <div class="border border-[var(--color-border)] px-8 py-16 text-center">
            <p class="pb-eyebrow mb-3">Sem resultados</p>
            <p class="text-[var(--color-text-secondary)]">Nenhum produto encontrado com os filtros atuais.</p>
        </div>
    @endforelse

    @if ($products->count() >= $perPage)
        <div class="mt-8 flex justify-center border-t border-[var(--color-border)] pt-8">
            <button
                type="button"
                wire:click="loadMore"
                class="pb-focus-ring border border-[var(--color-border)] px-8 py-4 text-xs uppercase tracking-[0.2em] text-[var(--color-text-secondary)] transition hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]"
            >
                Carregar mais
            </button>
        </div>
    @endif
</div>
