<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $page = Page::published()->where('slug', 'sobre')->with('sections')->firstOrFail();
        $sectionProducts = Product::published()->with(['categories', 'media'])->latest('id')->limit(4)->get();

        return view('pages.about', compact('page', 'sectionProducts'));
    }

    public function contact(): View
    {
        $page = Page::published()->where('slug', 'contato')->with('sections')->firstOrFail();
        $sectionProducts = Product::published()->with(['categories', 'media'])->latest('id')->limit(4)->get();

        return view('pages.contact', compact('page', 'sectionProducts'));
    }
}
