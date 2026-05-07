<?php

namespace App\Services\Import;

use App\Services\Import\Concerns\NormalizesSkuPath;
use App\Services\Import\Exceptions\ImageProcessingException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use ZipArchive;

class ImageProcessor
{
    use NormalizesSkuPath;

    private const MAX_DIMENSION = 1200;
    private const THUMB_W = 400;
    private const THUMB_H = 500;

    private const COLOR_MAP = [
        'PRETO' => '#000000',
        'BRANCO' => '#FFFFFF',
        'PRATA' => '#C0C0C0',
        'AZUL' => '#0057FF',
        'VERMELHO' => '#FF0000',
        'AMARELO' => '#FFD700',
        'VERDE' => '#008000',
        'LARANJA' => '#FF6600',
        'ROSA' => '#FF69B4',
    ];

    private const MATERIAL_MAP = [
        'MADEIRA' => 'wood',
        'METAL' => 'metal',
        'PLASTICO' => 'plastic',
        'COURO' => 'leather',
        'TECIDO' => 'fabric',
    ];

    public function process(string $sku, string $zipPath, int $quality = 80): MediaData
    {
        if (! class_exists(ZipArchive::class)) {
            throw new ImageProcessingException('A extensão ZIP do PHP não está disponível.');
        }

        $manager = new ImageManager(new Driver());
        $zip = new ZipArchive();
        $openResult = $zip->open($zipPath);

        if ($openResult !== true) {
            throw new ImageProcessingException("Não foi possível abrir o ZIP do SKU {$sku}.");
        }

        $safeSku = $this->sanitizeSkuForPath($sku);
        $gallery = [];
        $colors = [];
        $materials = [];
        $featuredBinary = null;

        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = (string) $zip->getNameIndex($index);

                if ($entryName === '' || str_ends_with($entryName, '/')) {
                    continue;
                }

                if (! $this->isImageEntry($entryName)) {
                    continue;
                }

                $binary = $zip->getFromIndex($index);

                if (! is_string($binary) || $binary === '') {
                    continue;
                }

                $img = $manager->read($binary)->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);
                $encoded = (string) $img->toWebp($quality);

                $seq = count($gallery) + 1;
                $relativePath = sprintf('media/%s/%s-%02d.webp', $safeSku, $safeSku, $seq);
                Storage::disk('public')->put($relativePath, $encoded);

                $thumb = (string) $manager->read($binary)->scaleDown(self::THUMB_W, self::THUMB_H)->toWebp(75);
                $thumbPath = sprintf('media/%s/%s-%02d_sm.webp', $safeSku, $safeSku, $seq);
                Storage::disk('public')->put($thumbPath, $thumb);

                if ($featuredBinary === null) {
                    $featuredBinary = $binary;
                }

                $gallery[] = new GalleryImage(
                    file: $relativePath,
                    checksum: hash('sha256', $encoded),
                    thumbFile: $thumbPath,
                );

                $stem = Str::of(pathinfo($entryName, PATHINFO_FILENAME))
                    ->ascii()
                    ->upper()
                    ->replaceMatches('/[^A-Z0-9]+/', ' ')
                    ->trim()
                    ->value();

                $colors = array_merge($colors, $this->detectColors($stem));
                $materials = array_merge($materials, $this->detectMaterials($stem));
            }
        } catch (\Throwable $exception) {
            throw new ImageProcessingException(
                "Falha ao processar imagens do SKU {$sku}: {$exception->getMessage()}",
                previous: $exception,
            );
        } finally {
            $zip->close();
        }

        if ($gallery === []) {
            throw new ImageProcessingException("O ZIP do SKU {$sku} não contém imagens válidas.");
        }

        $ogRelativePath = sprintf('media/%s/%s-og.webp', $safeSku, $safeSku);
        $ogBinary = (string) $manager->read($featuredBinary)->cover(1200, 630)->toWebp($quality);
        Storage::disk('public')->put($ogRelativePath, $ogBinary);

        return new MediaData(
            featured: $gallery[0]->file,
            ogImage: $ogRelativePath,
            gallery: $gallery,
            availableColors: array_values(array_unique($colors)),
            materials: array_values(array_unique($materials)),
        );
    }

    private function isImageEntry(string $entryName): bool
    {
        return in_array(
            Str::lower(pathinfo($entryName, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'webp'],
            true,
        );
    }

    /** @return string[] */
    private function detectColors(string $stem): array
    {
        $matches = [];
        foreach (self::COLOR_MAP as $pattern => $hex) {
            if (str_contains($stem, $pattern)) {
                $matches[] = $hex;
            }
        }
        return $matches;
    }

    /** @return string[] */
    private function detectMaterials(string $stem): array
    {
        $matches = [];
        foreach (self::MATERIAL_MAP as $pattern => $material) {
            if (str_contains($stem, $pattern)) {
                $matches[] = $material;
            }
        }
        return $matches;
    }
}
