<section class="grid gap-10 border border-[var(--color-border)] bg-[var(--color-bg-soft)] px-8 py-10 lg:grid-cols-[1.3fr_0.7fr] lg:px-12 lg:py-14">
    <div class="space-y-5">
        @if (!empty($section->content['eyebrow']))
            <p class="text-xs uppercase tracking-[0.24em] text-[var(--color-text-secondary)]">{{ $section->content['eyebrow'] }}</p>
        @endif

        <h1 class="max-w-4xl text-4xl leading-tight lg:text-6xl">{{ $section->heading }}</h1>

        @if (!empty($section->content['text']))
            <p class="max-w-2xl text-lg leading-8 text-[var(--color-text-secondary)]">{{ $section->content['text'] }}</p>
        @endif
    </div>

    <div class="flex flex-col items-start justify-end gap-4 lg:items-end">
        @if (!empty($section->content['primary_label']) && !empty($section->content['primary_url']))
            <a href="{{ $section->content['primary_url'] }}" class="pb-btn-primary pb-focus-ring px-6 py-3 text-sm uppercase">
                {{ $section->content['primary_label'] }}
            </a>
        @endif

        @if (!empty($section->content['secondary_label']) && !empty($section->content['secondary_url']))
            <a href="{{ $section->content['secondary_url'] }}" class="pb-focus-ring text-sm uppercase tracking-[0.18em] text-[var(--color-text-secondary)]">
                {{ $section->content['secondary_label'] }}
            </a>
        @endif
    </div>
</section>
