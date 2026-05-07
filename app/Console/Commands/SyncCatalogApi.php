<?php

namespace App\Console\Commands;

use App\Services\Import\ImportAction;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportResult;
use App\Services\Import\SyncApiRunner;
use Illuminate\Console\Command;
use Throwable;

class SyncCatalogApi extends Command
{
    protected $signature = 'catalog:sync-api
        {--source= : Fornecedor a sincronizar: xbz, asia ou all (padrão em config catalog.suppliers.default)}
        {--categoria= : Atalho de busca contextual (ex.: canetas, cadernos, garrafas, kits-churrasco)}
        {--busca= : Termo livre de busca para filtrar (aceita múltiplos termos separados por vírgula)}
        {--dry-run : Valida sem escrever no banco}
        {--limit= : Processa só os primeiros N produtos}';

    protected $description = 'Sincroniza produtos via API dos fornecedores (XBZ e/ou Asia Import).';

    public function handle(SyncApiRunner $runner): int
    {
        $sourceOption = $this->option('source');
        $source = is_string($sourceOption) && $sourceOption !== ''
            ? $sourceOption
            : (string) config('catalog.suppliers.default', 'xbz');
        $searchTerms = $this->resolveSearchTerms();
        $validSources = ['xbz', 'asia', 'all'];

        if (! in_array($source, $validSources, true)) {
            $this->error("Fornecedor inválido: {$source}. Use: ".implode(', ', $validSources));

            return self::FAILURE;
        }

        try {
            if ($searchTerms !== []) {
                $this->line('Filtro contextual ativo: ['.implode(', ', $searchTerms).']');
            }

            $batch = $runner->run($source, [
                'dry_run' => (bool) $this->option('dry-run'),
                'limit' => $this->option('limit'),
                'search_terms' => $searchTerms,
                'initiated_via' => 'command',
                'on_start' => fn (int $count) => $this->line(
                    "Sincronizando {$count} produto(s) de [{$source}]..."
                ),
                'on_result' => function (ImportResult $result): void {
                    if ($result->action === ImportAction::Failed) {
                        $this->error("[{$result->sku}] falhou: {$result->reason}");

                        return;
                    }

                    $this->info("[{$result->sku}] {$result->action->value} ({$result->imagesProcessed} imagem(ns))");
                },
            ]);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->renderSummary($batch);

        return self::SUCCESS;
    }

    private function renderSummary(ImportBatchResult $batch): void
    {
        $totals = $batch->totals();

        $this->newLine();
        $this->line('Sincronização concluída');
        $this->line('  total:       '.$totals['total']);
        $this->line('  criados:     '.$totals['created']);
        $this->line('  atualizados: '.$totals['updated']);
        $this->line('  pulados:     '.$totals['skipped']);
        $this->line('  dry-run:     '.$totals['dry_run']);
        $this->line('  falhas:      '.$totals['failed']);

        $failed = $batch->results->where('action', ImportAction::Failed);

        if ($failed->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->line('SKUs com falha:');

        foreach ($failed as $result) {
            $this->line("  {$result->sku}: {$result->reason}");
        }
    }

    /**
     * @return string[]
     */
    private function resolveSearchTerms(): array
    {
        $busca = trim((string) ($this->option('busca') ?? ''));
        if ($busca !== '') {
            return collect(explode(',', $busca))
                ->map(fn (string $term): string => trim(mb_strtolower($term)))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $categoria = trim(mb_strtolower((string) ($this->option('categoria') ?? '')));

        if ($categoria === '') {
            return [];
        }

        $presets = (array) config('catalog.api_sync.category_search_presets', []);
        $terms = $presets[$categoria] ?? [$categoria];

        return collect($terms)
            ->map(fn (mixed $term): string => trim(mb_strtolower((string) $term)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
