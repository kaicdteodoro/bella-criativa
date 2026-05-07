<?php

namespace Tests\Feature\Observers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;
use Tests\TestCase;

class ResponseCacheObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_creation_clears_response_cache(): void
    {
        ResponseCache::spy();

        Product::query()->create([
            'sku' => 'SKU-701',
            'title' => 'Mochila Slim',
            'slug' => 'mochila-slim',
            'status' => 'published',
        ]);

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_product_update_clears_response_cache(): void
    {
        $product = Product::query()->create([
            'sku' => 'SKU-702',
            'title' => 'Mochila Slim',
            'slug' => 'mochila-slim-2',
            'status' => 'published',
        ]);

        ResponseCache::spy();

        $product->update(['title' => 'Mochila Slim Pro']);

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_product_deletion_clears_response_cache(): void
    {
        $product = Product::query()->create([
            'sku' => 'SKU-703',
            'title' => 'Mochila Slim',
            'slug' => 'mochila-slim-3',
            'status' => 'published',
        ]);

        ResponseCache::spy();

        $product->delete();

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_category_creation_clears_response_cache(): void
    {
        ResponseCache::spy();

        Category::query()->create([
            'name' => 'Moleskines',
            'slug' => 'moleskines',
        ]);

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_category_update_clears_response_cache(): void
    {
        $category = Category::query()->create([
            'name' => 'Moleskines',
            'slug' => 'moleskines-2',
        ]);

        ResponseCache::spy();

        $category->update(['name' => 'Moleskines Premium']);

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_category_deletion_clears_response_cache(): void
    {
        $category = Category::query()->create([
            'name' => 'Moleskines',
            'slug' => 'moleskines-3',
        ]);

        ResponseCache::spy();

        $category->delete();

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_page_creation_clears_response_cache(): void
    {
        ResponseCache::spy();

        Page::query()->create([
            'title' => 'Sobre',
            'slug' => 'sobre',
            'template' => 'about',
            'status' => 'published',
        ]);

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_page_update_clears_response_cache(): void
    {
        $page = Page::query()->create([
            'title' => 'Sobre',
            'slug' => 'sobre-2',
            'template' => 'about',
            'status' => 'published',
        ]);

        ResponseCache::spy();

        $page->update(['title' => 'Sobre a Bella']);

        ResponseCache::shouldHaveReceived('clear')->once();
    }

    public function test_page_deletion_clears_response_cache(): void
    {
        $page = Page::query()->create([
            'title' => 'Sobre',
            'slug' => 'sobre-3',
            'template' => 'about',
            'status' => 'published',
        ]);

        ResponseCache::spy();

        $page->delete();

        ResponseCache::shouldHaveReceived('clear')->once();
    }
}
