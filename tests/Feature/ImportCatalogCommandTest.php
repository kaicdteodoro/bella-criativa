<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Import\GalleryImage;
use App\Services\Import\ImageProcessor;
use App\Services\Import\ImportAction;
use App\Services\Import\ImportResult;
use App\Services\Import\MediaData;
use App\Services\Import\ProductRow;
use App\Services\Import\ProductUpsert;
use App\Services\Import\SpreadsheetLoader;
use App\Services\Import\ZipDownloader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use stdClass;
use Tests\TestCase;

class ImportCatalogCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_in_dry_run_mode_without_calling_upsert(): void
    {
        $fixture = tempnam(sys_get_temp_dir(), 'catalog_test_');
        file_put_contents($fixture, 'fixture');

        $state = new stdClass();
        $state->upsertCalled = false;

        $this->app->instance(SpreadsheetLoader::class, new class extends SpreadsheetLoader
        {
            public function load(string $path): array
            {
                return [
                    new ProductRow(
                        sku: 'SKU-001',
                        title: 'Kit Churrasco Prime',
                        supplierCode: 'FORN-001',
                        category: 'Kits Churrasco',
                        shortDescription: 'Descrição curta',
                        technicalDescription: '<p>Descrição técnica</p>',
                        imagesZipUrl: 'https://example.com/kit.zip',
                    ),
                ];
            }
        });

        $this->app->instance(ZipDownloader::class, new class extends ZipDownloader
        {
            public function download(string $sku, string $url): string
            {
                $path = tempnam(sys_get_temp_dir(), 'catalog_zip_');
                file_put_contents($path, 'zip');

                return $path;
            }
        });

        $this->app->instance(ImageProcessor::class, new class extends ImageProcessor
        {
            public function process(string $sku, string $zipPath, int $quality = 80): MediaData
            {
                return new MediaData(
                    featured: 'media/sku-001/sku-001-01.webp',
                    ogImage: 'media/sku-001/sku-001-og.webp',
                    gallery: [new GalleryImage('media/sku-001/sku-001-01.webp', 'checksum-1')],
                );
            }
        });

        $this->app->instance(ProductUpsert::class, new class($state) extends ProductUpsert
        {
            public function __construct(private stdClass $state)
            {
            }

            public function upsert(ProductRow $row, MediaData $media, array $termMap, ?string $source = null): ImportResult
            {
                $this->state->upsertCalled = true;

                return new ImportResult($row->sku, ImportAction::Created, count($media->gallery));
            }
        });

        $this->artisan('catalog:import', [
            'file' => $fixture,
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('dry_run')
            ->expectsOutputToContain('Importação concluída')
            ->assertSuccessful();

        $this->assertFalse($state->upsertCalled);

        @unlink($fixture);
    }

    public function test_it_skips_existing_skus_when_resume_option_is_used(): void
    {
        Product::query()->create([
            'sku' => 'SKU-001',
            'title' => 'Existente',
            'slug' => 'existente-sku-001',
            'status' => 'published',
        ]);

        $fixture = tempnam(sys_get_temp_dir(), 'catalog_test_');
        file_put_contents($fixture, 'fixture');

        $state = new stdClass();
        $state->downloadCalled = false;

        $this->app->instance(SpreadsheetLoader::class, new class extends SpreadsheetLoader
        {
            public function load(string $path): array
            {
                return [
                    new ProductRow(
                        sku: 'SKU-001',
                        title: 'Kit Churrasco Prime',
                        supplierCode: 'FORN-001',
                        category: 'Kits Churrasco',
                        shortDescription: 'Descrição curta',
                        technicalDescription: '<p>Descrição técnica</p>',
                        imagesZipUrl: 'https://example.com/kit.zip',
                    ),
                ];
            }
        });

        $this->app->instance(ZipDownloader::class, new class($state) extends ZipDownloader
        {
            public function __construct(private stdClass $state)
            {
            }

            public function download(string $sku, string $url): string
            {
                $this->state->downloadCalled = true;

                return parent::download($sku, $url);
            }
        });

        $this->artisan('catalog:import', [
            'file' => $fixture,
            '--resume' => true,
        ])
            ->expectsOutputToContain('pulado: SKU já existente')
            ->expectsOutputToContain('pulados:     1')
            ->assertSuccessful();

        $this->assertFalse($state->downloadCalled);

        @unlink($fixture);
    }
}
