<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class CatalogGrid extends Component
{
    public int $perPage = 24;

    public ?string $category = null;

    public string $search = '';

    public function mount(?string $category = null, string $search = ''): void
    {
        $this->category = $category;
        $this->search = $search;
    }

    #[On('catalog-category-changed')]
    public function onCategoryChanged(?string $category): void
    {
        $this->category = $category;
        $this->perPage = 24;
    }

    #[On('catalog-search-changed')]
    public function onSearchChanged(string $search): void
    {
        $this->search = $search;
        $this->perPage = 24;
    }

    public function loadMore(): void
    {
        $this->perPage += 24;
    }

    public function render(): View
    {
        $search = mb_strtolower(trim($this->search));
        $category = $this->category;
        $perPage = $this->perPage;
        $cacheKey = 'catalog.grid:' . ($category ?? '') . ':' . md5($search) . ':' . $perPage;

        $products = Cache::remember($cacheKey, 60, function () use ($search, $category, $perPage) {
            return Product::published()
                ->select(['id', 'slug', 'title', 'featured_image', 'available_colors'])
                ->with(['categories', 'media'])
                ->when($category, fn ($query) => $query->whereHas(
                    'categories',
                    fn ($q) => $q->where('slug', $category)
                ))
                ->when($search !== '', function ($query) use ($search) {
                    $term = '%' . $search . '%';
                    $query->where(function ($inner) use ($term) {
                        $inner->where('title', 'like', $term)
                            ->orWhere('sku', 'like', $term);
                    });
                })
                ->latest('id')
                ->limit($perPage)
                ->get();
        });

        return view('livewire.catalog-grid', compact('products'));
    }
}
