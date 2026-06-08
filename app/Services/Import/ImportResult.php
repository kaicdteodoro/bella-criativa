<?php

namespace App\Services\Import;

readonly class ImportResult
{
    /**
     * @param  string[]  $warnings
     */
    public function __construct(
        public string $sku,
        public ImportAction $action,
        public int $imagesProcessed,
        public array $warnings = [],
        public ?string $reason = null,
        public bool $published = false,
    ) {
    }
}
