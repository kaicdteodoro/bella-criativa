<?php

namespace App\Console\Commands;

use App\Services\Import\ImportAction;
use App\Services\Import\SyncApiRunner;
use Illuminate\Console\Command;
use Throwable;

class SeedCatalogSample extends Command
{
    protected $signature = 'catalog:seed-sample
                            {--source=xbz : Fornecedor: xbz, asia ou all}
                            {--per-category=5 : Produtos novos a importar por categoria}
                            {--dry-run : Simula sem escrever no banco}';

    protected $description = 'Importa uma amostra de produtos por categoria principal para popular o catálogo';

    private const CATEGORIES = [
        'canetas'        => ['caneta'],
        'cadernos'       => ['caderno'],
        'canecas'        => ['caneca', 'copo'],
        'garrafas'       => ['garrafa', 'squeeze'],
        'mochilas'       => ['mochila'],
        'ecobags'        => ['sacola', 'ecobag'],
        'bolsas-termicas'=> ['bolsa termica', 'bolsa térmica'],
        'kits-vinho'     => ['kit vinho', 'vinho'],
        'kits-churrasco' => ['kit churrasco', 'churrasco'],
        'chaveiros'      => ['chaveiro'],
        'agendas'        => ['agenda'],
        'fones-de-ouvido'=> ['fone de ouvido', 'headphone', 'earbuds'],
        'power-bank'     => ['power bank', 'powerbank'],
        'almofadas'      => ['almofada'],
        'bones'          => ['bone', 'boné'],
    ];

    public function handle(SyncApiRunner $runner): int
    {
        $source = (string) ($this->option('source') ?: 'xbz');
        $perCategory = max(1, (int) ($this->option('per-category') ?: 5));
        $dry = (bool) $this->option('dry-run');

        $totalCreated = 0;
        $totalUpdated = 0;
        $totalFailed  = 0;

        foreach (self::CATEGORIES as $label => $terms) {
            $this->line("  → <comment>{$label}</comment>");

            try {
                $created = 0;
                $updated = 0;
                $failed  = 0;

                $runner->run($source, [
                    'dry_run'        => $dry,
                    'search_terms'   => $terms,
                    'max_published'  => $perCategory,
                    'record_history' => false,
                    'on_result'      => function ($result) use (&$created, &$updated, &$failed): void {
                        match ($result->action) {
                            ImportAction::Created => $created++,
                            ImportAction::Updated => $updated++,
                            ImportAction::Failed  => $failed++,
                            default               => null,
                        };
                    },
                ]);

                $this->line("     criados: {$created}  atualizados: {$updated}  falhas: {$failed}");

                $totalCreated += $created;
                $totalUpdated += $updated;
                $totalFailed  += $failed;

            } catch (Throwable $e) {
                $this->error("     erro: {$e->getMessage()}");
                $totalFailed++;
            }
        }

        $this->newLine();
        $this->info("Concluído — criados: {$totalCreated}  atualizados: {$totalUpdated}  falhas: {$totalFailed}");

        if ($dry) {
            $this->warn('Modo dry-run — nenhuma alteração salva.');
        }

        return self::SUCCESS;
    }
}
