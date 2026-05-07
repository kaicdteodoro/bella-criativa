@if ($products->isNotEmpty())
    <section class="space-y-6">
        <div class="space-y-2">
            @if ($section->heading)
                <h2 class="text-3xl">{{ $section->heading }}</h2>
            @endif

            @if (!empty($section->content['text']))
                <p class="max-w-2xl text-[var(--color-text-secondary)]">{{ $section->content['text'] }}</p>
            @endif
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($products->take(4) as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>
@endif
