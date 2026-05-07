<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\ImportAction;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportResult;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ImportBatchResultTest extends TestCase
{
    public function test_it_aggregates_counts_and_totals(): void
    {
        $batch = new ImportBatchResult(5, new Collection([
            new ImportResult('SKU-1', ImportAction::Created, 2),
            new ImportResult('SKU-2', ImportAction::Updated, 1),
            new ImportResult('SKU-3', ImportAction::Skipped, 0, reason: 'Existente'),
            new ImportResult('SKU-4', ImportAction::Failed, 0, reason: 'ZIP inválido'),
            new ImportResult('SKU-5', ImportAction::DryRun, 3),
        ]));

        $this->assertSame(1, $batch->createdCount());
        $this->assertSame(1, $batch->updatedCount());
        $this->assertSame(1, $batch->skippedCount());
        $this->assertSame(1, $batch->failedCount());
        $this->assertSame(1, $batch->dryRunCount());
        $this->assertSame([
            'total' => 5,
            'created' => 1,
            'updated' => 1,
            'skipped' => 1,
            'failed' => 1,
            'dry_run' => 1,
        ], $batch->totals());
    }

    public function test_it_marks_status_as_failed_only_when_all_results_failed(): void
    {
        $failedBatch = new ImportBatchResult(2, new Collection([
            new ImportResult('SKU-1', ImportAction::Failed, 0, reason: 'Erro 1'),
            new ImportResult('SKU-2', ImportAction::Failed, 0, reason: 'Erro 2'),
        ]));

        $mixedBatch = new ImportBatchResult(2, new Collection([
            new ImportResult('SKU-1', ImportAction::Created, 1),
            new ImportResult('SKU-2', ImportAction::Failed, 0, reason: 'Erro 2'),
        ]));

        $completedBatch = new ImportBatchResult(1, new Collection([
            new ImportResult('SKU-1', ImportAction::Created, 1),
        ]));

        $this->assertSame('failed', $failedBatch->status());
        $this->assertSame('completed_with_errors', $mixedBatch->status());
        $this->assertSame('completed', $completedBatch->status());
    }

    public function test_it_serializes_results_array(): void
    {
        $batch = new ImportBatchResult(1, new Collection([
            new ImportResult('SKU-1', ImportAction::Updated, 4, ['curadoria aplicada'], 'ok'),
        ]));

        $this->assertSame([
            [
                'sku' => 'SKU-1',
                'action' => 'updated',
                'images_processed' => 4,
                'warnings' => ['curadoria aplicada'],
                'reason' => 'ok',
            ],
        ], $batch->toResultsArray());
    }
}
