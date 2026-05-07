<?php

namespace Tests\Feature;

use App\Livewire\CatalogGrid;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_page_renders_successfully(): void
    {
        Category::query()->create([
            'name' => 'Copos',
            'slug' => 'copos',
        ]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Catálogo completo');
        $response->assertSee('Copos');
    }

    public function test_product_detail_page_renders_with_related_products(): void
    {
        $category = Category::query()->create([
            'name' => 'Garrafas',
            'slug' => 'garrafas',
        ]);

        $product = Product::query()->create([
            'sku' => 'SKU-201',
            'title' => 'Garrafa Inox',
            'slug' => 'garrafa-inox',
            'status' => 'published',
            'featured_image' => 'media/featured/garrafa-inox.webp',
        ]);
        $product->categories()->attach($category);

        $related = Product::query()->create([
            'sku' => 'SKU-202',
            'title' => 'Caneca Térmica',
            'slug' => 'caneca-termica',
            'status' => 'published',
            'featured_image' => 'media/featured/caneca-termica.webp',
        ]);
        $related->categories()->attach($category);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('Garrafa Inox');
        $response->assertSee('Caneca Térmica');
        $response->assertSee('SKU SKU-201');
    }

    public function test_category_page_renders_successfully(): void
    {
        $category = Category::query()->create([
            'name' => 'Escritório',
            'slug' => 'escritorio',
            'description' => 'Itens para rotina corporativa.',
        ]);

        $response = $this->get(route('categories.show', $category->slug));

        $response->assertOk();
        $response->assertSee('Escritório');
        $response->assertSee('Itens para rotina corporativa.');
    }

    public function test_catalog_grid_filters_products_by_category_and_search(): void
    {
        $copos = Category::query()->create([
            'name' => 'Copos',
            'slug' => 'copos',
        ]);
        $garrafas = Category::query()->create([
            'name' => 'Garrafas',
            'slug' => 'garrafas',
        ]);

        $matching = Product::query()->create([
            'sku' => 'SKU-301',
            'title' => 'Copo Térmico',
            'slug' => 'copo-termico',
            'status' => 'published',
            'featured_image' => 'media/featured/copo-termico.webp',
        ]);
        $matching->categories()->attach($copos);

        $otherCategory = Product::query()->create([
            'sku' => 'SKU-302',
            'title' => 'Garrafa Metal',
            'slug' => 'garrafa-metal',
            'status' => 'published',
            'featured_image' => 'media/featured/garrafa-metal.webp',
        ]);
        $otherCategory->categories()->attach($garrafas);

        $otherSearch = Product::query()->create([
            'sku' => 'SKU-303',
            'title' => 'Caneca Cerâmica',
            'slug' => 'caneca-ceramica',
            'status' => 'published',
            'featured_image' => 'media/featured/caneca-ceramica.webp',
        ]);
        $otherSearch->categories()->attach($copos);

        Livewire::test(CatalogGrid::class, [
            'category' => 'copos',
            'search' => 'Térmico',
        ])
            ->assertSee('Copo Térmico')
            ->assertDontSee('Garrafa Metal')
            ->assertDontSee('Caneca Cerâmica');
    }
}
