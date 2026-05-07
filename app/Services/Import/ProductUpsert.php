<?php

namespace App\Services\Import;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Str;

class ProductUpsert
{
    private readonly ProductAutoCurator $curator;

    public function __construct(
        ?ProductAutoCurator $curator = null,
    ) {
        $this->curator = $curator ?? new ProductAutoCurator(
            new ProductContentNormalizer(),
            new ProductQualityEvaluator(),
        );
    }

    /**
     * @param  array<string, string>  $termMap
     */
    public function upsert(
        ProductRow $row,
        MediaData $media,
        array $termMap,
        ?string $source = null,
        ?AiCuratedProductContent $aiContent = null,
    ): ImportResult
    {
        $product = Product::query()->where('sku', $row->sku)->first();
        $action = $product ? ImportAction::Updated : ImportAction::Created;
        $curated = $this->curator->curate($row, $media, $source, $aiContent);

        $product ??= new Product();

        $product->fill([
            'sku' => $row->sku,
            'title' => $curated->title,
            'slug' => $curated->slug,
            'status' => $curated->status,
            'curation_status' => $curated->curationStatus,
            'short_description' => $curated->shortDescription,
            'technical_description' => $curated->technicalDescription,
            'featured_image' => $curated->featuredImage,
            'og_image' => $curated->ogImage,
            'available_colors' => $curated->availableColors,
            'materials' => $curated->materials,
            'supplier_code' => $curated->supplierCode,
            'source_supplier' => $curated->sourceSupplier,
            'quality_score' => $curated->qualityScore,
            'quality_notes' => $curated->qualityNotes,
            'processed_at' => now(),
            'enriched_at' => $aiContent ? now() : $product->enriched_at,
        ]);

        $product->save();

        $categoryIds = [];

        if ($curated->category) {
            $resolved = $this->resolveCategoryNameAndSlug($curated->category, $termMap);

            $category = Category::query()->firstOrCreate(
                ['slug' => $resolved['slug']],
                ['name' => $resolved['name']],
            );

            $categoryIds[] = $category->id;
        }

        $product->categories()->sync($categoryIds);

        foreach ($media->gallery as $index => $image) {
            $existingMedia = ProductMedia::query()
                ->where('product_id', $product->id)
                ->where('checksum', $image->checksum)
                ->first();

            if ($existingMedia) {
                if ($image->colorHex && $existingMedia->color_hex !== $image->colorHex) {
                    $existingMedia->update(['color_hex' => $image->colorHex]);
                }
                continue;
            }

            ProductMedia::query()->create([
                'product_id' => $product->id,
                'file' => $image->file,
                'thumb_file' => $image->thumbFile,
                'checksum' => $image->checksum,
                'color_hex' => $image->colorHex,
                'order' => $index,
            ]);
        }

        return new ImportResult(
            sku: $row->sku,
            action: $action,
            imagesProcessed: count($media->gallery),
            warnings: $curated->qualityNotes,
        );
    }

    /**
     * @param  array<string, string>  $termMap
     * @return array{name: string, slug: string}
     */
    private function resolveCategoryNameAndSlug(string $rawCategory, array $termMap): array
    {
        $trimmed = trim($rawCategory);
        $canonicalMap = config('catalog.category_canonical_names', []);
        $lower = mb_strtolower($trimmed);

        $name = $canonicalMap[$lower] ?? $trimmed;

        foreach ($termMap as $term => $_slug) {
            if (mb_strtolower($term) === mb_strtolower($name)) {
                $name = $term;

                break;
            }
        }

        $slug = $termMap[$name] ?? Str::slug($name);

        return ['name' => $name, 'slug' => $slug];
    }
}
