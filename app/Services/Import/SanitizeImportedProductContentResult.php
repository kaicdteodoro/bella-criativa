<?php

namespace App\Services\Import;

readonly class SanitizeImportedProductContentResult
{
    /**
     * @param  array<int, array{sku: string, changes: string[]}>  $samples
     */
    public function __construct(
        public int $scanned,
        public int $updated,
        public int $unchanged,
        public bool $dryRun,
        public array $samples = [],
    ) {
    }
}
