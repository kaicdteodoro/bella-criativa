<?php

namespace Tests\Unit\Models;

use App\Models\ImportRun;
use Tests\TestCase;

class ImportRunTest extends TestCase
{
    public function test_status_label_maps_known_statuses(): void
    {
        $completed = new ImportRun(['status' => 'completed']);
        $failed = new ImportRun(['status' => 'failed']);
        $custom = new ImportRun(['status' => 'queued_for_review']);

        $this->assertSame('Concluída', $completed->status_label);
        $this->assertSame('Falhou', $failed->status_label);
        $this->assertSame('Queued for review', $custom->status_label);
    }

    public function test_status_color_maps_known_statuses(): void
    {
        $this->assertSame('warning', (new ImportRun(['status' => 'running']))->status_color);
        $this->assertSame('success', (new ImportRun(['status' => 'completed']))->status_color);
        $this->assertSame('warning', (new ImportRun(['status' => 'completed_with_errors']))->status_color);
        $this->assertSame('danger', (new ImportRun(['status' => 'failed']))->status_color);
        $this->assertSame('gray', (new ImportRun(['status' => 'queued']))->status_color);
    }

    public function test_failed_items_accessor_filters_only_failed_results(): void
    {
        $run = new ImportRun([
            'results' => [
                ['sku' => 'SKU-001', 'action' => 'created'],
                ['sku' => 'SKU-002', 'action' => 'failed', 'reason' => 'Imagem ausente'],
                ['sku' => 'SKU-003', 'action' => 'updated'],
                ['sku' => 'SKU-004', 'action' => 'failed', 'reason' => 'CSV inválido'],
            ],
        ]);

        $this->assertSame([
            ['sku' => 'SKU-002', 'action' => 'failed', 'reason' => 'Imagem ausente'],
            ['sku' => 'SKU-004', 'action' => 'failed', 'reason' => 'CSV inválido'],
        ], $run->failed_items);
    }
}
