<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_core_routes_and_published_content(): void
    {
        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'template' => 'home',
            'status' => 'published',
        ]);
        Page::query()->create([
            'title' => 'Sobre',
            'slug' => 'sobre',
            'template' => 'about',
            'status' => 'published',
        ]);
        Category::query()->create([
            'name' => 'Copos',
            'slug' => 'copos',
        ]);
        Product::query()->create([
            'sku' => 'SKU-801',
            'title' => 'Copo Térmico',
            'slug' => 'copo-termico',
            'status' => 'published',
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringContainsString('xml', (string) $response->headers->get('content-type'));
        $response->assertSee(route('home'), false);
        $response->assertSee(route('products.index'), false);
        $response->assertSee(route('about'), false);
        $response->assertSee(route('categories.show', 'copos'), false);
        $response->assertSee(route('products.show', 'copo-termico'), false);
    }
}
