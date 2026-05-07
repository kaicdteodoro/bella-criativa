<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\AiCuratedProductContent;
use App\Services\Import\GalleryImage;
use App\Services\Import\MediaData;
use App\Services\Import\ProductContentNormalizer;
use App\Services\Import\ProductRow;
use Tests\TestCase;

class ProductContentNormalizerTest extends TestCase
{
    public function test_it_normalizes_title_descriptions_and_slug(): void
    {
        $normalizer = new ProductContentNormalizer();

        $row = new ProductRow(
            sku: 'SKU-1101',
            title: 'Caneca premium OBS: lote atacado',
            supplierCode: 'FORN-1101',
            category: '  Canecas  ',
            shortDescription: null,
            technicalDescription: "<p>Descrição técnica longa com conteúdo suficiente para resumo automático.</p>\n\n\n<p>Mais detalhes.</p>",
            imagesZipUrl: '',
        );

        $media = new MediaData(
            featured: 'media/SKU-1101/SKU-1101-01.webp',
            ogImage: 'media/SKU-1101/SKU-1101-og.webp',
            gallery: [new GalleryImage('media/SKU-1101/SKU-1101-01.webp', 'checksum')],
            availableColors: ['#000000', '#000000'],
            materials: ['metal', 'metal'],
        );

        $result = $normalizer->normalize($row, $media, 'xbz');

        $this->assertSame('Caneca premium', $result->title);
        $this->assertSame('caneca-premium-sku-1101', $result->slug);
        $this->assertSame('Canecas', $result->category);
        $this->assertNotNull($result->shortDescription);
        $this->assertStringContainsString('Descrição técnica longa', $result->shortDescription);
        $this->assertStringContainsString('Mais detalhes.', (string) $result->technicalDescription);
        $this->assertSame(['#000000'], $result->availableColors);
        $this->assertSame(['metal'], $result->materials);
        $this->assertSame('draft', $result->status);
        $this->assertSame('processed', $result->curationStatus);
    }

    public function test_it_prefers_ai_curated_content_when_available(): void
    {
        $normalizer = new ProductContentNormalizer();

        $row = new ProductRow(
            sku: 'SKU-1102',
            title: 'Título bruto',
            supplierCode: 'FORN-1102',
            category: 'Categoria bruta',
            shortDescription: 'Descrição bruta',
            technicalDescription: 'Técnica bruta',
            imagesZipUrl: '',
        );

        $media = new MediaData(
            featured: 'media/SKU-1102/SKU-1102-01.webp',
            ogImage: 'media/SKU-1102/SKU-1102-og.webp',
            gallery: [new GalleryImage('media/SKU-1102/SKU-1102-01.webp', 'checksum')],
        );

        $ai = new AiCuratedProductContent(
            sku: 'SKU-1102',
            title: 'Caneta Executiva',
            shortDescription: 'Descrição da IA com mais qualidade.',
            technicalDescription: 'Detalhes técnicos da IA.',
            category: 'Escrita',
            confidence: 0.9,
        );

        $result = $normalizer->normalize($row, $media, 'asia', $ai);

        $this->assertSame('Caneta Executiva', $result->title);
        $this->assertSame('Escrita', $result->category);
        $this->assertSame('Descrição da IA com mais qualidade.', $result->shortDescription);
        $this->assertSame('Detalhes técnicos da IA.', $result->technicalDescription);
    }
}
