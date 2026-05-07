<?php

namespace Tests\Unit\Filament;

use App\Filament\Pages\ImportCatalog;
use App\Models\ImportRun;
use ReflectionMethod;
use Tests\TestCase;

class ImportCatalogPageTest extends TestCase
{
    public function test_format_run_summary_includes_totals_and_failed_items(): void
    {
        $page = new ImportCatalog();
        $run = new ImportRun([
            'status' => 'completed_with_errors',
            'source' => 'Jaqmouse',
            'file_path' => 'imports/catalog/test.csv',
            'original_filename' => 'catalogo.csv',
            'dry_run' => true,
            'resume' => true,
            'limit' => 10,
            'initiated_via' => 'admin',
            'total_rows' => 12,
            'created_count' => 7,
            'updated_count' => 2,
            'skipped_count' => 1,
            'failed_count' => 2,
            'summary' => ['message' => 'Execução com alertas.'],
            'results' => [
                ['sku' => 'SKU-001', 'action' => 'failed', 'reason' => 'Imagem ausente'],
                ['sku' => 'SKU-002', 'action' => 'created'],
            ],
        ]);
        $run->setAttribute('id', 42);
        $run->started_at = now()->startOfDay();
        $run->finished_at = now()->startOfDay()->addMinutes(5);

        $summary = $page->formatRunSummary($run);

        $this->assertStringContainsString('Resumo da importação', $summary);
        $this->assertStringContainsString('Execução: #42', $summary);
        $this->assertStringContainsString('Status: Concluída com falhas', $summary);
        $this->assertStringContainsString('Arquivo: catalogo.csv', $summary);
        $this->assertStringContainsString('Modo: dry-run', $summary);
        $this->assertStringContainsString('- Criados: 7', $summary);
        $this->assertStringContainsString('Mensagem: Execução com alertas.', $summary);
        $this->assertStringContainsString('- SKU-001: Imagem ausente', $summary);
    }

    public function test_download_summary_uses_expected_filename_and_content_type(): void
    {
        $page = new ImportCatalog();
        $run = new ImportRun([
            'status' => 'completed',
            'file_path' => 'imports/catalog/test.csv',
            'original_filename' => 'catalogo.csv',
            'initiated_via' => 'admin',
            'total_rows' => 1,
            'created_count' => 1,
            'updated_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
        ]);
        $run->setAttribute('id', 43);
        $run->exists = true;

        $summary = $this->callProtected($page, 'buildRunSummary', [$run]);

        $this->assertStringContainsString('Execução: #43', $summary);
        $this->assertStringContainsString('Arquivo: catalogo.csv', $summary);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private function callProtected(object $target, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($target, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($target, $arguments);
    }
}
