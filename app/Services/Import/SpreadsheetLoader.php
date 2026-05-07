<?php

namespace App\Services\Import;

use App\Services\Import\Exceptions\SpreadsheetException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use SplFileObject;

class SpreadsheetLoader
{
    private const REQUIRED_HEADERS = [
        'post_title',
        'codigo_sku',
        'imagens_zip_url',
    ];

    /**
     * @return ProductRow[]
     */
    public function load(string $path): array
    {
        $rows = $this->readRows($path);

        if ($rows === []) {
            throw new SpreadsheetException('A planilha está vazia.');
        }

        $headerRow = array_shift($rows);
        $headers = $this->normalizeHeaders($headerRow);
        $this->assertRequiredHeaders($headers);

        $mappedRows = [];

        foreach ($rows as $row) {
            $mapped = $this->combineRow($headers, $row);

            if ($this->isBlankRow($mapped)) {
                continue;
            }

            $mappedRows[] = new ProductRow(
                sku: trim((string) ($mapped['codigo_sku'] ?? '')),
                title: trim((string) ($mapped['post_title'] ?? '')),
                supplierCode: $this->nullableString($mapped['codigo_fornecedor'] ?? null),
                category: $this->nullableString($mapped['categoria'] ?? null),
                shortDescription: $this->nullableString($mapped['descricao_curta'] ?? null),
                technicalDescription: $this->nullableString($mapped['descricao_tecnica'] ?? null),
                imagesZipUrl: trim((string) ($mapped['imagens_zip_url'] ?? '')),
            );
        }

        return $mappedRows;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readRows(string $path): array
    {
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsvRows($path);
        }

        $sheets = Excel::toArray(new class
        {
        }, $path);

        return $sheets[0] ?? [];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readCsvRows(string $path): array
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl(',');

        $rows = [];

        foreach ($file as $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return string[]
     */
    private function normalizeHeaders(array $headerRow): array
    {
        return Collection::make($headerRow)
            ->map(fn ($header) => Str::of((string) $header)->trim()->lower()->value())
            ->values()
            ->all();
    }

    /**
     * @param  string[]  $headers
     */
    private function assertRequiredHeaders(array $headers): void
    {
        $missing = array_values(array_diff(self::REQUIRED_HEADERS, $headers));

        if ($missing !== []) {
            throw new SpreadsheetException(
                'Colunas obrigatórias ausentes: '.implode(', ', $missing)
            );
        }
    }

    /**
     * @param  string[]  $headers
     * @param  array<int, mixed>  $row
     * @return array<string, mixed>
     */
    private function combineRow(array $headers, array $row): array
    {
        $values = array_pad($row, count($headers), null);

        /** @var array<string, mixed> $combined */
        $combined = array_combine($headers, $values);

        return $combined;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isBlankRow(array $row): bool
    {
        $interestingColumns = [
            'post_title',
            'codigo_sku',
            'imagens_zip_url',
        ];

        foreach ($interestingColumns as $column) {
            if (trim((string) ($row[$column] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
