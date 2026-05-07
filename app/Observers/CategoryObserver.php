<?php

namespace App\Observers;

use App\Models\Category;
use Spatie\ResponseCache\Facades\ResponseCache;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        ResponseCache::clear();
    }

    public function deleted(Category $category): void
    {
        ResponseCache::clear();
    }
}
