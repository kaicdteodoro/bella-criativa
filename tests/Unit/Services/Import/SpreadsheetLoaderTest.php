<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\Exceptions\SpreadsheetException;
use App\Services\Import\SpreadsheetLoader;
use Tests\TestCase;

class SpreadsheetLoaderTest extends TestCase
{
    public function test_it_loads_rows_from_a_spreadsheet_fixture(): void
    {
        $loader = new SpreadsheetLoader();

        $rows = $loader->load(base_path('tests/Fixtures/import/products.csv'));

        $this->assertCount(2, $rows);
        $this->assertSame('SKU-001', $rows[0]->sku);
        $this->assertSame('Kit Churrasco Prime', $rows[0]->title);
        $this->assertSame('Kits Churrasco', $rows[0]->category);
        $this->assertSame('https://example.com/kit.zip', $rows[0]->imagesZipUrl);
    }

    public function test_it_throws_when_required_headers_are_missing(): void
    {
        $loader = new SpreadsheetLoader();

        $this->expectException(SpreadsheetException::class);
        $this->expectExceptionMessage('codigo_sku');

        $loader->load(base_path('tests/Fixtures/import/products-missing-header.csv'));
    }
}
