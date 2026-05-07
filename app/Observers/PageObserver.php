<?php

namespace App\Observers;

use App\Models\Page;
use Spatie\ResponseCache\Facades\ResponseCache;

class PageObserver
{
    public function saved(Page $page): void
    {
        ResponseCache::clear();
    }

    public function deleted(Page $page): void
    {
        ResponseCache::clear();
    }
}
