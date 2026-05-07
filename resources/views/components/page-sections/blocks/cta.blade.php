<section class="border border-[var(--color-border)] px-8 py-10 lg:px-12 lg:py-14">
    <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
        <div class="space-y-3">
            @if ($section->heading)
                <h2 class="max-w-3xl text-3xl leading-tight">{{ $section->heading }}</h2>
            @endif

            @if (!empty($section->content['text']))
                <p class="max-w-2xl text-[var(--color-text-secondary)]">{{ $section->content['text'] }}</p>
            @endif
        </div>

        @if (!empty($section->content['primary_label']) && !empty($section->content['primary_url']))
            <a href="{{ $section->content['primary_url'] }}" class="pb-btn-outline pb-focus-ring px-6 py-3 text-sm uppercase">
                {{ $section->content['primary_label'] }}
            </a>
        @endif
    </div>
</section>
