<?php

namespace App\Services\Suppliers;

use App\Services\Import\ApiProductRow;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AsiaImportClient
{
    private const BASE_URL = 'https://api.asiaimport.com.br/';

    private const PER_PAGE = 100;

    /**
     * @return ApiProductRow[]
     */
    public function fetchAll(): array
    {
        $apiKey = config('catalog.suppliers.asia.api_key');
        $secretKey = config('catalog.suppliers.asia.secret_key');

        if (empty($apiKey) || empty($secretKey)) {
            throw new RuntimeException('Credenciais da Asia Import não configuradas (ASIA_IMPORT_API_KEY / ASIA_IMPORT_SECRET_KEY).');
        }

        $rows = [];
        $page = 1;

        do {
            $response = Http::timeout(30)
                ->asForm()
                ->post(self::BASE_URL, [
                    'api_key' => $apiKey,
                    'secret_key' => $secretKey,
                    'funcao' => 'listarProdutos2',
                    'pagina' => $page,
                    'por_pagina' => self::PER_PAGE,
                    'status' => 'true',
                ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    "Asia Import API retornou HTTP {$response->status()} na página {$page}."
                );
            }

            $data = $response->json();
            $totalPages = (int) ($data['total_paginas'] ?? 1);

            foreach ($data['produtos'] ?? [] as $product) {
                $rows[] = $this->mapToRow($product);
            }

            $page++;
        } while ($page <= $totalPages);

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function mapToRow(array $product): ApiProductRow
    {
        $imageUrls = [];

        if (! empty($product['imagem'])) {
            $imageUrls[] = (string) $product['imagem'];
        }

        foreach ($product['galeria'] ?? [] as $item) {
            $url = is_array($item)
                ? ($item['url'] ?? $item['imagem'] ?? null)
                : $item;

            if (is_string($url) && $url !== '') {
                $imageUrls[] = $url;
            }
        }

        $category = null;
        $categorias = $product['categorias'] ?? [];

        if (is_array($categorias) && ! empty($categorias)) {
            $first = $categorias[0];
            $category = is_array($first)
                ? ($first['nome'] ?? null)
                : (is_string($first) ? $first : null);
        }

        return new ApiProductRow(
            sku: (string) $product['referencia'],
            title: (string) $product['nome'],
            supplierCode: (string) $product['referencia'],
            category: $category,
            shortDescription: ! empty($product['descricao']) ? (string) $product['descricao'] : null,
            technicalDescription: null,
            imageUrls: $imageUrls,
        );
    }
}
