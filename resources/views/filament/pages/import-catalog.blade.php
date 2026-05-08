<x-filament-panels::page>
    <section class="mb-6 grid gap-4 xl:grid-cols-3">
        <article class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-950 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-100">
            <h3 class="text-base font-semibold">Teste seguro</h3>
            <p class="mt-2">Preenche um dry-run pequeno para validar credenciais e resposta da XBZ sem gravar produto.</p>
            <div class="mt-4">
                <x-filament::button size="sm" color="success" wire:click="fillSafeTest">
                    Preencher teste 10 canetas
                </x-filament::button>
            </div>
        </article>

        <article class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
            <h3 class="text-base font-semibold">Carga inicial sugerida</h3>
            <p class="mt-2">Preenche uma bateria com várias categorias. O limite será aplicado para cada categoria selecionada.</p>
            <div class="mt-4">
                <x-filament::button size="sm" color="warning" wire:click="fillStarterBatch">
                    Preencher bateria inicial
                </x-filament::button>
            </div>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5 text-sm text-gray-700 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Resumo do que vai rodar</h3>
            <dl class="mt-3 space-y-2">
                <div class="flex items-center justify-between gap-4">
                    <dt>Fornecedor</dt>
                    <dd class="font-medium">{{ $apiSyncData['source'] ?? 'xbz' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt>Categorias selecionadas</dt>
                    <dd class="font-medium">{{ count($this->selectedApiCategories()) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt>Limite por categoria</dt>
                    <dd class="font-medium">{{ (int) ($apiSyncData['limit'] ?? 0) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt>Estimativa total</dt>
                    <dd class="font-medium">{{ $this->estimatedApiItems() }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt>Modo</dt>
                    <dd class="font-medium">{{ ($apiSyncData['dry_run'] ?? false) ? 'dry-run' : 'real' }}</dd>
                </div>
            </dl>
        </article>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        <div class="space-y-6">
            <form wire:submit="submitApiSync" class="space-y-6">
                {{ $this->apiSyncForm }}

                <div class="rounded-xl border border-dashed border-gray-300 bg-white/70 p-4 text-sm text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                    Sugestão para carga inicial: rode lotes de 50 por categoria com XBZ em dry-run primeiro, depois rode real.
                </div>

                @if ($this->selectedApiCategories() !== [])
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Categorias desta execução</h4>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($this->selectedApiCategories() as $category)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                    {{ str($category)->replace('-', ' ')->title() }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3">
                    <x-filament::button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitApiSync">Executar sync da API</span>
                        <span wire:loading wire:target="submitApiSync">Sincronizando...</span>
                    </x-filament::button>
                </div>
            </form>

            <form wire:submit="submitImport" class="space-y-6">
                {{ $this->importForm }}

                <div class="flex items-center justify-end gap-3">
                    <x-filament::button type="submit" color="gray" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitImport">Executar importação por planilha</span>
                        <span wire:loading wire:target="submitImport">Importando...</span>
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Histórico recente</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">As 5 últimas execuções para consulta rápida.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <x-filament::button size="sm" color="gray" wire:click="clearRunningImportHistory">
                        Limpar travadas
                    </x-filament::button>
                    <x-filament::button size="sm" color="danger" wire:click="clearImportHistory">
                        Limpar histórico
                    </x-filament::button>
                </div>
            </div>

            @forelse ($this->recentRuns as $run)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                    @php($successfulCount = max(($run->total_rows ?? 0) - ($run->failed_count ?? 0), 0))
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

                    <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="font-medium text-gray-950 dark:text-white">Leitura rápida:</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $successfulCount }} item(ns) sem falha</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $run->dry_run ? 'dry-run: nada foi gravado' : 'execução real: itens válidos foram gravados' }}</span>
                        </div>
                    </div>

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
                                <details class="mt-2">
                                    <summary class="cursor-pointer font-medium">Ver falhas desta execução ({{ count($run->failed_items) }})</summary>
                                    <ul class="mt-3 space-y-2">
                                        @foreach (array_slice($run->failed_items, 0, 8) as $item)
                                            <li class="rounded-md bg-white/70 px-3 py-2 dark:bg-black/10">
                                                <strong>{{ $item['sku'] }}</strong><br>
                                                <span>{{ $item['reason'] ?? 'Falha sem detalhe.' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
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
                    @php($successfulCount = max(($run->total_rows ?? 0) - ($run->failed_count ?? 0), 0))
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

                    <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span class="font-medium text-gray-950 dark:text-white">Leitura rápida:</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $successfulCount }} item(ns) sem falha</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $run->dry_run ? 'dry-run: nada foi gravado' : 'execução real: itens válidos foram gravados' }}</span>
                        </div>
                    </div>

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
                                <ul class="mt-2 space-y-2">
                                    @foreach ($run->failed_items as $item)
                                        <li class="rounded-md bg-white/70 px-3 py-2 dark:bg-black/10"><strong>{{ $item['sku'] }}</strong><br>{{ $item['reason'] ?? 'Falha sem detalhe.' }}</li>
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
