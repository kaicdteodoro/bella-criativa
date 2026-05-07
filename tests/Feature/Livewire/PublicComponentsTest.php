<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CatalogFilters;
use App\Livewire\ProductSearch;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_toggle_and_clear_dispatch_events(): void
    {
        Category::query()->create(['name' => 'Copos', 'slug' => 'copos']);

        Livewire::test(CatalogFilters::class)
            ->call('toggleCategory', 'copos')
            ->assertSet('category', 'copos')
            ->assertDispatched('catalog-category-changed', category: 'copos')
            ->set('search', 'termico')
            ->assertDispatched('catalog-search-changed', search: 'termico')
            ->call('clearFilters')
            ->assertSet('category', null)
            ->assertSet('search', '')
            ->assertDispatched('catalog-category-changed', category: null)
            ->assertDispatched('catalog-search-changed', search: '');
    }

    public function test_product_search_returns_only_published_matches_for_queries_with_two_chars(): void
    {
        Product::query()->create([
            'sku' => 'SKU-901',
            'title' => 'Caneca Térmica',
            'slug' => 'caneca-termica',
            'status' => 'published',
        ]);
        Product::query()->create([
            'sku' => 'SKU-902',
            'title' => 'Caneta Luxo',
            'slug' => 'caneta-luxo',
            'status' => 'draft',
        ]);

        Livewire::test(ProductSearch::class)
            ->set('query', 'Ca')
            ->assertSee('Caneca Térmica')
            ->assertDontSee('Caneta Luxo');
    }

    public function test_product_search_shows_hint_for_short_queries(): void
    {
        Livewire::test(ProductSearch::class)
            ->set('query', 'C')
            ->assertSee('Digite pelo menos 2 caracteres');
    }
}
