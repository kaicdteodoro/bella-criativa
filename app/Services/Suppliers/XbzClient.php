<?php

namespace App\Services\Suppliers;

use App\Services\Import\ApiProductRow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class XbzClient
{
    private const BASE_URL = 'https://api.minhaxbz.com.br:5001/api/clientes/ProdutosListar';
    private const COLOR_TOKEN_MAP = [
        'AZU' => '#0057FF',
        'BCO' => '#FFFFFF',
        'BRA' => '#FFFFFF',
        'LAR' => '#FF6600',
        'PRA' => '#C0C0C0',
        'PRE' => '#000000',
        'VD' => '#008000',
        'VER' => '#008000',
        'VM' => '#FF0000',
        'AMA' => '#FFD700',
        'ROS' => '#FF69B4',
    ];

    /**
     * @return ApiProductRow[]
     */
    public function fetchAll(string $busca = ''): array
    {
        $cnpj = config('catalog.suppliers.xbz.cnpj');
        $token = config('catalog.suppliers.xbz.token');

        if (empty($cnpj) || empty($token)) {
            throw new RuntimeException('Credenciais da XBZ não configuradas (XBZ_CNPJ / XBZ_TOKEN).');
        }

        $params = $busca !== '' ? ['busca' => $busca] : [];

        $response = Http::timeout(30)
            ->withHeaders([
                'cnpj' => $cnpj,
                'token' => $token,
            ])
            ->get(self::BASE_URL, $params);

        match ($response->status()) {
            401 => throw new RuntimeException('XBZ: token ou CNPJ inválidos.'),
            403 => throw new RuntimeException('XBZ: limite diário de 24 requisições atingido.'),
            500 => throw new RuntimeException('XBZ: erro interno no servidor do fornecedor.'),
            default => null,
        };

        if (! $response->successful()) {
            throw new RuntimeException("XBZ API retornou HTTP {$response->status()}.");
        }

        return collect($this->groupByBaseProduct($response->json() ?? []))
            ->map(fn (array $p) => $this->mapToRow($p))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function mapToRow(array $product): ApiProductRow
    {
        $descricao = (string) $product['descricao'];
        $categoriaInferida = $this->inferCategory($descricao);
        $baseCode = trim((string) ($product['codigoAmigavel'] ?? ''));
        $sku = $baseCode !== '' ? "XBZ-{$baseCode}" : (string) $product['codigoXbz'];

        return new ApiProductRow(
            sku: $sku,
            title: mb_substr((string) ($product['nome'] ?? $descricao), 0, 255),
            supplierCode: $baseCode !== '' ? $baseCode : (string) $product['codigoXbz'],
            category: $categoriaInferida,
            shortDescription: $descricao !== '' ? $descricao : null,
            technicalDescription: null,
            imageUrls: $this->buildImageUrlsWithColorHints($product),
        );
    }

    private function inferCategory(string $descricao): ?string
    {
        $text = Str::of($descricao)->lower()->ascii()->value();

        return match (true) {
            str_contains($text, 'caderno') => 'Cadernos',
            str_contains($text, 'caneta') => 'Canetas',
            str_contains($text, 'garrafa'), str_contains($text, 'squeeze') => 'Garrafas',
            str_contains($text, 'kit churrasco'), str_contains($text, 'churrasco') => 'Kits Churrasco',
            str_contains($text, 'kit queijo'), str_contains($text, 'queijo') => 'Kits Queijo',
            default => null,
        };
    }

    /**
     * @param  array<int, mixed>  $rawItems
     * @return array<int, array<string, mixed>>
     */
    private function groupByBaseProduct(array $rawItems): array
    {
        $grouped = [];

        foreach ($rawItems as $item) {
            if (! is_array($item)) {
                continue;
            }

            $base = trim((string) ($item['codigoAmigavel'] ?? ''));
            $groupKey = $base !== '' ? $base : trim((string) ($item['codigoXbz'] ?? ''));

            if ($groupKey === '') {
                continue;
            }

            if (! isset($grouped[$groupKey])) {
                $grouped[$groupKey] = $item;
                $grouped[$groupKey]['_variants'] = [$item];
                continue;
            }

            $grouped[$groupKey]['_variants'][] = $item;
        }

        return array_values($grouped);
    }

    /**
     * @param  array<string, mixed>  $product
     * @return string[]
     */
    private function buildImageUrlsWithColorHints(array $product): array
    {
        $variants = $product['_variants'] ?? [$product];
        $urls = [];

        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $imageLink = trim((string) ($variant['imageLink'] ?? ''));
            if ($imageLink === '') {
                continue;
            }

            $hint = $this->extractColorHint((string) ($variant['codigoComposto'] ?? ''));
            $urlWithHint = $hint ? "{$imageLink}#{$hint}" : $imageLink;
            $urls[] = $urlWithHint;
        }

        return array_values(array_unique($urls));
    }

    private function extractColorHint(string $codigoComposto): ?string
    {
        if ($codigoComposto === '') {
            return null;
        }

        if (! preg_match('/-([A-Z]{2,4})(?:\/([A-Z]{2,4}))?$/', Str::upper($codigoComposto), $matches)) {
            return null;
        }

        $tokens = array_filter([$matches[1] ?? null, $matches[2] ?? null]);

        foreach ($tokens as $token) {
            if (isset(self::COLOR_TOKEN_MAP[$token])) {
                return self::COLOR_TOKEN_MAP[$token];
            }
        }

        return null;
    }
}
