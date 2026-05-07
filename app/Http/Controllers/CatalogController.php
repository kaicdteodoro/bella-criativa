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

        $priority = config('catalog.category_filter_priority', []);

        $sectionCategories = Category::query()
            ->withCount([
                'products as published_products_count' => fn ($query) => $query->where('status', 'published'),
            ])
            ->get()
            ->sort(function (Category $a, Category $b) use ($priority): int {
                $ia = array_search($a->slug, $priority, true);
                $ib = array_search($b->slug, $priority, true);
                $ia = $ia === false ? PHP_INT_MAX : $ia;
                $ib = $ib === false ? PHP_INT_MAX : $ib;

                if ($ia !== $ib) {
                    return $ia <=> $ib;
                }

                return strcasecmp($a->filterDisplayName(), $b->filterDisplayName());
            })
            ->values();

        $featuredProductsQuery = Product::published()
            ->with(['categories', 'media'])
            ->latest('id');

        $featuredProducts = (clone $featuredProductsQuery)
            ->where('is_featured', true)
            ->limit(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $featuredProductsQuery
                ->limit(8)
                ->get();
        }

        $slideCategories = $sectionCategories
            ->filter(fn (Category $category) => ($category->published_products_count ?? 0) > 0)
            ->take(4)
            ->values();

        $slideCategoryIds = $slideCategories->pluck('id')->all();

        $coverProductsByCategoryId = Product::published()
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $slideCategoryIds))
            ->with(['media', 'categories'])
            ->latest('id')
            ->get()
            ->reduce(function (\Illuminate\Support\Collection $carry, Product $product) use ($slideCategoryIds) {
                $matchedCategoryId = $product->categories
                    ->pluck('id')
                    ->first(fn (int $id): bool => in_array($id, $slideCategoryIds, true));

                if (! $matchedCategoryId) {
                    return $carry;
                }

                if (! $carry->has($matchedCategoryId)) {
                    $carry->put($matchedCategoryId, $product);
                }

                return $carry;
            }, collect());

        $categorySlides = $slideCategories
            ->map(function (Category $category) use ($coverProductsByCategoryId): array {
                /** @var Product|null $coverProduct */
                $coverProduct = $coverProductsByCategoryId->get($category->id);
                $image = $coverProduct?->media->first()?->thumb_url
                    ?? $coverProduct?->featured_image_url
                    ?? $coverProduct?->media->first()?->url;

                return [
                    'slug' => $category->slug,
                    'name' => $category->filterDisplayName(),
                    'description' => $category->description,
                    'image' => $image,
                    'products_count' => (int) ($category->published_products_count ?? 0),
                ];
            })
            ->values();

        return view('pages.home', compact('page', 'featuredProducts', 'sectionCategories', 'categorySlides'));
    }
}
