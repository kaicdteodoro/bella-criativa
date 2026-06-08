<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeDraftProducts extends Command
{
    protected $signature = 'catalog:purge-drafts
        {--dry-run : Mostra o que seria removido sem deletar}
        {--force : Executa sem pedir confirmação}';

    protected $description = 'Remove produtos em rascunho (status draft) e seus arquivos de mídia.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $total = Product::query()->where('status', 'draft')->count();

        if ($total === 0) {
            $this->info('Nenhum produto em draft encontrado.');
            return self::SUCCESS;
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("Modo dry-run: {$total} produto(s) em draft seriam removidos.");
            $this->listDrafts();
            return self::SUCCESS;
        }

        $this->warn("ATENÇÃO: {$total} produto(s) em draft serão removidos permanentemente.");

        if (! $this->option('force') && ! $this->confirm('Confirma a remoção?', false)) {
            $this->line('Operação cancelada.');
            return self::SUCCESS;
        }

        $deleted = 0;

        Product::query()
            ->where('status', 'draft')
            ->chunkById(100, function ($products) use (&$deleted): void {
                foreach ($products as $product) {
                    $this->deleteMediaFiles($product->sku);
                    $deleted++;
                }
                Product::query()->whereIn('id', $products->pluck('id'))->delete();
            });

        $this->info("  ✓ {$deleted} produto(s) em draft removido(s)");

        $pruned = Category::query()->doesntHave('products')->count();
        if ($pruned > 0) {
            Category::query()->doesntHave('products')->delete();
            $this->info("  ✓ {$pruned} categoria(s) vazia(s) removida(s)");
        }

        $this->newLine();
        $remaining = Product::query()->where('status', 'published')->count();
        $this->info("Catálogo publicado: {$remaining} produto(s)");

        return self::SUCCESS;
    }

    private function listDrafts(): void
    {
        Product::query()
            ->where('status', 'draft')
            ->select(['sku', 'title', 'quality_score'])
            ->orderBy('sku')
            ->chunkById(200, function ($products): void {
                foreach ($products as $product) {
                    $score = $product->quality_score !== null ? " (score: {$product->quality_score})" : '';
                    $this->line("  – [{$product->sku}] {$product->title}{$score}");
                }
            });
    }

    private function deleteMediaFiles(string $sku): void
    {
        $safeSku = preg_replace('/[^A-Za-z0-9\-_]/', '_', $sku) ?? $sku;
        $dir = "media/{$safeSku}";

        if (Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->deleteDirectory($dir);
        }
    }
}
