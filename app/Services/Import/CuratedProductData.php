<?php

namespace App\Services\Import;

readonly class CuratedProductData
{
    /**
     * @param  string[]  $availableColors
     * @param  string[]  $materials
     * @param  string[]  $qualityNotes
     */
    public function __construct(
        public string $title,
        public string $slug,
        public ?string $category,
        public ?string $shortDescription,
        public ?string $technicalDescription,
        public ?string $supplierCode,
        public ?string $sourceSupplier,
        public string $featuredImage,
        public string $ogImage,
        public array $availableColors,
        public array $materials,
        public string $status,
        public string $curationStatus,
        public int $qualityScore,
        public array $qualityNotes,
    ) {
    }
}
