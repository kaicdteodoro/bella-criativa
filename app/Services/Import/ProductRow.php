<?php

namespace App\Services\Import;

readonly class ProductRow
{
    public function __construct(
        public string $sku,
        public string $title,
        public ?string $supplierCode,
        public ?string $category,
        public ?string $shortDescription,
        public ?string $technicalDescription,
        public string $imagesZipUrl,
    ) {
    }
}
