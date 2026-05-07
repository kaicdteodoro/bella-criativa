<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateMediaThumbsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_missing_files_as_failures(): void
    {
        Storage::fake('public');

        $product = Product::query()->create([
            'sku' => 'SKU-1601',
            'title' => 'Produto',
            'slug' => 'produto-1601',
            'status' => 'published',
        ]);

        ProductMedia::query()->create([
            'product_id' => $product->id,
            'file' => 'media/SKU-1601/SKU-1601-01.webp',
            'checksum' => 'checksum-1601',
            'order' => 0,
        ]);

        $this->artisan('media:generate-thumbs')
            ->expectsOutputToContain('Encontrados 1 registros sem thumbnail.')
            ->expectsOutputToContain('[SKIP] arquivo não encontrado')
            ->expectsOutputToContain('Concluído — 0 gerados, 1 falhas.')
            ->assertFailed();
    }

    public function test_it_runs_in_dry_run_mode_without_updating_records(): void
    {
        Storage::fake('public');

        $product = Product::query()->create([
            'sku' => 'SKU-1602',
            'title' => 'Produto',
            'slug' => 'produto-1602',
            'status' => 'published',
        ]);

        Storage::disk('public')->put('media/SKU-1602/SKU-1602-01.webp', 'binary');

        $media = ProductMedia::query()->create([
            'product_id' => $product->id,
            'file' => 'media/SKU-1602/SKU-1602-01.webp',
            'checksum' => 'checksum-1602',
            'order' => 0,
        ]);

        $this->artisan('media:generate-thumbs', ['--dry-run' => true])
            ->expectsOutputToContain('[DRY] media/SKU-1602/SKU-1602-01.webp')
            ->expectsOutputToContain('Concluído — 1 gerados, 0 falhas.')
            ->assertSuccessful();

        $this->assertNull($media->fresh()->thumb_file);
        Storage::disk('public')->assertMissing('media/SKU-1602/SKU-1602-01_sm.webp');
    }
}
