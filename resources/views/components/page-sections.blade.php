@props([
    'page' => null,
    'categories' => collect(),
    'products' => collect(),
])

@if ($page?->sections?->count())
    <div class="space-y-16">
        @foreach ($page->sections->where('is_active', true)->sortBy('position') as $section)
            @php($sectionView = 'components.page-sections.blocks.' . $section->type)

            @if (view()->exists($sectionView))
                @include($sectionView, [
                    'section' => $section,
                    'categories' => $categories,
                    'products' => $products,
                ])
            @endif
        @endforeach
    </div>
@endif
