<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Import\GalleryImage;
use App\Services\Import\ImportAction;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportResult;
use App\Services\Import\MediaData;
use App\Services\Import\ProductAiBatchCurator;
use App\Services\Import\ProductRow;
use App\Services\Import\ProductUpsert;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class EnrichCatalog extends Command
{
    protected $signature = 'catalog:enrich
        {--source= : Fornecedor a processar: xbz, asia ou all (padrão em config catalog.suppliers.default)}
        {--limit= : Processa só os primeiros N produtos}
        {--dry-run : Simula enriquecimento sem gravar}
        {--force : Reprocessa também produtos já enriquecidos}';

    protected $description = 'Enriquece produtos já importados com curadoria de IA.';

    public function handle(
        ProductAiBatchCurator $aiBatchCurator,
        ProductUpsert $upsert,
    ): int {
        $sourceOption = $this->option('source');
        $source = is_string($sourceOption) && $sourceOption !== ''
            ? $sourceOption
            : (string) config('catalog.suppliers.default', 'xbz');

        if (! in_array($source, ['xbz', 'asia', 'all'], true)) {
            $this->error("Fornecedor inválido: {$source}. Use: xbz, asia ou all.");

            return self::FAILURE;
        }

        $query = Product::query()
            ->with(['media:id,product_id,file,checksum,order', 'categories:id,name'])
            ->orderBy('id');

        if ($source !== 'all') {
            $query->where('source_supplier', $source);
        }

        if (! (bool) $this->option('force')) {
            $query->whereNull('enriched_at');
        }

        $limit = $this->option('limit');
        if ($limit !== null) {
            $query->limit(max(0, (int) $limit));
        }

        /** @var Collection<int, Product> $products */
        $products = $query->get();

        if ($products->isEmpty()) {
            $this->warn('Nenhum produto pendente para enriquecimento com os filtros atuais.');

            return self::SUCCESS;
        }

        $this->line("Processando {$products->count()} produto(s) para enriquecimento...");

        $results = collect();
        $dryRun = (bool) $this->option('dry-run');
        $termMap = (array) config('catalog.term_map', []);
        $batchSize = max(1, (int) config('catalog.ai_curation.batch_size', 20));

        foreach ($products->chunk($batchSize) as $chunk) {
            $rows = $chunk->map(fn (Product $product): ProductRow => $this->toProductRow($product))->values();
            $aiSuggestions = $aiBatchCurator->curate($rows);

            foreach ($chunk as $product) {
                $media = $this->toMediaData($product);
                $aiContent = $aiSuggestions[$product->sku] ?? null;

                if ($dryRun) {
                    $result = new ImportResult(
                        sku: $product->sku,
                        action: ImportAction::DryRun,
                        imagesProcessed: count($media->gallery),
                    );
                    $results->push($result);
                    $this->info("[{$result->sku}] dry_run ({$result->imagesProcessed} imagem(ns))");
                    continue;
                }

                try {
                    $result = $upsert->upsert(
                        row: $this->toProductRow($product),
                        media: $media,
                        termMap: $termMap,
                        source: $product->source_supplier,
                        aiContent: $aiContent,
                    );
                    $results->push($result);
                    $suffix = $aiContent ? '' : ' [fallback local]';
                    $this->info("[{$result->sku}] {$result->action->value}{$suffix} ({$result->imagesProcessed} imagem(ns))");
                } catch (Throwable $exception) {
                    $result = new ImportResult(
                        sku: $product->sku,
                        action: ImportAction::Failed,
                        imagesProcessed: count($media->gallery),
                        reason: $exception->getMessage(),
                    );
                    $results->push($result);
                    $this->error("[{$result->sku}] falhou: {$result->reason}");
                }
            }
        }

        $this->renderSummary(new ImportBatchResult($products->count(), $results));

        return self::SUCCESS;
    }

    private function toProductRow(Product $product): ProductRow
    {
        return new ProductRow(
            sku: $product->sku,
            title: $product->title,
            supplierCode: $product->supplier_code,
            category: $product->categories->first()?->name,
            shortDescription: $product->short_description,
            technicalDescription: $product->technical_description,
            imagesZipUrl: '',
        );
    }

    private function toMediaData(Product $product): MediaData
    {
        $gallery = $product->media->map(fn ($media): GalleryImage => new GalleryImage(
            file: $media->file,
            checksum: $media->checksum,
        ))->values()->all();

        $featured = $product->featured_image ?: ($gallery[0]->file ?? '');
        $ogImage = $product->og_image ?: $featured;

        return new MediaData(
            featured: $featured,
            ogImage: $ogImage,
            gallery: $gallery,
            availableColors: is_array($product->available_colors) ? $product->available_colors : [],
            materials: is_array($product->materials) ? $product->materials : [],
        );
    }

    private function renderSummary(ImportBatchResult $batch): void
    {
        $totals = $batch->totals();

        $this->newLine();
        $this->line('Enriquecimento concluído');
        $this->line('  total:       '.$totals['total']);
        $this->line('  criados:     '.$totals['created']);
        $this->line('  atualizados: '.$totals['updated']);
        $this->line('  pulados:     '.$totals['skipped']);
        $this->line('  dry-run:     '.$totals['dry_run']);
        $this->line('  falhas:      '.$totals['failed']);
    }
}
