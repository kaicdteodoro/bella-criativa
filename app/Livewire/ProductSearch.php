<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProductSearch extends Component
{
    public string $query = '';

    public function render(): View
    {
        $results = mb_strlen($this->query) >= 2
            ? Product::published()
                ->where(fn ($q) => $q
                    ->where('title', 'like', "%{$this->query}%")
                    ->orWhere('sku', 'like', "%{$this->query}%"))
                ->limit(10)
                ->get()
            : collect();

        return view('livewire.product-search', ['results' => $results]);
    }
}
