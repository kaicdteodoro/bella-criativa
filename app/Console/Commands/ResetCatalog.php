<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ResetCatalog extends Command
{
    protected $signature = 'catalog:reset
        {--force : Executa sem pedir confirmação}
        {--keep-categories : Mantém as categorias cadastradas}
        {--keep-history : Mantém o histórico de importações}';

    protected $description = 'Remove todos os produtos, mídias e (opcionalmente) categorias do catálogo.';

    public function handle(): int
    {
        $this->newLine();
        $this->warn('ATENÇÃO: esta operação é irreversível.');
        $this->line('Serão removidos: produtos, imagens em disco, registros de mídia e pivot de categorias.');

        if (! $this->option('force') && ! $this->confirm('Confirma o reset completo do catálogo?', false)) {
            $this->line('Operação cancelada.');
            return self::SUCCESS;
        }

        $productCount = Product::count();
        $this->line("Removendo {$productCount} produto(s) e arquivos de mídia...");

        $deleted = 0;
        Product::query()->chunkById(100, function ($products) use (&$deleted): void {
            foreach ($products as $product) {
                $this->deleteMediaFiles($product->sku);
                $deleted++;
            }
            Product::query()->whereIn('id', $products->pluck('id'))->delete();
        });

        $this->info("  ✓ {$deleted} produto(s) removido(s)");

        if (! $this->option('keep-categories')) {
            $catCount = Category::query()->doesntHave('products')->count();
            Category::query()->doesntHave('products')->delete();
            $this->info("  ✓ {$catCount} categoria(s) vazia(s) removida(s)");
        }

        if (! $this->option('keep-history')) {
            \App\Models\ImportRun::query()->delete();
            $this->info('  ✓ Histórico de importações limpo');
        }

        $this->newLine();
        $this->info('Catálogo resetado. Rode catalog:sync-curated para repovoar.');

        return self::SUCCESS;
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
