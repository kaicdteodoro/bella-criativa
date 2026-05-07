<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $productsForSeo = Product::published()
            ->whereHas('categories', fn ($query) => $query->where('categories.id', $category->id))
            ->latest('id')
            ->limit(12)
            ->get(['id', 'title', 'slug', 'sku', 'short_description']);

        $productCount = $productsForSeo->count();
        $title = $category->filterDisplayName().' | Bella Criativa';
        $description = $category->description
            ? \Illuminate\Support\Str::limit(strip_tags($category->description), 160)
            : 'Confira produtos personalizados da categoria '.$category->filterDisplayName().' na Bella Criativa.';

        if ($productCount > 0) {
            $description = \Illuminate\Support\Str::limit(
                $description.' Explore '.$productCount.' opções disponíveis nesta categoria.',
                160
            );
        }

        return view('pages.categories.show', compact('category', 'title', 'description', 'productsForSeo'));
    }
}
