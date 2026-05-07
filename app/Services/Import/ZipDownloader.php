<?php

namespace App\Services\Import;

use App\Services\Import\Exceptions\DownloadException;
use Illuminate\Support\Facades\Http;

class ZipDownloader
{
    public function download(string $sku, string $url): string
    {
        $timeout = (int) config('catalog.import.timeout', 30);
        $maxAttempts = max(1, (int) config('catalog.import.max_attempts', 3));

        $lastError = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout($timeout)->get($url);

                if (! $response->successful()) {
                    throw new DownloadException("HTTP {$response->status()} ao baixar ZIP.");
                }

                $tempPath = tempnam(sys_get_temp_dir(), 'catalog_');

                if ($tempPath === false) {
                    throw new DownloadException('Não foi possível criar arquivo temporário.');
                }

                file_put_contents($tempPath, $response->body());

                return $tempPath;
            } catch (\Throwable $exception) {
                $lastError = $exception;

                if ($attempt < $maxAttempts) {
                    usleep(500000 * $attempt);
                }
            }
        }

        throw new DownloadException(
            sprintf(
                'Falha ao baixar ZIP do SKU %s após %d tentativas: %s',
                $sku,
                $maxAttempts,
                $lastError?->getMessage() ?? 'erro desconhecido',
            ),
            previous: $lastError,
        );
    }
}
