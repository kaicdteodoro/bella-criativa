<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ProductSearch extends Component
{
    public string $query = '';

    public function render(): View
    {
        $query = mb_strtolower(trim($this->query));

        $results = mb_strlen($query) >= 2
            ? Cache::remember('search.results:' . md5($query), 30, function () use ($query) {
                $term = '%' . $query . '%';

                return Product::published()
                    ->select(['id', 'title', 'sku', 'slug'])
                    ->where(fn ($q) => $q
                        ->where('title', 'like', $term)
                        ->orWhere('sku', 'like', $term))
                    ->limit(10)
                    ->get();
            })
            : collect();

        return view('livewire.product-search', ['results' => $results]);
    }
}
