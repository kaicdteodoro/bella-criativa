<?php

namespace App\Services\Import;

use Illuminate\Support\Str;

class ProductContentNormalizer
{
    public function normalize(
        ProductRow $row,
        MediaData $media,
        ?string $source = null,
        ?AiCuratedProductContent $aiContent = null,
    ): CuratedProductData
    {
        $title = $this->normalizeTitle($aiContent?->title ?? $row->title);
        $category = $this->normalizeText($aiContent?->category ?? $row->category);
        $shortDescription = $this->normalizeDescription($aiContent?->shortDescription ?? $row->shortDescription);
        $technicalDescription = $this->normalizeDescription($aiContent?->technicalDescription ?? $row->technicalDescription);

        if ($shortDescription === null && $technicalDescription !== null) {
            $shortDescription = Str::limit(strip_tags($technicalDescription), 180);
        }

        $slugBase = $title !== '' ? $title : $row->sku;

        return new CuratedProductData(
            title: $title !== '' ? $title : $row->sku,
            slug: Str::slug(mb_substr($slugBase, 0, 200).' '.$row->sku),
            category: $category,
            shortDescription: $shortDescription,
            technicalDescription: $technicalDescription,
            supplierCode: $this->normalizeText($row->supplierCode),
            sourceSupplier: $this->normalizeText($source),
            featuredImage: $media->featured,
            ogImage: $media->ogImage,
            availableColors: array_values(array_unique($media->availableColors)),
            materials: array_values(array_unique($media->materials)),
            status: 'draft',
            curationStatus: 'processed',
            qualityScore: 0,
            qualityNotes: [],
        );
    }

    private function normalizeTitle(?string $value): ?string
    {
        $title = $this->normalizeText($value);

        if ($title === null) {
            return null;
        }

        $title = preg_replace('/\bOBS\.?:?.*/iu', '', $title) ?? $title;
        $title = preg_replace('/\bPEDID[OA]S?\s+M[ÍI]NIM[OA]S?.*/iu', '', $title) ?? $title;
        $title = preg_replace('/\b(ATACADO|VAREJO|REVENDA|PROMO[CÇ][AÃ]O)\b/iu', '', $title) ?? $title;
        $title = preg_replace('/\s{2,}/u', ' ', trim($title)) ?? $title;

        return Str::limit($title, 90, '');
    }

    private function normalizeDescription(?string $value): ?string
    {
        $normalized = $this->normalizeText($value);

        return $normalized === null ? null : preg_replace("/\n{3,}/", "\n\n", $normalized);
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\r"], "\n", $value)) ?? '');

        return $normalized !== '' ? $normalized : null;
    }
}
