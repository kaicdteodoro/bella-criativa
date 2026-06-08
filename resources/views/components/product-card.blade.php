@props(['product'])

<a
    href="{{ route('products.show', $product->slug) }}"
    data-catalog-save-return-state="1"
    class="pb-card-hover pb-focus-ring group relative flex h-full overflow-hidden bg-white"
>
    @php
        $thumbUrl = $product->media->first()?->thumb_url ?? $product->featured_image_url;
        $categoryLine = $product->categories->map(fn ($c) => $c->filterDisplayName())->unique()->join(' · ');
    @endphp

    <div class="relative aspect-[4/5] w-full bg-[var(--color-bg-soft)]">
        @if ($thumbUrl)
            <img
                src="{{ $thumbUrl }}"
                srcset="{{ $thumbUrl }} 400w, {{ $product->featured_image_url }} 1200w"
                sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"
                alt="{{ $product->title }}{{ $categoryLine !== '' ? ' | ' . $categoryLine : '' }} — Bella Criativa"
                width="400"
                height="500"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-[1.02]"
            >
        @else
            <div class="h-full w-full bg-[var(--color-bg-soft)]"></div>
        @endif

        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/52 via-black/14 to-transparent"></div>

        <div class="absolute inset-x-0 bottom-0 p-4 text-white">
            <p class="text-[10px] uppercase tracking-[0.2em] text-white/80">
                {{ $categoryLine !== '' ? $categoryLine : 'Bella Criativa' }}
            </p>
            <h3 class="mt-2 text-sm leading-snug text-white/95">
                {{ $product->title }}
            </h3>
            @if (!empty($product->available_colors))
                <div class="mt-3 flex items-center gap-2" aria-label="Cores disponíveis">
                    @foreach (array_slice($product->available_colors, 0, 5) as $color)
                        @php($isHex = is_string($color) && preg_match('/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color))
                        @php($normalized = $isHex ? strtoupper($color) : '')
                        @php($colorName = $isHex ? (config('catalog.color_names')[$normalized] ?? 'Cor personalizada') : 'Cor')
                        <span
                            class="h-4 w-4 rounded-[3px] border border-white/60 shadow-[0_0_0_1px_rgba(0,0,0,0.16)]"
                            style="{{ $isHex ? 'background-color: '.$color.';' : 'background-color: transparent;' }}"
                            title="{{ $colorName }}"
                        ></span>
                    @endforeach
                    @if (count($product->available_colors) > 5)
                        <span class="text-[11px] uppercase tracking-[0.12em] text-white/85">+{{ count($product->available_colors) - 5 }}</span>
                    @endif
                </div>
            @endif
            <span class="mt-3 inline-flex text-[11px] uppercase tracking-[0.18em] text-white/85">Ver produto →</span>
        </div>
    </div>
</a>
