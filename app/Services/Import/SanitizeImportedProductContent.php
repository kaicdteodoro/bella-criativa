<?php

namespace App\Services\Import;

use App\Models\Product;

class SanitizeImportedProductContent
{
    public function __construct(
        private readonly ProductContentNormalizer $normalizer,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function run(array $options = []): SanitizeImportedProductContentResult
    {
        $source = (string) ($options['source'] ?? 'xbz');
        $limit = filled($options['limit'] ?? null) ? max(1, (int) $options['limit']) : null;
        $dryRun = (bool) ($options['dry_run'] ?? true);

        $query = Product::query()
            ->when($source !== 'all', fn ($builder) => $builder->where('source_supplier', $source))
            ->whereNotNull('source_supplier')
            ->where(function ($builder) {
                $builder
                    ->whereNull('enriched_at')
                    ->orWhere('curation_status', 'processed');
            })
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $scanned = 0;
        $updated = 0;
        $samples = [];

        $query->get()->each(function (Product $product) use (&$scanned, &$updated, &$samples, $dryRun): void {
            $scanned++;

            $nextTitle = $this->normalizer->sanitizeImportedTitle($product->title) ?? $product->title;
            $nextShort = $this->normalizer->sanitizeCustomerFacingDescription($product->short_description);
            $nextTechnical = $this->normalizer->sanitizeCustomerFacingDescription($product->technical_description);

            $changes = [];

            if ($nextTitle !== $product->title) {
                $changes[] = 'title';
            }

            if ($nextShort !== $product->short_description) {
                $changes[] = 'short_description';
            }

            if ($nextTechnical !== $product->technical_description) {
                $changes[] = 'technical_description';
            }

            if ($changes === []) {
                return;
            }

            $updated++;

            if (count($samples) < 8) {
                $samples[] = [
                    'sku' => $product->sku,
                    'changes' => $changes,
                ];
            }

            if ($dryRun) {
                return;
            }

            $product->forceFill([
                'title' => $nextTitle,
                'short_description' => $nextShort,
                'technical_description' => $nextTechnical,
            ])->save();
        });

        return new SanitizeImportedProductContentResult(
            scanned: $scanned,
            updated: $updated,
            unchanged: max($scanned - $updated, 0),
            dryRun: $dryRun,
            samples: $samples,
        );
    }
}
