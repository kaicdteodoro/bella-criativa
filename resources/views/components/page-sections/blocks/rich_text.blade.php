<section class="grid gap-8 border-t border-[var(--color-border)] pt-8 lg:grid-cols-[0.55fr_1fr]">
    <div>
        @if ($section->heading)
            <h2 class="text-2xl leading-tight tracking-[0.01em] lg:text-3xl">{{ $section->heading }}</h2>
        @endif
    </div>

    <div class="prose max-w-none prose-p:text-[var(--color-text-secondary)] prose-p:leading-8 prose-p:tracking-[0.005em]">
        {!! \App\Support\HtmlSanitizer::clean($section->content['text'] ?? '') !!}
    </div>
</section>
