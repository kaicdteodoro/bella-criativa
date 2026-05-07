<?php

namespace App\Services\Import;

use App\Services\Import\Concerns\NormalizesSkuPath;
use App\Services\Import\Exceptions\ImageProcessingException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UrlImageProcessor
{
    use NormalizesSkuPath;

    private const MAX_DIMENSION = 1200;
    private const THUMB_W = 400;
    private const THUMB_H = 500;

    /**
     * @param  string[]  $imageUrls
     */
    public function process(string $sku, array $imageUrls, int $quality = 80): MediaData
    {
        if ($imageUrls === []) {
            throw new ImageProcessingException("Nenhuma URL de imagem disponível para o SKU {$sku}.");
        }

        $manager = new ImageManager(new Driver());
        $safeSku = $this->sanitizeSkuForPath($sku);
        $gallery = [];
        $featuredBinary = null;
        $colors = [];

        foreach ($imageUrls as $urlWithHint) {
            [$url, $colorHex] = $this->splitColorHint($urlWithHint);
            $binary = $this->downloadImage($url);

            if ($binary === null) {
                continue;
            }

            try {
                $encoded = (string) $manager->read($binary)->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION)->toWebp($quality);
                $thumb = (string) $manager->read($binary)->scaleDown(self::THUMB_W, self::THUMB_H)->toWebp(75);
            } catch (\Throwable) {
                continue;
            }

            $seq = count($gallery) + 1;
            $relativePath = sprintf('media/%s/%s-%02d.webp', $safeSku, $safeSku, $seq);
            $thumbPath = sprintf('media/%s/%s-%02d_sm.webp', $safeSku, $safeSku, $seq);

            Storage::disk('public')->put($relativePath, $encoded);
            Storage::disk('public')->put($thumbPath, $thumb);

            if ($featuredBinary === null) {
                $featuredBinary = $binary;
            }

            $gallery[] = new GalleryImage(
                file: $relativePath,
                checksum: hash('sha256', $encoded),
                colorHex: $colorHex,
                thumbFile: $thumbPath,
            );

            if ($colorHex !== null) {
                $colors[] = $colorHex;
            }
        }

        if ($gallery === []) {
            throw new ImageProcessingException(
                "Nenhuma imagem pôde ser baixada ou processada para o SKU {$sku}."
            );
        }

        $ogRelativePath = sprintf('media/%s/%s-og.webp', $safeSku, $safeSku);
        $ogBinary = (string) $manager->read($featuredBinary)->cover(1200, 630)->toWebp($quality);
        Storage::disk('public')->put($ogRelativePath, $ogBinary);

        return new MediaData(
            featured: $gallery[0]->file,
            ogImage: $ogRelativePath,
            gallery: $gallery,
            availableColors: array_values(array_unique($colors)),
        );
    }

    private function downloadImage(string $url): ?string
    {
        $timeout = (int) config('catalog.import.timeout', 30);
        $maxAttempts = max(1, (int) config('catalog.import.max_attempts', 3));

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout($timeout)->get($url);
                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Throwable) {
                // retry
            }

            if ($attempt < $maxAttempts) {
                usleep(500000 * $attempt);
            }
        }

        return null;
    }

    /** @return array{0:string,1:?string} */
    private function splitColorHint(string $urlWithHint): array
    {
        $parts = explode('#', $urlWithHint, 2);
        $url = $parts[0];
        $hint = $parts[1] ?? null;

        if (! is_string($hint) || ! preg_match('/^#[0-9A-Fa-f]{6}$/', $hint)) {
            return [$url, null];
        }

        return [$url, strtoupper($hint)];
    }
}
