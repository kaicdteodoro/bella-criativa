<?php

namespace App\Services\Import;

use Illuminate\Support\Collection;

readonly class ImportBatchResult
{
    /**
     * @param  Collection<int, ImportResult>  $results
     */
    public function __construct(
        public int $totalRows,
        public Collection $results,
    ) {
    }

    public function createdCount(): int
    {
        return $this->results->where('action', ImportAction::Created)->count();
    }

    public function updatedCount(): int
    {
        return $this->results->where('action', ImportAction::Updated)->count();
    }

    public function skippedCount(): int
    {
        return $this->results->where('action', ImportAction::Skipped)->count();
    }

    public function failedCount(): int
    {
        return $this->results->where('action', ImportAction::Failed)->count();
    }

    public function dryRunCount(): int
    {
        return $this->results->where('action', ImportAction::DryRun)->count();
    }

    public function status(): string
    {
        if ($this->failedCount() === $this->results->count() && $this->results->isNotEmpty()) {
            return 'failed';
        }

        if ($this->failedCount() > 0) {
            return 'completed_with_errors';
        }

        return 'completed';
    }

    /**
     * @return array<string, int>
     */
    public function totals(): array
    {
        return [
            'total' => $this->totalRows,
            'created' => $this->createdCount(),
            'updated' => $this->updatedCount(),
            'skipped' => $this->skippedCount(),
            'failed' => $this->failedCount(),
            'dry_run' => $this->dryRunCount(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toResultsArray(): array
    {
        return $this->results
            ->map(fn (ImportResult $result): array => [
                'sku' => $result->sku,
                'action' => $result->action->value,
                'images_processed' => $result->imagesProcessed,
                'warnings' => $result->warnings,
                'reason' => $result->reason,
            ])
            ->values()
            ->all();
    }
}
