<?php

namespace App\Services\Import;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Throwable;

class ProductAiBatchCurator
{
    /**
     * @param  Collection<int, ProductRow>  $rows
     * @return array<string, AiCuratedProductContent>
     */
    public function curate(Collection $rows): array
    {
        if (! config('catalog.ai_curation.enabled') || $rows->isEmpty()) {
            return [];
        }

        try {
            $request = Prism::structured()
                ->using($this->provider(), (string) config('catalog.ai_curation.model'))
                ->withSchema($this->schema())
                ->withSystemPrompt($this->systemPrompt())
                ->withPrompt($this->userPrompt($rows))
                ->withMaxTokens((int) config('catalog.ai_curation.max_tokens', 4000))
                ->withClientOptions(['timeout' => (int) config('catalog.ai_curation.timeout', 120)]);

            if (method_exists($request, 'withTemperature')) {
                $request = $request->withTemperature((float) config('catalog.ai_curation.temperature', 0.2));
            }

            $response = $request->asStructured();
        } catch (Throwable $exception) {
            Log::warning('AI batch curation failed. Falling back to local normalization.', [
                'message' => $exception->getMessage(),
                'provider' => config('catalog.ai_curation.provider'),
                'model' => config('catalog.ai_curation.model'),
                'batch_size' => $rows->count(),
            ]);

            return [];
        }

        $items = (array) ($response->structured['products'] ?? []);
        $mapped = [];

        foreach ($items as $item) {
            $sku = trim((string) ($item['sku'] ?? ''));

            if ($sku === '') {
                continue;
            }

            $mapped[$sku] = new AiCuratedProductContent(
                sku: $sku,
                title: $this->nullableString($item['title'] ?? null),
                shortDescription: $this->nullableString($item['short_description'] ?? null),
                technicalDescription: $this->nullableString($item['technical_description'] ?? null),
                category: $this->nullableString($item['category'] ?? null),
                confidence: isset($item['confidence']) ? (float) $item['confidence'] : null,
            );
        }

        return $mapped;
    }

    private function provider(): Provider|string
    {
        $provider = (string) config('catalog.ai_curation.provider', 'ollama');

        return Provider::tryFrom($provider) ?? $provider;
    }

    private function schema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'catalog_batch_curation',
            description: 'Structured curation output for a batch of catalog products.',
            properties: [
                new ArraySchema(
                    name: 'products',
                    description: 'Curated product content items.',
                    items: new ObjectSchema(
                        name: 'product',
                        description: 'Curated content for a single product.',
                        properties: [
                            new StringSchema('sku', 'Original SKU of the product.'),
                            new StringSchema('title', 'Clean commercial title with unnecessary details removed.'),
                            new StringSchema('short_description', 'Short SEO-friendly summary in Brazilian Portuguese.'),
                            new StringSchema('technical_description', 'Cleaned technical description in Brazilian Portuguese without marketing noise.'),
                            new StringSchema('category', 'Best normalized category label for the product.', true),
                            new NumberSchema('confidence', 'Confidence from 0 to 1 for the curation quality.', true, null, 1, null, 0, null),
                        ],
                        requiredFields: ['sku', 'title', 'short_description', 'technical_description']
                    ),
                    minItems: 1,
                ),
            ],
            requiredFields: ['products'],
        );
    }

    /**
     * @param  Collection<int, ProductRow>  $rows
     */
    private function userPrompt(Collection $rows): string
    {
        $payload = $rows->map(fn (ProductRow $row): array => [
            'sku' => $row->sku,
            'title' => $row->title,
            'supplier_code' => $row->supplierCode,
            'category' => $row->category,
            'short_description' => $row->shortDescription,
            'technical_description' => $row->technicalDescription,
        ])->values()->all();

        return "Curate the following products and return only the structured result.\n\n".
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are curating imported catalog products for a Brazilian corporate gifts e-commerce.

Rules:
- Write in pt-BR.
- Remove useless supplier jargon, codes, color suffixes, quantity packs, and shipping noise from titles.
- Keep titles commercially clear, concise, and SEO-friendly.
- Title must be short: ideally 35-65 characters and never exceed 80 characters.
- Never keep internal-sales notes like "OBS", "pedido mínimo", "atacado", "varejo", "consulte", "código interno", "lote" in the title.
- Never keep full technical specs in the title (capacity, detailed mechanisms, long phrase lists). Move details to technical_description.
- Output titles in title case and with a clean product naming style.
- Use short descriptions with strong commercial clarity, but no exaggerated marketing fluff.
- Clean technical descriptions so they are readable and useful.
- If the imported title contains details that belong in the description, move them out of the title.
- Preserve the product identity and avoid inventing specifications not present in the source.
- Normalize category labels when possible.
- Return one item for each input SKU.
PROMPT;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
