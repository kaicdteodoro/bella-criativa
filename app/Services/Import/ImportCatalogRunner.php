<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportCatalogRunner
{
    public function __construct(
        private readonly SpreadsheetLoader $loader,
        private readonly ZipDownloader $downloader,
        private readonly ImageProcessor $processor,
        private readonly ProductUpsert $upsert,
        private readonly ProductAiBatchCurator $aiBatchCurator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function run(string $path, array $options = []): ImportBatchResult
    {
        $run = $this->startRun($path, $options);

        try {
            if (! is_file($path)) {
                throw new \RuntimeException("Arquivo nao encontrado: {$path}");
            }

            $rows = collect($this->loader->load($path));
            $limit = $options['limit'] ?? null;

            if ($limit !== null) {
                $rows = $rows->take(max(0, (int) $limit));
            }

            ($options['on_start'] ?? null)?->__invoke($rows->count());

            $results = $this->processRows($rows, $options);
            $batch = new ImportBatchResult($rows->count(), $results);

            $this->finishRun($run, $batch, $options);

            return $batch;
        } catch (Throwable $exception) {
            $this->failRun($run, $exception, $path, $options);

            throw $exception;
        }
    }

    /**
     * @param  Collection<int, ProductRow>  $rows
     * @param  array<string, mixed>  $options
     * @return Collection<int, ImportResult>
     */
    private function processRows(Collection $rows, array $options): Collection
    {
        $results = collect();
        $termMap = (array) config('catalog.term_map', []);
        $quality = (int) config('catalog.import.quality', 80);
        $source = $options['source'] ?? null;
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $resume = (bool) ($options['resume'] ?? false);

        $batchSize = max(1, (int) config('catalog.ai_curation.batch_size', 20));

        foreach ($rows->chunk($batchSize) as $chunk) {
            $aiSuggestions = $dryRun ? [] : $this->aiBatchCurator->curate($chunk->values());

            foreach ($chunk as $row) {
                $zipPath = null;

                try {
                    if ($resume && Product::query()->where('sku', $row->sku)->exists()) {
                        $result = new ImportResult(
                            sku: $row->sku,
                            action: ImportAction::Skipped,
                            imagesProcessed: 0,
                            reason: 'SKU já existente.',
                        );
                    } else {
                        $zipPath = $this->downloader->download($row->sku, $row->imagesZipUrl);
                        $media = $this->processor->process($row->sku, $zipPath, $quality);

                        $result = $dryRun
                            ? new ImportResult(
                                sku: $row->sku,
                                action: ImportAction::DryRun,
                                imagesProcessed: count($media->gallery),
                            )
                            : $this->upsert->upsert(
                                row: $row,
                                media: $media,
                                termMap: $termMap,
                                source: $source,
                                aiContent: $aiSuggestions[$row->sku] ?? null,
                            );
                    }
                } catch (Throwable $exception) {
                    $result = new ImportResult(
                        sku: $row->sku,
                        action: ImportAction::Failed,
                        imagesProcessed: 0,
                        reason: $exception->getMessage(),
                    );
                } finally {
                    if (is_string($zipPath) && is_file($zipPath)) {
                        @unlink($zipPath);
                    }
                }

                $results->push($result);
                ($options['on_result'] ?? null)?->__invoke($result);
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function startRun(string $path, array $options): ?ImportRun
    {
        if (($options['record_history'] ?? true) === false) {
            return null;
        }

        return ImportRun::query()->create([
            'user_id' => $options['user_id'] ?? null,
            'source' => $options['source'] ?? null,
            'initiated_via' => $options['initiated_via'] ?? 'admin',
            'file_path' => $options['file_path'] ?? $path,
            'original_filename' => $options['original_filename'] ?? basename($path),
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'resume' => (bool) ($options['resume'] ?? false),
            'limit' => filled($options['limit'] ?? null) ? (int) $options['limit'] : null,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function finishRun(?ImportRun $run, ImportBatchResult $batch, array $options): void
    {
        if (! $run) {
            return;
        }

        $run->forceFill([
            'status' => $batch->status(),
            'total_rows' => $batch->totalRows,
            'created_count' => $batch->createdCount(),
            'updated_count' => $batch->updatedCount(),
            'skipped_count' => $batch->skippedCount(),
            'failed_count' => $batch->failedCount(),
            'summary' => [
                ...$batch->totals(),
                'source' => $options['source'] ?? null,
            ],
            'results' => $batch->toResultsArray(),
            'finished_at' => now(),
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function failRun(?ImportRun $run, Throwable $exception, string $path, array $options): void
    {
        if ($run) {
            $run->forceFill([
                'status' => 'failed',
                'summary' => [
                    'message' => $exception->getMessage(),
                    'source' => $options['source'] ?? null,
                ],
                'finished_at' => now(),
            ])->save();
        }

        Log::error('Catalog import failed.', [
            'path' => $path,
            'message' => $exception->getMessage(),
        ]);
    }
}
