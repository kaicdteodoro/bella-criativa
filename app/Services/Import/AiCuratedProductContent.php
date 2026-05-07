<?php

namespace App\Services\Import;

readonly class AiCuratedProductContent
{
    public function __construct(
        public string $sku,
        public ?string $title,
        public ?string $shortDescription,
        public ?string $technicalDescription,
        public ?string $category,
        public ?float $confidence = null,
    ) {
    }
}
