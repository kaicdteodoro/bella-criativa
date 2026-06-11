<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
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
        $products = Product::published()
            ->with(['categories', 'media'])
            ->when($this->category, fn ($query) => $query->whereHas(
                'categories',
                fn ($categoryQuery) => $categoryQuery->where('slug', $this->category)
            ))
            ->when(trim($this->search) !== '', function ($query) {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('short_description', 'like', $term)
                        ->orWhere('sku', 'like', $term);
                });
            })
            ->latest('id')
            ->limit($this->perPage)
            ->get();

        return view('livewire.catalog-grid', compact('products'));
    }
}
