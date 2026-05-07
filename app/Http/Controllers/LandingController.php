<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function launches(): View
    {
        $page = Page::published()->where('slug', 'lancamentos')->with('sections')->firstOrFail();
        $products = Product::published()
            ->where('is_launch', true)
            ->with(['categories', 'media'])
            ->latest('id')
            ->get();
        $sectionCategories = Category::query()->orderBy('name')->get();

        return view('pages.launches', compact('page', 'products', 'sectionCategories'));
    }

    public function premium(): View
    {
        $page = Page::published()->where('slug', 'linha-premium')->with('sections')->firstOrFail();
        $products = Product::published()
            ->where('is_premium', true)
            ->with(['categories', 'media'])
            ->latest('id')
            ->get();
        $sectionCategories = Category::query()->orderBy('name')->get();

        return view('pages.premium', compact('page', 'products', 'sectionCategories'));
    }
}
