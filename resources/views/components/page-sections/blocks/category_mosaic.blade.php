@php
    $selectedCategories = collect($section->content['categories'] ?? []);
    $items = $categories
        ->when($selectedCategories->isNotEmpty(), fn ($collection) => $collection->whereIn('slug', $selectedCategories))
        ->take(3);
@endphp

@if ($items->isNotEmpty())
    <section class="space-y-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                @if ($section->heading)
                    <h2 class="text-3xl">{{ $section->heading }}</h2>
                @endif

                @if (!empty($section->content['text']))
                    <p class="max-w-2xl text-[var(--color-text-secondary)]">{{ $section->content['text'] }}</p>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($items as $item)
                <a href="{{ route('categories.show', $item->slug) }}" class="pb-card-hover pb-surface-warm group flex min-h-72 flex-col justify-end border border-[var(--color-border)] p-6">
                    <p class="text-xs uppercase tracking-[0.22em] text-[var(--color-text-secondary)]">Categoria</p>
                    <h3 class="mt-3 text-2xl">{{ $item->name }}</h3>
                    @if ($item->description)
                        <p class="mt-3 max-w-xs text-sm leading-6 text-[var(--color-text-secondary)]">{{ $item->description }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
