<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\ImageProcessor;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ImageProcessorTest extends TestCase
{
    public function test_it_processes_gallery_images_and_generates_og_image(): void
    {
        if (! class_exists(ZipArchive::class) || ! function_exists('imagepng')) {
            $this->markTestSkipped('ZipArchive ou GD não estão disponíveis neste runtime.');
        }

        Storage::fake('public');

        $zipPath = tempnam(sys_get_temp_dir(), 'catalog_zip_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('SKU001_PRETO_METAL.png', $this->pngFixture('#000000'));
        $zip->addFromString('SKU001_AZUL_MADEIRA.png', $this->pngFixture('#0057FF'));
        $zip->close();

        $processor = new ImageProcessor();
        $media = $processor->process('SKU-001', $zipPath, 80);

        $this->assertSame('media/SKU-001/SKU-001-01.webp', $media->featured);
        $this->assertSame('media/SKU-001/SKU-001-og.webp', $media->ogImage);
        $this->assertCount(2, $media->gallery);
        $this->assertContains('#000000', $media->availableColors);
        $this->assertContains('#0057FF', $media->availableColors);
        $this->assertContains('metal', $media->materials);
        $this->assertContains('wood', $media->materials);
        $this->assertNotEmpty($media->gallery[0]->checksum);
        Storage::disk('public')->assertExists($media->featured);
        Storage::disk('public')->assertExists($media->ogImage);
        Storage::disk('public')->assertExists($media->gallery[1]->file);

        @unlink($zipPath);
    }

    private function pngFixture(string $hex): string
    {
        [$red, $green, $blue] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        $image = imagecreatetruecolor(8, 8);
        $color = imagecolorallocate($image, $red, $green, $blue);
        imagefill($image, 0, 0, $color);

        ob_start();
        imagepng($image);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return $binary;
    }
}
