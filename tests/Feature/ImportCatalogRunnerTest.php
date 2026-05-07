<?php

namespace Tests\Feature;

use App\Models\ImportRun;
use App\Services\Import\AiCuratedProductContent;
use App\Services\Import\GalleryImage;
use App\Services\Import\ImageProcessor;
use App\Services\Import\ImportAction;
use App\Services\Import\ImportCatalogRunner;
use App\Services\Import\ImportResult;
use App\Services\Import\MediaData;
use App\Services\Import\ProductAiBatchCurator;
use App\Services\Import\ProductRow;
use App\Services\Import\ProductUpsert;
use App\Services\Import\SpreadsheetLoader;
use App\Services\Import\ZipDownloader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use RuntimeException;
use stdClass;
use Tests\TestCase;

class ImportCatalogRunnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_completed_run_in_dry_run_mode(): void
    {
        $fixture = tempnam(sys_get_temp_dir(), 'catalog_runner_');
        file_put_contents($fixture, 'fixture');

        $state = new stdClass();
        $state->upsertCalled = false;

        $runner = new ImportCatalogRunner(
            loader: new class extends SpreadsheetLoader
            {
                public function load(string $path): array
                {
                    return [
                        new ProductRow(
                            sku: 'SKU-601',
                            title: 'Copo Térmico',
                            supplierCode: 'FORN-601',
                            category: 'Copos',
                            shortDescription: 'Descrição curta',
                            technicalDescription: 'Descrição técnica',
                            imagesZipUrl: 'https://example.com/sku-601.zip',
                        ),
                    ];
                }
            },
            downloader: new class extends ZipDownloader
            {
                public function download(string $sku, string $url): string
                {
                    $path = tempnam(sys_get_temp_dir(), 'catalog_zip_');
                    file_put_contents($path, 'zip');

                    return $path;
                }
            },
            processor: new class extends ImageProcessor
            {
                public function process(string $sku, string $zipPath, int $quality = 80): MediaData
                {
                    return new MediaData(
                        featured: 'media/sku-601/sku-601-01.webp',
                        ogImage: 'media/sku-601/sku-601-og.webp',
                        gallery: [new GalleryImage('media/sku-601/sku-601-01.webp', 'checksum-601')],
                    );
                }
            },
            upsert: new class($state) extends ProductUpsert
            {
                public function __construct(private stdClass $state)
                {
                    parent::__construct();
                }

                public function upsert(
                    ProductRow $row,
                    MediaData $media,
                    array $termMap,
                    ?string $source = null,
                    ?AiCuratedProductContent $aiContent = null,
                ): ImportResult {
                    $this->state->upsertCalled = true;

                    return new ImportResult($row->sku, ImportAction::Created, count($media->gallery));
                }
            },
            aiBatchCurator: new class extends ProductAiBatchCurator
            {
                public function curate(Collection $rows): array
                {
                    return [];
                }
            },
        );

        $batch = $runner->run($fixture, [
            'dry_run' => true,
            'source' => 'Jaqmouse',
            'initiated_via' => 'admin',
            'original_filename' => 'catalogo.csv',
        ]);

        $run = ImportRun::query()->firstOrFail();

        $this->assertSame('completed', $batch->status());
        $this->assertFalse($state->upsertCalled);
        $this->assertSame('completed', $run->status);
        $this->assertSame('catalogo.csv', $run->original_filename);
        $this->assertSame(1, $run->total_rows);
        $this->assertSame(0, $run->created_count);
        $this->assertSame(1, $run->summary['dry_run']);
        $this->assertSame('dry_run', $run->results[0]['action']);

        @unlink($fixture);
    }

    public function test_it_records_failed_run_when_loader_throws(): void
    {
        $fixture = tempnam(sys_get_temp_dir(), 'catalog_runner_');
        file_put_contents($fixture, 'fixture');

        $runner = new ImportCatalogRunner(
            loader: new class extends SpreadsheetLoader
            {
                public function load(string $path): array
                {
                    throw new RuntimeException('Planilha corrompida.');
                }
            },
            downloader: new class extends ZipDownloader
            {
            },
            processor: new class extends ImageProcessor
            {
            },
            upsert: new class extends ProductUpsert
            {
            },
            aiBatchCurator: new class extends ProductAiBatchCurator
            {
            },
        );

        try {
            $runner->run($fixture, [
                'source' => 'Fornecedor X',
                'initiated_via' => 'admin',
            ]);
            $this->fail('Era esperado que a importação falhasse.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Planilha corrompida.', $exception->getMessage());
        }

        $run = ImportRun::query()->firstOrFail();

        $this->assertSame('failed', $run->status);
        $this->assertSame('Planilha corrompida.', $run->summary['message']);
        $this->assertSame('Fornecedor X', $run->summary['source']);
        $this->assertNotNull($run->finished_at);

        @unlink($fixture);
    }
}
