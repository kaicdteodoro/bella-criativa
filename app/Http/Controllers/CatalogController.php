<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        $page = Page::published()
            ->where('slug', 'home')
            ->with('sections')
            ->first();

        $sectionCategories = Category::query()
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::published()
            ->where('is_featured', true)
            ->with(['categories', 'media'])
            ->latest('id')
            ->limit(8)
            ->get();

        return view('pages.home', compact('page', 'featuredProducts', 'sectionCategories'));
    }
}
