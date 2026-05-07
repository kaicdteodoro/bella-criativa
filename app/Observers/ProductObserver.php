<?php

namespace App\Observers;

use App\Models\Product;
use Spatie\ResponseCache\Facades\ResponseCache;

class ProductObserver
{
    public function saved(Product $product): void
    {
        ResponseCache::clear();
    }

    public function deleted(Product $product): void
    {
        ResponseCache::clear();
    }
}
