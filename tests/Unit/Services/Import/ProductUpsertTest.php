<?php

namespace Tests\Unit\Services\Import;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Services\Import\GalleryImage;
use App\Services\Import\ImportAction;
use App\Services\Import\MediaData;
use App\Services\Import\ProductRow;
use App\Services\Import\ProductUpsert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_product_and_related_records(): void
    {
        $service = new ProductUpsert();

        $result = $service->upsert(
            row: new ProductRow(
                sku: 'SKU-001',
                title: 'Kit Churrasco Prime',
                supplierCode: 'FORN-001',
                category: 'Kits Churrasco',
                shortDescription: 'Kit promocional completo para campanhas corporativas, eventos e relacionamento com clientes.',
                technicalDescription: '<p>Descrição técnica</p>',
                imagesZipUrl: 'https://example.com/kit.zip',
            ),
            media: new MediaData(
                featured: 'media/sku-001/sku-001-01.webp',
                ogImage: 'media/sku-001/sku-001-og.webp',
                gallery: [
                    new GalleryImage('media/sku-001/sku-001-01.webp', 'checksum-1'),
                    new GalleryImage('media/sku-001/sku-001-02.webp', 'checksum-2'),
                ],
                availableColors: ['#000000'],
                materials: ['metal'],
            ),
            termMap: ['Kits Churrasco' => 'bbq-kits'],
            source: 'xbzbrindes',
        );

        $this->assertSame(ImportAction::Created, $result->action);

        $product = Product::query()->where('sku', 'SKU-001')->firstOrFail();

        $this->assertSame('Kit Churrasco Prime', $product->title);
        $this->assertSame('published', $product->status);
        $this->assertSame('ready', $product->curation_status);
        $this->assertSame('xbzbrindes', $product->source_supplier);
        $this->assertSame(['#000000'], $product->available_colors);
        $this->assertSame(['metal'], $product->materials);
        $this->assertSame(100, $product->quality_score);
        $this->assertSame(['Curadoria automatica concluida sem pendencias.'], $product->quality_notes);
        $this->assertCount(1, $product->categories);
        $this->assertSame('bbq-kits', $product->categories->first()->slug);
        $this->assertSame(2, ProductMedia::query()->where('product_id', $product->id)->count());
    }

    public function test_it_updates_existing_product_and_deduplicates_media(): void
    {
        $product = Product::query()->create([
            'sku' => 'SKU-001',
            'title' => 'Antigo',
            'slug' => 'antigo-sku-001',
            'status' => 'published',
        ]);

        $oldCategory = Category::query()->create([
            'name' => 'Antiga',
            'slug' => 'antiga',
        ]);

        $product->categories()->sync([$oldCategory->id]);

        ProductMedia::query()->create([
            'product_id' => $product->id,
            'file' => 'media/sku-001/sku-001-01.webp',
            'checksum' => 'checksum-1',
            'order' => 0,
        ]);

        $service = new ProductUpsert();

        $result = $service->upsert(
            row: new ProductRow(
                sku: 'SKU-001',
                title: 'Kit Churrasco Prime',
                supplierCode: 'FORN-001',
                category: null,
                shortDescription: null,
                technicalDescription: null,
                imagesZipUrl: 'https://example.com/kit.zip',
            ),
            media: new MediaData(
                featured: 'media/sku-001/sku-001-01.webp',
                ogImage: 'media/sku-001/sku-001-og.webp',
                gallery: [
                    new GalleryImage('media/sku-001/sku-001-01.webp', 'checksum-1'),
                    new GalleryImage('media/sku-001/sku-001-02.webp', 'checksum-2'),
                ],
            ),
            termMap: [],
            source: 'novo-fornecedor',
        );

        $this->assertSame(ImportAction::Updated, $result->action);

        $product->refresh();

        $this->assertSame('Kit Churrasco Prime', $product->title);
        $this->assertSame('draft', $product->status);
        $this->assertSame('blocked', $product->curation_status);
        $this->assertSame('novo-fornecedor', $product->source_supplier);
        $this->assertCount(0, $product->categories);
        $this->assertNotNull($product->quality_score);
        $this->assertContains('Categoria ausente.', $product->quality_notes);
        $this->assertSame(2, ProductMedia::query()->where('product_id', $product->id)->count());
    }

    public function test_it_maps_escrita_synonym_to_canetas_slug(): void
    {
        $service = new ProductUpsert();

        $service->upsert(
            row: new ProductRow(
                sku: 'SKU-PEN',
                title: 'Caneta metal',
                supplierCode: 'FORN-PEN',
                category: 'Acessórios de Escrita',
                shortDescription: 'Caneta promocional completa para campanhas corporativas, eventos e relacionamento com clientes.',
                technicalDescription: '<p>Descrição técnica</p>',
                imagesZipUrl: 'https://example.com/pen.zip',
            ),
            media: new MediaData(
                featured: 'media/sku-pen/01.webp',
                ogImage: 'media/sku-pen/og.webp',
                gallery: [
                    new GalleryImage('media/sku-pen/01.webp', 'checksum-pen'),
                ],
                availableColors: ['#000000'],
                materials: ['metal'],
            ),
            termMap: ['Canetas' => 'pens'],
            source: 'xbzbrindes',
        );

        $product = Product::query()->where('sku', 'SKU-PEN')->firstOrFail();

        $this->assertCount(1, $product->categories);
        $this->assertSame('pens', $product->categories->first()->slug);
        $this->assertSame('Canetas', $product->categories->first()->name);
    }
}
