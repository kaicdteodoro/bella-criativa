<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\MediaData;
use App\Services\Import\UrlImageProcessor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UrlImageProcessorTest extends TestCase
{
    public function test_it_processes_remote_images_and_keeps_color_hints(): void
    {
        if (! function_exists('imagepng')) {
            $this->markTestSkipped('GD não está disponível neste runtime.');
        }

        Storage::fake('public');

        Http::fake([
            'https://cdn.example.com/1.png' => Http::response($this->pngFixture('#000000'), 200),
            'https://cdn.example.com/2.png' => Http::response($this->pngFixture('#0057FF'), 200),
        ]);

        $processor = new UrlImageProcessor();
        $media = $processor->process('SKU-1501', [
            'https://cdn.example.com/1.png|||#000000',
            'https://cdn.example.com/2.png|||#0057FF',
        ], 80);

        $this->assertSame('media/SKU-1501/SKU-1501-01.webp', $media->featured);
        $this->assertSame('media/SKU-1501/SKU-1501-og.webp', $media->ogImage);
        $this->assertCount(2, $media->gallery);
        $this->assertSame('#000000', $media->gallery[0]->colorHex);
        $this->assertSame('#0057FF', $media->gallery[1]->colorHex);
        $this->assertSame(['#000000', '#0057FF'], $media->availableColors);
        Storage::disk('public')->assertExists($media->featured);
        Storage::disk('public')->assertExists($media->gallery[0]->thumbFile);
        Storage::disk('public')->assertExists($media->ogImage);
    }

    public function test_it_returns_empty_media_when_no_image_can_be_downloaded(): void
    {
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $media = (new UrlImageProcessor())->process('SKU-1502', ['https://cdn.example.com/fail.png']);

        $this->assertInstanceOf(MediaData::class, $media);
        $this->assertSame('', $media->featured);
        $this->assertSame('', $media->ogImage);
        $this->assertCount(0, $media->gallery);
    }

    public function test_it_returns_empty_media_when_image_urls_is_empty(): void
    {
        $media = (new UrlImageProcessor())->process('SKU-1503', []);

        $this->assertInstanceOf(MediaData::class, $media);
        $this->assertSame('', $media->featured);
        $this->assertCount(0, $media->gallery);
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
