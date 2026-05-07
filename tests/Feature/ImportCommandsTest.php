<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\Import\AiCuratedProductContent;
use App\Services\Import\GalleryImage;
use App\Services\Import\ImportAction;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportResult;
use App\Services\Import\MediaData;
use App\Services\Import\ProductAiBatchCurator;
use App\Services\Import\ProductRow;
use App\Services\Import\ProductUpsert;
use App\Services\Import\SyncApiRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ImportCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_catalog_api_rejects_invalid_source(): void
    {
        $this->artisan('catalog:sync-api', ['--source' => 'invalido'])
            ->expectsOutputToContain('Fornecedor inválido')
            ->assertFailed();
    }

    public function test_sync_catalog_api_reports_summary_from_runner(): void
    {
        $this->app->instance(SyncApiRunner::class, new class extends SyncApiRunner
        {
            public function __construct()
            {
            }

            public function run(string $source, array $options = []): ImportBatchResult
            {
                ($options['on_start'] ?? null)?->__invoke(2);
                ($options['on_result'] ?? null)?->__invoke(new ImportResult('SKU-1301', ImportAction::Created, 2));
                ($options['on_result'] ?? null)?->__invoke(new ImportResult('SKU-1302', ImportAction::Failed, 0, reason: 'Erro remoto'));

                return new ImportBatchResult(2, collect([
                    new ImportResult('SKU-1301', ImportAction::Created, 2),
                    new ImportResult('SKU-1302', ImportAction::Failed, 0, reason: 'Erro remoto'),
                ]));
            }
        });

        $this->artisan('catalog:sync-api', ['--source' => 'xbz', '--categoria' => 'canetas'])
            ->expectsOutputToContain('Filtro contextual ativo')
            ->expectsOutputToContain('Sincronização concluída')
            ->expectsOutputToContain('falhas:')
            ->assertSuccessful();
    }

    public function test_enrich_catalog_rejects_invalid_source(): void
    {
        $this->artisan('catalog:enrich', ['--source' => 'invalido'])
            ->expectsOutputToContain('Fornecedor inválido')
            ->assertFailed();
    }

    public function test_enrich_catalog_handles_dry_run_for_pending_products(): void
    {
        $category = Category::query()->create([
            'name' => 'Canecas',
            'slug' => 'canecas',
        ]);

        $product = Product::query()->create([
            'sku' => 'SKU-1401',
            'title' => 'Caneca Base',
            'slug' => 'caneca-base',
            'status' => 'draft',
            'source_supplier' => 'xbz',
            'featured_image' => 'media/SKU-1401/SKU-1401-01.webp',
            'technical_description' => 'Detalhes iniciais.',
            'short_description' => 'Resumo inicial com conteúdo suficiente.',
        ]);
        $product->categories()->attach($category);
        $product->media()->create([
            'file' => 'media/SKU-1401/SKU-1401-01.webp',
            'checksum' => 'checksum-1401',
            'order' => 0,
        ]);

        $this->app->instance(ProductAiBatchCurator::class, new class extends ProductAiBatchCurator
        {
            public function curate(Collection $rows): array
            {
                return [
                    'SKU-1401' => new AiCuratedProductContent(
                        sku: 'SKU-1401',
                        title: 'Caneca Curada',
                        shortDescription: 'Descrição curada.',
                        technicalDescription: 'Detalhes curados.',
                        category: 'Canecas',
                        confidence: 0.95,
                    ),
                ];
            }
        });

        $this->app->instance(ProductUpsert::class, new class extends ProductUpsert
        {
            public function __construct()
            {
                parent::__construct();
            }
        });

        $this->artisan('catalog:enrich', ['--source' => 'xbz', '--dry-run' => true])
            ->expectsOutputToContain('Processando 1 produto(s)')
            ->expectsOutputToContain('dry_run')
            ->expectsOutputToContain('Enriquecimento concluído')
            ->assertSuccessful();
    }
}
