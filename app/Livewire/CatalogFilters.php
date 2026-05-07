<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class CatalogFilters extends Component
{
    #[Url(as: 'categoria')]
    public ?string $category = null;

    #[Url(as: 'busca')]
    public string $search = '';

    public function toggleCategory(string $slug): void
    {
        $this->category = $this->category === $slug ? null : $slug;
        $this->dispatch('catalog-category-changed', category: $this->category)
            ->to($this->gridComponentName);
    }

    public function updatedSearch(): void
    {
        $this->dispatch('catalog-search-changed', search: $this->search)
            ->to($this->gridComponentName);
    }

    public function clearFilters(): void
    {
        $this->category = null;
        $this->search = '';
        $this->dispatch('catalog-category-changed', category: null)
            ->to($this->gridComponentName);
        $this->dispatch('catalog-search-changed', search: '')
            ->to($this->gridComponentName);
    }

    #[Computed]
    public function gridComponentName(): string
    {
        return CatalogGrid::class;
    }

    public function render(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('livewire.catalog-filters', compact('categories'));
    }
}
