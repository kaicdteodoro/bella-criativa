<?php

namespace Tests\Unit\Filament;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\ProductResource;
use ReflectionMethod;
use Tests\TestCase;

class ResourceHelperTest extends TestCase
{
    public function test_product_media_item_label_uses_order_and_filename(): void
    {
        $label = $this->callProtectedStatic(ProductResource::class, 'mediaItemLabel', [[
            'file' => 'media/gallery/copo-termico.webp',
            'order' => 3,
        ]]);

        $this->assertSame('Imagem 3: copo-termico.webp', $label);
    }

    public function test_page_template_type_options_follow_template_rules(): void
    {
        $homeOptions = $this->callProtectedStatic(PageResource::class, 'allowedTypeOptionsForTemplate', ['home']);
        $defaultOptions = $this->callProtectedStatic(PageResource::class, 'allowedTypeOptionsForTemplate', ['invalid']);

        $this->assertArrayHasKey('featured_products', $homeOptions);
        $this->assertArrayHasKey('category_mosaic', $homeOptions);
        $this->assertSame([
            'rich_text' => 'Texto rico',
            'cta' => 'Chamada para ação',
        ], $defaultOptions);
    }

    public function test_page_section_item_label_includes_state_and_heading(): void
    {
        $label = $this->callProtectedStatic(PageResource::class, 'buildSectionItemLabel', [[
            'type' => 'hero',
            'heading' => 'Coleção corporativa',
            'content' => ['text' => 'Texto complementar'],
            'is_active' => false,
        ]]);

        $this->assertSame('Hero - Coleção corporativa (inativo)', $label);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private function callProtectedStatic(string $class, string $method, array $arguments): mixed
    {
        $reflection = new ReflectionMethod($class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(null, $arguments);
    }
}
