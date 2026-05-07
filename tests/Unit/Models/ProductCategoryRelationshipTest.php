<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_and_category_use_product_category_pivot(): void
    {
        $product = Product::query()->create([
            'sku' => 'SKU-401',
            'title' => 'Mochila Executiva',
            'slug' => 'mochila-executiva',
            'status' => 'published',
        ]);

        $category = Category::query()->create([
            'name' => 'Mochilas',
            'slug' => 'mochilas',
        ]);

        $product->categories()->attach($category);

        $this->assertDatabaseHas('product_category', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($product->fresh()->categories->contains($category));
        $this->assertTrue($category->fresh()->products->contains($product));
    }
}
