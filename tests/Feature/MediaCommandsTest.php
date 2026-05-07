<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_audit_exports_csv(): void
    {
        Storage::fake('public');

        $product = Product::query()->create([
            'sku' => 'SKU-1001',
            'title' => 'Copo Térmico',
            'slug' => 'copo-termico',
            'status' => 'published',
            'featured_image' => 'media/SKU-1001/SKU-1001-01.webp',
        ]);

        ProductMedia::query()->create([
            'product_id' => $product->id,
            'file' => 'media/SKU-1001/SKU-1001-01.webp',
            'thumb_file' => 'media/SKU-1001/SKU-1001-01_sm.webp',
            'checksum' => 'checksum-1001',
            'color_hex' => '#000000',
            'order' => 0,
        ]);

        Storage::disk('public')->put('media/SKU-1001/SKU-1001-01.webp', str_repeat('a', 2048));
        Storage::disk('public')->put('media/SKU-1001/SKU-1001-01_sm.webp', str_repeat('a', 1024));
        Storage::disk('public')->put('media/SKU-1001/SKU-1001-og.webp', str_repeat('a', 1024));

        $csvPath = tempnam(sys_get_temp_dir(), 'media_audit_');

        $this->artisan('media:audit', ['--csv' => $csvPath])
            ->expectsOutputToContain('Produtos: 1')
            ->expectsOutputToContain('CSV exportado:')
            ->assertSuccessful();

        $this->assertStringContainsString('SKU', (string) file_get_contents($csvPath));
        $this->assertStringContainsString('SKU-1001', (string) file_get_contents($csvPath));

        @unlink($csvPath);
    }

    public function test_prune_orphans_dry_run_reports_orphan_directories_without_deleting(): void
    {
        Storage::fake('public');

        Product::query()->create([
            'sku' => 'SKU-1002',
            'title' => 'Mochila',
            'slug' => 'mochila',
            'status' => 'published',
            'featured_image' => 'media/SKU-1002/SKU-1002-01.webp',
        ]);

        Storage::disk('public')->put('media/SKU-1002/SKU-1002-01.webp', 'used');
        Storage::disk('public')->put('media/ORPHAN-001/file.webp', 'orphan');

        $this->artisan('media:prune-orphans', ['--dry-run' => true])
            ->expectsOutputToContain('[DRY-RUN] Encontradas 1 pastas órfãs')
            ->expectsOutputToContain('ORPHAN-001')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('media/ORPHAN-001/file.webp');
    }
}
