<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Import\ProductUpsert;
use Illuminate\Console\Command;

class AutoFlagProducts extends Command
{
    protected $signature = 'catalog:auto-flag
                            {--dry-run : Mostra o que seria alterado sem salvar}';

    protected $description = 'Infere is_launch e is_premium para produtos existentes sem flag manual';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Lançamentos: produtos criados nos últimos 60 dias ainda sem flag
        $launches = Product::published()
            ->where('is_launch', false)
            ->where('created_at', '>=', now()->subDays(60))
            ->get();

        $this->info("Lançamentos recentes sem flag: {$launches->count()}");

        if (! $dry) {
            Product::published()
                ->where('is_launch', false)
                ->where('created_at', '>=', now()->subDays(60))
                ->update(['is_launch' => true]);
        }

        // Premium: varredura por palavra-chave em todos os publicados sem flag
        $premiumCount = 0;

        Product::published()
            ->where('is_premium', false)
            ->with('categories:id,name')
            ->chunkById(200, function ($products) use ($dry, &$premiumCount) {
                foreach ($products as $product) {
                    $category = $product->categories->first()?->name;

                    if (ProductUpsert::detectPremium($product->title, $product->short_description, $category)) {
                        $premiumCount++;

                        if (! $dry) {
                            $product->update(['is_premium' => true]);
                        }
                    }
                }
            });

        $this->info("Premium detectados: {$premiumCount}");

        if ($dry) {
            $this->warn('Modo dry-run — nenhuma alteração salva.');
        }

        return self::SUCCESS;
    }
}
