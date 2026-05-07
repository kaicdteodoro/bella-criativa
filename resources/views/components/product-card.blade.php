@props(['product'])

<a
    href="{{ route('products.show', $product->slug) }}"
    onclick="try{sessionStorage.setItem('catalog:return-state', JSON.stringify({url: window.location.pathname + window.location.search, y: window.scrollY}));}catch(e){}"
    class="pb-card-hover pb-focus-ring group flex h-full flex-col overflow-hidden border border-[var(--color-border)] bg-white"
>
    <div class="aspect-[4/5] bg-white p-5">
        @if ($product->featured_image_url)
            @php
                $thumbUrl = $product->media->first()?->thumb_url ?? $product->featured_image_url;
            @endphp
            <img
                src="{{ $thumbUrl }}"
                srcset="{{ $thumbUrl }} 400w, {{ $product->featured_image_url }} 1200w"
                sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"
                alt="{{ $product->title }}"
                loading="lazy"
                class="h-full w-full object-contain transition duration-500 group-hover:scale-[1.03]"
            >
        @endif
    </div>

    <div class="flex flex-1 flex-col border-t border-[var(--color-border)] p-4">
        @php
            $categoryLine = $product->categories->map(fn ($c) => $c->filterDisplayName())->unique()->join(' · ');
        @endphp
        <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--color-text-muted)]">{{ $categoryLine !== '' ? $categoryLine : 'Bella Criativa' }}</p>
        <h3 class="mt-2 flex-1 text-sm leading-snug text-[var(--color-text)]">{{ $product->title }}</h3>
        @if (!empty($product->available_colors))
            <div class="mt-3 flex items-center gap-2" aria-label="Cores disponíveis">
                @foreach (array_slice($product->available_colors, 0, 5) as $color)
                    @php($isHex = is_string($color) && preg_match('/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color))
                    @php($normalized = $isHex ? strtoupper($color) : '')
                    @php($colorName = $isHex ? (config('catalog.color_names')[$normalized] ?? 'Cor personalizada') : 'Cor')
                    <span
                        class="h-4 w-4 rounded-full border border-[var(--color-border)]"
                        style="{{ $isHex ? 'background-color: '.$color.';' : 'background-color: transparent;' }}"
                        title="{{ $colorName }}"
                    ></span>
                @endforeach
                @if (count($product->available_colors) > 5)
                    <span class="text-[11px] uppercase tracking-[0.12em] text-[var(--color-text-secondary)]">+{{ count($product->available_colors) - 5 }}</span>
                @endif
            </div>
        @endif
        <span class="mt-3 text-[11px] uppercase tracking-[0.18em] text-[var(--color-accent)]">Ver produto →</span>
    </div>
</a>
