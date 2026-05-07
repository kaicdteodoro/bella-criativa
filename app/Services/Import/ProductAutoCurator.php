<?php

namespace App\Services\Import;

class ProductAutoCurator
{
    public function __construct(
        private readonly ProductContentNormalizer $normalizer,
        private readonly ProductQualityEvaluator $qualityEvaluator,
    ) {
    }

    public function curate(
        ProductRow $row,
        MediaData $media,
        ?string $source = null,
        ?AiCuratedProductContent $aiContent = null,
    ): CuratedProductData
    {
        $normalized = $this->normalizer->normalize($row, $media, $source, $aiContent);
        $evaluation = $this->qualityEvaluator->evaluate($normalized, $media);

        return new CuratedProductData(
            title: $normalized->title,
            slug: $normalized->slug,
            category: $normalized->category,
            shortDescription: $normalized->shortDescription,
            technicalDescription: $normalized->technicalDescription,
            supplierCode: $normalized->supplierCode,
            sourceSupplier: $normalized->sourceSupplier,
            featuredImage: $normalized->featuredImage,
            ogImage: $normalized->ogImage,
            availableColors: $normalized->availableColors,
            materials: $normalized->materials,
            status: $evaluation->publicationStatus,
            curationStatus: $evaluation->curationStatus,
            qualityScore: $evaluation->score,
            qualityNotes: $evaluation->notes,
        );
    }
}
