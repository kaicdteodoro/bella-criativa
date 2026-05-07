<?php

namespace App\Services\Import;

use App\Models\ImportRun;
use App\Services\Suppliers\AsiaImportClient;
use App\Services\Suppliers\XbzClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class SyncApiRunner
{
    public function __construct(
        private readonly AsiaImportClient $asiaClient,
        private readonly XbzClient $xbzClient,
        private readonly UrlImageProcessor $processor,
        private readonly ProductUpsert $upsert,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function run(string $source, array $options = []): ImportBatchResult
    {
        $run = $this->startRun($source, $options);

        try {
            $searchTerms = collect($options['search_terms'] ?? [])
                ->map(fn (mixed $term): string => trim(mb_strtolower((string) $term)))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $apiSearch = $searchTerms[0] ?? '';
            $rows = collect($this->fetchRows($source, $apiSearch));

            if ($searchTerms !== []) {
                $rows = $rows->filter(fn (ApiProductRow $row): bool => $this->matchesSearchTerms($row, $searchTerms))->values();
            }

            $limit = $options['limit'] ?? null;

            if ($limit !== null) {
                $rows = $rows->take(max(0, (int) $limit));
            }

            ($options['on_start'] ?? null)?->__invoke($rows->count());

            $results = $this->processRows($rows, $source, $options);
            $batch = new ImportBatchResult($rows->count(), $results);

            $this->finishRun($run, $batch, $source);

            return $batch;
        } catch (Throwable $exception) {
            $this->failRun($run, $exception, $source);
            throw $exception;
        }
    }

    /**
     * @return ApiProductRow[]
     */
    private function fetchRows(string $source, string $search = ''): array
    {
        return match ($source) {
            'xbz' => $this->xbzClient->fetchAll($search),
            'asia' => $this->asiaClient->fetchAll(),
            'all' => array_merge(
                $this->xbzClient->fetchAll($search),
                $this->asiaClient->fetchAll(),
            ),
            default => throw new InvalidArgumentException("Fornecedor desconhecido: {$source}"),
        };
    }

    /**
     * @param  string[]  $searchTerms
     */
    private function matchesSearchTerms(ApiProductRow $row, array $searchTerms): bool
    {
        $haystack = mb_strtolower(implode(' ', [
            $row->sku,
            $row->title,
            $row->supplierCode ?? '',
            $row->category ?? '',
            $row->shortDescription ?? '',
            $row->technicalDescription ?? '',
        ]));

        foreach ($searchTerms as $search) {
            if (mb_strpos($haystack, $search) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, ApiProductRow>  $rows
     * @param  array<string, mixed>  $options
     * @return Collection<int, ImportResult>
     */
    private function processRows(Collection $rows, string $source, array $options): Collection
    {
        $results = collect();
        $termMap = (array) config('catalog.term_map', []);
        $quality = (int) config('catalog.import.quality', 80);
        $dryRun = (bool) ($options['dry_run'] ?? false);

        foreach ($rows as $row) {
            try {
                $media = $this->processor->process($row->sku, $row->imageUrls, $quality);

                $result = $dryRun
                    ? new ImportResult(
                        sku: $row->sku,
                        action: ImportAction::DryRun,
                        imagesProcessed: count($media->gallery),
                    )
                    : $this->upsert->upsert(
                        $this->toProductRow($row),
                        $media,
                        $termMap,
                        $source,
                        null,
                    );
            } catch (Throwable $exception) {
                $result = new ImportResult(
                    sku: $row->sku,
                    action: ImportAction::Failed,
                    imagesProcessed: 0,
                    reason: $exception->getMessage(),
                );
            }

            $results->push($result);
            ($options['on_result'] ?? null)?->__invoke($result);
        }

        return $results;
    }

    private function toProductRow(ApiProductRow $row): ProductRow
    {
        return new ProductRow(
            sku: $row->sku,
            title: $row->title,
            supplierCode: $row->supplierCode,
            category: $row->category,
            shortDescription: $row->shortDescription,
            technicalDescription: $row->technicalDescription,
            imagesZipUrl: '',
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function startRun(string $source, array $options): ?ImportRun
    {
        if (($options['record_history'] ?? true) === false) {
            return null;
        }

        return ImportRun::query()->create([
            'user_id' => null,
            'source' => $source,
            'initiated_via' => $options['initiated_via'] ?? 'scheduler',
            'file_path' => "api:{$source}",
            'original_filename' => "api:{$source}",
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'resume' => false,
            'limit' => filled($options['limit'] ?? null) ? (int) $options['limit'] : null,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    private function finishRun(?ImportRun $run, ImportBatchResult $batch, string $source): void
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
                'source' => $source,
            ],
            'results' => $batch->toResultsArray(),
            'finished_at' => now(),
        ])->save();
    }

    private function failRun(?ImportRun $run, Throwable $exception, string $source): void
    {
        if ($run) {
            $run->forceFill([
                'status' => 'failed',
                'summary' => [
                    'message' => $exception->getMessage(),
                    'source' => $source,
                ],
                'finished_at' => now(),
            ])->save();
        }

        Log::error('Sync de catálogo via API falhou.', [
            'source' => $source,
            'message' => $exception->getMessage(),
        ]);
    }
}
