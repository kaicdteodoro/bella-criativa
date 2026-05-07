<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditMedia extends Command
{
    protected $signature = 'media:audit {--heavy=100 : Flagga imagens acima de X KB} {--csv= : Exporta para CSV no caminho indicado}';
    protected $description = 'Auditoria de mídia: peso, thumbnails, OG e case duplicado por SKU';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $heavyKb = (int) $this->option('heavy');
        $csvPath = $this->option('csv');

        $products = Product::with('media')->get();
        $rows = [];

        foreach ($products as $product) {
            $safeSku = $this->sanitize($product->sku);
            $dir = "media/{$safeSku}";

            $files = $disk->files($dir);
            $totalKb = round(collect($files)->sum(fn ($f) => $disk->size($f)) / 1024);
            $hasOg = $disk->exists("{$dir}/{$safeSku}-og.webp");
            $hasThumbs = $product->media->every(fn ($m) => $m->thumb_file !== null);
            $hasColorHex = $product->media->every(fn ($m) => $m->color_hex !== null);

            $caseDuplicate = $disk->exists('media/' . strtolower($safeSku))
                && strtolower($safeSku) !== $safeSku;

            $rows[] = [
                'sku' => $product->sku,
                'status' => $product->status,
                'images' => $product->media->count(),
                'total_kb' => $totalKb,
                'heavy' => $totalKb > $heavyKb ? 'SIM' : '',
                'has_og' => $hasOg ? 'SIM' : 'NÃO',
                'has_thumbs' => $hasThumbs ? 'SIM' : 'NÃO',
                'color_hex_ok' => $hasColorHex ? 'SIM' : 'NÃO',
                'case_dup' => $caseDuplicate ? 'SIM' : '',
            ];
        }

        $headers = ['SKU', 'Status', 'Imagens', 'Total KB', 'Pesada?', 'OG?', 'Thumbs?', 'Color hex?', 'Case dup?'];

        $this->table($headers, array_map(fn ($r) => array_values($r), $rows));

        $heavy = array_filter($rows, fn ($r) => $r['heavy'] === 'SIM');
        $noThumb = array_filter($rows, fn ($r) => $r['has_thumbs'] === 'NÃO');
        $noOg = array_filter($rows, fn ($r) => $r['has_og'] === 'NÃO');
        $caseDup = array_filter($rows, fn ($r) => $r['case_dup'] === 'SIM');

        $this->newLine();
        $this->line("Produtos: " . count($rows));
        $this->line("Pesados (>{$heavyKb}KB): " . count($heavy));
        $this->line("Sem thumbnail: " . count($noThumb));
        $this->line("Sem OG: " . count($noOg));
        $this->line("Case duplicado: " . count($caseDup));

        if ($csvPath) {
            $fp = fopen($csvPath, 'w');
            fputcsv($fp, $headers);
            foreach ($rows as $row) {
                fputcsv($fp, array_values($row));
            }
            fclose($fp);
            $this->newLine();
            $this->info("CSV exportado: {$csvPath}");
        }

        return self::SUCCESS;
    }

    private function sanitize(string $sku): string
    {
        $normalized = preg_replace('/[^A-Z0-9]+/', '-', strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $sku) ?: $sku));
        return trim($normalized, '-') ?: 'SKU-' . substr(sha1($sku), 0, 8);
    }
}
