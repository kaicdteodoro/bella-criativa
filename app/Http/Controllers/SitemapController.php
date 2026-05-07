<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('home')))
            ->add(Url::create(route('products.index')));

        Page::published()->get()->each(function (Page $page) use ($sitemap): void {
            if ($page->slug === 'home') {
                return;
            }

            $path = match ($page->slug) {
                'sobre' => route('about'),
                'contato' => route('contact'),
                'lancamentos' => route('launches'),
                'linha-premium' => route('premium'),
                default => null,
            };

            if ($path) {
                $sitemap->add(Url::create($path)->setLastModificationDate($page->updated_at));
            }
        });

        Category::query()->get()->each(
            fn (Category $category) => $sitemap->add(
                Url::create(route('categories.show', $category->slug))->setLastModificationDate($category->updated_at)
            )
        );

        Product::published()->get()->each(
            fn (Product $product) => $sitemap->add(
                Url::create(route('products.show', $product->slug))->setLastModificationDate($product->updated_at)
            )
        );

        return $sitemap->toResponse(request());
    }
}
