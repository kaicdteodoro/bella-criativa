<?php

namespace App\Services\Import;

readonly class GalleryImage
{
    public function __construct(
        public string $file,
        public string $checksum,
        public ?string $colorHex = null,
        public ?string $thumbFile = null,
    ) {
    }
}
