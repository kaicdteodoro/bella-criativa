<?php

namespace App\Console\Commands;

use App\Services\Import\ImportAction;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportCatalogRunner;
use App\Services\Import\ImportResult;
use Illuminate\Console\Command;
use Throwable;

class ImportCatalog extends Command
{
    protected $signature = 'catalog:import
        {file : Caminho para o arquivo XLSX}
        {--dry-run : Valida sem escrever}
        {--limit= : Processa só os primeiros N produtos}
        {--resume : Pula SKUs que ja existem no banco}
        {--source= : Nome do fornecedor}';

    protected $description = 'Importa produtos a partir de uma planilha XLSX de fornecedor.';

    public function handle(ImportCatalogRunner $runner): int
    {
        $path = $this->resolveFilePath((string) $this->argument('file'));

        if (! is_file($path)) {
            $this->error("Arquivo não encontrado: {$path}");

            return self::FAILURE;
        }

        try {
            $batch = $runner->run($path, [
                'dry_run' => (bool) $this->option('dry-run'),
                'limit' => $this->option('limit'),
                'resume' => (bool) $this->option('resume'),
                'source' => $this->option('source'),
                'initiated_via' => 'command',
                'file_path' => $path,
                'original_filename' => basename($path),
                'on_start' => fn (int $count) => $this->line("Iniciando importação de {$count} produto(s)..."),
                'on_result' => function (ImportResult $result): void {
                    if ($result->action === ImportAction::Failed) {
                        $this->error("[{$result->sku}] falhou: {$result->reason}");

                        return;
                    }

                    if ($result->action === ImportAction::Skipped) {
                        $this->warn("[{$result->sku}] pulado: {$result->reason}");

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

    private function resolveFilePath(string $file): string
    {
        if (str_starts_with($file, DIRECTORY_SEPARATOR)) {
            return $file;
        }

        return base_path($file);
    }

    private function renderSummary(ImportBatchResult $batch): void
    {
        $totals = $batch->totals();

        $this->newLine();
        $this->line('Importação concluída');
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
}
