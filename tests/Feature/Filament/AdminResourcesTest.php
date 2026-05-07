<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_resource_urls_are_registered(): void
    {
        $this->assertSame('http://localhost:8000/admin/products/create', ProductResource::getUrl('create'));
        $this->assertSame('http://localhost:8000/admin/categories/create', CategoryResource::getUrl('create'));
        $this->assertSame('http://localhost:8000/admin/pages/create', PageResource::getUrl('create'));
    }

    public function test_product_list_page_shows_existing_records(): void
    {
        $this->actingAs(User::factory()->create());

        $products = collect([
            Product::query()->create([
                'sku' => 'SKU-001',
                'title' => 'Mochila Executiva',
                'slug' => 'mochila-executiva',
                'status' => 'draft',
            ]),
            Product::query()->create([
                'sku' => 'SKU-002',
                'title' => 'Garrafa Térmica',
                'slug' => 'garrafa-termica',
                'status' => 'published',
            ]),
        ]);

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords($products);
    }

    public function test_category_list_page_shows_existing_records(): void
    {
        $this->actingAs(User::factory()->create());

        $categories = collect([
            Category::query()->create([
                'name' => 'Brindes',
                'slug' => 'brindes',
            ]),
            Category::query()->create([
                'name' => 'Escritório',
                'slug' => 'escritorio',
            ]),
        ]);

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords($categories);
    }
}
