<?php

namespace App\Console\Commands;

use App\Services\Import\ImportAction;
use App\Services\Import\ImportResult;
use App\Services\Import\SyncApiRunner;
use Illuminate\Console\Command;
use Throwable;

class SyncCuratedCatalog extends Command
{
    protected $signature = 'catalog:sync-curated
        {--source=xbz : Fornecedor (xbz, asia)}
        {--dry-run : Valida sem escrever no banco}
        {--resume : Pula SKUs que já existem no banco}';

    protected $description = 'Importa o catálogo curado da Bella por categoria, respeitando cotas definidas em config/catalog.php.';

    public function handle(SyncApiRunner $runner): int
    {
        $plan    = (array) config('catalog.curated_plan', []);
        $source  = (string) ($this->option('source') ?? 'xbz');
        $dryRun  = (bool) $this->option('dry-run');
        $resume  = (bool) $this->option('resume');

        if ($plan === []) {
            $this->error('Nenhuma categoria definida em config/catalog.curated_plan.');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhuma alteração será salva.');
        }

        $totals = ['created' => 0, 'updated' => 0, 'failed' => 0];

        foreach ($plan as $categoria => $quota) {
            $this->newLine();
            $this->line("▸ <fg=cyan>{$categoria}</> (cota: {$quota})");

            try {
                $batch = $runner->run($source, [
                    'dry_run'       => $dryRun,
                    'resume'        => $resume,
                    'max_published' => $quota,
                    'search_terms'  => $this->presetsFor($categoria),
                    'initiated_via' => 'command',
                    'record_history'=> false,
                    'on_result'     => function (ImportResult $result): void {
                        $icon = match ($result->action) {
                            ImportAction::Failed  => '<fg=red>✗</>',
                            ImportAction::Skipped => '<fg=yellow>–</>',
                            ImportAction::DryRun  => '<fg=gray>~</>',
                            default               => '<fg=green>✓</>',
                        };
                        $this->line("  {$icon} [{$result->sku}] {$result->action->value}");
                    },
                ]);
            } catch (Throwable $e) {
                $this->error("  Falha na categoria {$categoria}: {$e->getMessage()}");
                continue;
            }

            $t = $batch->totals();
            $totals['created'] += $t['created'];
            $totals['updated'] += $t['updated'];
            $totals['failed']  += $t['failed'];

            $this->line("  → criados: {$t['created']} | atualizados: {$t['updated']} | falhas: {$t['failed']}");
        }

        $this->newLine();
        $this->info('Curadoria concluída');
        $this->line("  criados:  {$totals['created']}");
        $this->line("  atualizados: {$totals['updated']}");
        $this->line("  falhas:   {$totals['failed']}");

        if (! $dryRun) {
            $published = \App\Models\Product::query()->where('status', 'published')->count();
            $draft     = \App\Models\Product::query()->where('status', 'draft')->count();
            $this->newLine();
            $this->info("Catálogo publicado: {$published} produto(s)");
            $this->warn("Em revisão manual:  {$draft} produto(s)");
        }

        return self::SUCCESS;
    }

    /** @return string[] */
    private function presetsFor(string $categoria): array
    {
        $presets = (array) config('catalog.api_sync.category_search_presets', []);

        $terms = $presets[$categoria] ?? [$categoria];

        return collect($terms)
            ->map(fn (mixed $t): string => trim(mb_strtolower((string) $t)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
