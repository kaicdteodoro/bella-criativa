<?php

namespace App\Services\Import\Concerns;

use Illuminate\Support\Str;

trait NormalizesSkuPath
{
    private function sanitizeSkuForPath(string $sku): string
    {
        $normalized = Str::of($sku)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->value();

        return $normalized !== '' ? $normalized : 'SKU-'.substr(hash('sha1', $sku), 0, 8);
    }
}
