<?php

namespace Tests\Feature;

use App\Models\ImportRun;
use App\Services\Import\ApiProductRow;
use App\Services\Import\GalleryImage;
use App\Services\Import\ImportAction;
use App\Services\Import\ImportResult;
use App\Services\Import\MediaData;
use App\Services\Import\ProductUpsert;
use App\Services\Import\SyncApiRunner;
use App\Services\Import\UrlImageProcessor;
use App\Services\Suppliers\AsiaImportClient;
use App\Services\Suppliers\XbzClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use stdClass;
use Tests\TestCase;

class SyncApiRunnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_completed_sync_run_in_dry_run_mode(): void
    {
        $runner = new SyncApiRunner(
            asiaClient: new class extends AsiaImportClient
            {
                public function fetchAll(): array
                {
                    return [];
                }
            },
            xbzClient: new class extends XbzClient
            {
                public function fetchAll(string $busca = ''): array
                {
                    return [
                        new ApiProductRow(
                            sku: 'SKU-1701',
                            title: 'Copo Térmico',
                            supplierCode: 'FORN-1701',
                            category: 'Copos',
                            shortDescription: 'Descrição curta',
                            technicalDescription: null,
                            imageUrls: ['https://cdn.example.com/1.png'],
                        ),
                    ];
                }
            },
            processor: new class extends UrlImageProcessor
            {
                public function process(string $sku, array $imageUrls, int $quality = 80): MediaData
                {
                    return new MediaData(
                        featured: 'media/SKU-1701/SKU-1701-01.webp',
                        ogImage: 'media/SKU-1701/SKU-1701-og.webp',
                        gallery: [new GalleryImage('media/SKU-1701/SKU-1701-01.webp', 'checksum-1701')],
                    );
                }
            },
            upsert: new class extends ProductUpsert
            {
                public function __construct()
                {
                    parent::__construct();
                }
            },
        );

        $batch = $runner->run('xbz', ['dry_run' => true, 'initiated_via' => 'command']);

        $run = ImportRun::query()->firstOrFail();

        $this->assertSame('completed', $batch->status());
        $this->assertSame('completed', $run->status);
        $this->assertSame('api:xbz', $run->file_path);
        $this->assertSame(1, $run->total_rows);
        $this->assertSame('dry_run', $run->results[0]['action']);
    }

    public function test_it_filters_rows_by_search_terms_before_processing(): void
    {
        $state = new stdClass();
        $state->processedSkus = [];

        $runner = new SyncApiRunner(
            asiaClient: new class extends AsiaImportClient
            {
                public function fetchAll(): array
                {
                    return [];
                }
            },
            xbzClient: new class extends XbzClient
            {
                public function fetchAll(string $busca = ''): array
                {
                    return [
                        new ApiProductRow('SKU-1702', 'Caneca Térmica', 'FORN-1702', 'Canecas', 'Desc', null, ['u1']),
                        new ApiProductRow('SKU-1703', 'Mochila Casual', 'FORN-1703', 'Mochilas', 'Desc', null, ['u2']),
                    ];
                }
            },
            processor: new class extends UrlImageProcessor
            {
                public function process(string $sku, array $imageUrls, int $quality = 80): MediaData
                {
                    return new MediaData(
                        featured: "media/{$sku}/{$sku}-01.webp",
                        ogImage: "media/{$sku}/{$sku}-og.webp",
                        gallery: [new GalleryImage("media/{$sku}/{$sku}-01.webp", "checksum-{$sku}")],
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
                    \App\Services\Import\ProductRow $row,
                    MediaData $media,
                    array $termMap,
                    ?string $source = null,
                    ?\App\Services\Import\AiCuratedProductContent $aiContent = null,
                ): ImportResult {
                    $this->state->processedSkus[] = $row->sku;

                    return new ImportResult($row->sku, ImportAction::Created, count($media->gallery));
                }
            },
        );

        $runner->run('xbz', ['search_terms' => ['caneca']]);

        $this->assertSame(['SKU-1702'], $state->processedSkus);
    }
}
