<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $priority = config('catalog.category_filter_priority', []);

        $categories = Category::query()->get()->sort(function (Category $a, Category $b) use ($priority): int {
            $ia = array_search($a->slug, $priority, true);
            $ib = array_search($b->slug, $priority, true);
            $ia = $ia === false ? PHP_INT_MAX : $ia;
            $ib = $ib === false ? PHP_INT_MAX : $ib;

            if ($ia !== $ib) {
                return $ia <=> $ib;
            }

            return strcasecmp($a->filterDisplayName(), $b->filterDisplayName());
        })->values();
        $activeCategory = $request->query('categoria');
        $search = trim((string) $request->query('busca', ''));

        return view('pages.products.index', compact('categories', 'activeCategory', 'search'));
    }

    public function show(string $slug): View
    {
        $product = Product::published()
            ->with(['categories', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::published()
            ->whereKeyNot($product->id)
            ->with(['categories', 'media'])
            ->limit(4)
            ->get();

        return view('pages.products.show', compact('product', 'related'));
    }
}
