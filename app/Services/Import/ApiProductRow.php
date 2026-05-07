<?php

namespace App\Services\Import;

readonly class ApiProductRow
{
    /**
     * @param  string[]  $imageUrls
     */
    public function __construct(
        public string $sku,
        public string $title,
        public ?string $supplierCode,
        public ?string $category,
        public ?string $shortDescription,
        public ?string $technicalDescription,
        public array $imageUrls,
    ) {
    }
}
