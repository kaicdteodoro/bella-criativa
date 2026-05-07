<?php

namespace App\Services\Import;

readonly class MediaData
{
    /**
     * @param  GalleryImage[]  $gallery
     * @param  string[]  $availableColors
     * @param  string[]  $materials
     */
    public function __construct(
        public string $featured,
        public string $ogImage,
        public array $gallery,
        public array $availableColors = [],
        public array $materials = [],
    ) {
    }
}
