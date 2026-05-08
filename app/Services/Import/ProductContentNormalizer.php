<?php

namespace App\Services\Import;

use Illuminate\Support\Str;

class ProductContentNormalizer
{
    public function sanitizeImportedTitle(?string $value): ?string
    {
        return $this->normalizeTitle($value);
    }

    public function sanitizeCustomerFacingDescription(?string $value): ?string
    {
        return $this->normalizeDescription($value);
    }

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

        if ($normalized === null) {
            return null;
        }

        $normalized = $this->stripSupplierCommercialNotes($normalized);

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

    private function stripSupplierCommercialNotes(string $value): ?string
    {
        $patterns = [
            '/\(?\bobs\.?:?.*$/iu',
            '/\(?\bpedido\s*m[íi]nimo\b.*?(?:[.;]|$)/iu',
            '/\(?\bpedidos?\s*m[íi]nimos?\b.*?(?:[.;]|$)/iu',
            '/\(?\bquantidade\s*m[íi]nima\b.*?(?:[.;]|$)/iu',
            '/\(?\bquantidades?\s*m[íi]nimas?\b.*?(?:[.;]|$)/iu',
            '/\(?\bm[íi]nimo\s+de\s+\d+\s*(?:pe[çc]as?|un(?:id(?:ades?)?)?)\b.*?(?:[.;]|$)/iu',
            '/\(?\bvenda\s+somente\s+para\s+revenda\b.*?(?:[.;]|$)/iu',
            '/\(?\bexclusivo\s+para\s+revenda\b.*?(?:[.;]|$)/iu',
            '/\(?\batacado\b.*?(?:[.;]|$)/iu',
            '/\(?\brevenda\b.*?(?:[.;]|$)/iu',
            '/\(?\bcaixa\s+fechada\b.*?(?:[.;]|$)/iu',
            '/\(?\bpack\s+fechado\b.*?(?:[.;]|$)/iu',
            '/\(?\bm[úu]ltiplos?\s+de\s+\d+\b.*?(?:[.;]|$)/iu',
        ];

        $cleaned = $value;

        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $cleaned) ?? $cleaned;
        }

        $cleaned = preg_replace('/\(\s*\)/u', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s*([,;:.])\s*/u', '$1 ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/([,;:])\s*([,;:.])/u', '$2 ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/(?:\s*[|\/-]\s*){2,}/u', ' ', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s{2,}/u', ' ', $cleaned) ?? $cleaned;
        $cleaned = trim($cleaned, " \t\n\r\0\x0B,;:-|/");

        return $cleaned !== '' ? $cleaned : null;
    }
}
