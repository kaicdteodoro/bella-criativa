<?php

namespace App\Services\Import;

readonly class ProductQualityEvaluation
{
    /**
     * @param  string[]  $notes
     */
    public function __construct(
        public int $score,
        public string $curationStatus,
        public string $publicationStatus,
        public array $notes,
    ) {
    }
}
