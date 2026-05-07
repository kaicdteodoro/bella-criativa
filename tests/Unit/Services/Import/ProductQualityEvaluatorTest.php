<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\CuratedProductData;
use App\Services\Import\GalleryImage;
use App\Services\Import\MediaData;
use App\Services\Import\ProductQualityEvaluator;
use Tests\TestCase;

class ProductQualityEvaluatorTest extends TestCase
{
    public function test_it_marks_high_quality_products_as_ready_and_published(): void
    {
        $evaluator = new ProductQualityEvaluator();

        $product = new CuratedProductData(
            title: 'Caneca Térmica Executiva',
            slug: 'caneca-termica-executiva-sku-1201',
            category: 'Canecas',
            shortDescription: 'Descrição curta com tamanho suficiente para publicação automática sem bloqueios adicionais.',
            technicalDescription: 'Descrição técnica completa.',
            supplierCode: 'FORN-1201',
            sourceSupplier: 'xbz',
            featuredImage: 'media/SKU-1201/SKU-1201-01.webp',
            ogImage: 'media/SKU-1201/SKU-1201-og.webp',
            availableColors: ['#000000'],
            materials: ['metal'],
            status: 'draft',
            curationStatus: 'processed',
            qualityScore: 0,
            qualityNotes: [],
        );

        $media = new MediaData(
            featured: 'media/SKU-1201/SKU-1201-01.webp',
            ogImage: 'media/SKU-1201/SKU-1201-og.webp',
            gallery: [
                new GalleryImage('media/SKU-1201/SKU-1201-01.webp', 'checksum-1'),
                new GalleryImage('media/SKU-1201/SKU-1201-02.webp', 'checksum-2'),
            ],
        );

        $result = $evaluator->evaluate($product, $media);

        $this->assertSame('ready', $result->curationStatus);
        $this->assertSame('published', $result->publicationStatus);
        $this->assertGreaterThanOrEqual(70, $result->score);
    }

    public function test_it_blocks_low_quality_products_and_collects_notes(): void
    {
        $evaluator = new ProductQualityEvaluator();

        $product = new CuratedProductData(
            title: 'Curto',
            slug: '',
            category: null,
            shortDescription: 'Pequena',
            technicalDescription: null,
            supplierCode: 'FORN-1202',
            sourceSupplier: 'asia',
            featuredImage: '',
            ogImage: '',
            availableColors: [],
            materials: [],
            status: 'draft',
            curationStatus: 'processed',
            qualityScore: 0,
            qualityNotes: [],
        );

        $media = new MediaData(
            featured: '',
            ogImage: '',
            gallery: [],
        );

        $result = $evaluator->evaluate($product, $media);

        $this->assertSame('blocked', $result->curationStatus);
        $this->assertSame('draft', $result->publicationStatus);
        $this->assertLessThan(70, $result->score);
        $this->assertNotEmpty($result->notes);
    }
}
