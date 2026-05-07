<x-filament-panels::page>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3">
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Executar importação</span>
                    <span wire:loading wire:target="submit">Importando...</span>
                </x-filament::button>
            </div>
        </form>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Histórico recente</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">As 5 últimas execuções para consulta rápida.</p>
            </div>

            @forelse ($this->recentRuns as $run)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $run->original_filename ?? basename($run->file_path) }}</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $run->started_at?->format('d/m/Y H:i') }}
                                @if ($run->user)
                                    | {{ $run->user->name }}
                                @endif
                                | via {{ $run->initiated_via }}
                            </p>
                        </div>

                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $run->status_color === 'success',
                            'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $run->status_color === 'warning',
                            'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' => $run->status_color === 'danger',
                            'bg-gray-100 text-gray-700 dark:bg-gray-500/15 dark:text-gray-300' => $run->status_color === 'gray',
                        ])>
                            {{ $run->status_label }}
                        </span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->total_rows }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Criados</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->created_count }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Atualizados</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->updated_count }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Falhas</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->failed_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                        @if ($run->source)
                            <span>Fornecedor: {{ $run->source }}</span>
                        @endif
                        @if ($run->dry_run)
                            <span>| dry-run</span>
                        @endif
                        @if ($run->resume)
                            <span>| resume</span>
                        @endif
                        @if ($run->limit)
                            <span>| limite {{ $run->limit }}</span>
                        @endif
                    </div>

                    @if (($run->summary['message'] ?? null) || filled($run->failed_items))
                        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
                            @if ($run->summary['message'] ?? null)
                                <p>{{ $run->summary['message'] }}</p>
                            @endif

                            @if (filled($run->failed_items))
                                <ul class="mt-2 space-y-1">
                                    @foreach (array_slice($run->failed_items, 0, 5) as $item)
                                        <li><strong>{{ $item['sku'] }}</strong>: {{ $item['reason'] ?? 'Falha sem detalhe.' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-white/70 p-8 text-sm text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                    Nenhuma importação registrada ainda.
                </div>
            @endforelse
        </div>
    </div>

    <section class="mt-6 space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Histórico completo</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lista paginada de todas as importações com opções para exportar e copiar o resumo operacional.</p>
        </div>

        <div class="space-y-4">
            @foreach ($this->historyRuns as $run)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $run->original_filename ?? basename($run->file_path) }}</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $run->started_at?->format('d/m/Y H:i') }}
                                @if ($run->user)
                                    | {{ $run->user->name }}
                                @endif
                                | via {{ $run->initiated_via }}
                            </p>
                        </div>

                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $run->status_color === 'success',
                            'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $run->status_color === 'warning',
                            'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' => $run->status_color === 'danger',
                            'bg-gray-100 text-gray-700 dark:bg-gray-500/15 dark:text-gray-300' => $run->status_color === 'gray',
                        ])>
                            {{ $run->status_label }}
                        </span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-5">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->total_rows }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Criados</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->created_count }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Atualizados</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->updated_count }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pulados</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->skipped_count }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Falhas</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $run->failed_count }}</dd>
                        </div>
                    </dl>

                    @php($runSummary = $this->formatRunSummary($run))
                    <div class="mt-4 flex flex-wrap items-center gap-2" x-data="{ summary: @js($runSummary) }">
                        <x-filament::button size="sm" color="gray" wire:click="downloadSummary({{ $run->id }})" wire:loading.attr="disabled">
                            Baixar resumo
                        </x-filament::button>
                        <x-filament::button size="sm" color="gray" type="button" x-on:click="navigator.clipboard.writeText(summary)">
                            Copiar resumo
                        </x-filament::button>
                    </div>

                    @if (($run->summary['message'] ?? null) || filled($run->failed_items))
                        <details class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
                            <summary class="cursor-pointer font-medium">Detalhes da execução</summary>
                            @if ($run->summary['message'] ?? null)
                                <p class="mt-2">{{ $run->summary['message'] }}</p>
                            @endif

                            @if (filled($run->failed_items))
                                <ul class="mt-2 space-y-1">
                                    @foreach ($run->failed_items as $item)
                                        <li><strong>{{ $item['sku'] }}</strong>: {{ $item['reason'] ?? 'Falha sem detalhe.' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </details>
                    @endif
                </article>
            @endforeach
        </div>

        <div>
            {{ $this->historyRuns->links() }}
        </div>
    </section>
</x-filament-panels::page>
