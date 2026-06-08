<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class PromoteCatalog extends Command
{
    protected $signature = 'catalog:promote
        {--dry-run : Mostra o que seria publicado sem salvar}
        {--limit= : Processar só os primeiros N produtos}';

    protected $description = 'Reavalia produtos em draft e publica os que atendem os critérios de qualidade.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit  = filled($this->option('limit')) ? max(1, (int) $this->option('limit')) : null;

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhuma alteração será salva.');
        }

        $query = Product::query()
            ->where('status', 'draft')
            ->with('categories')
            ->orderBy('quality_score', 'desc')
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $scanned  = 0;
        $promoted = 0;
        $blocked  = 0;

        $query->each(function (Product $product) use (&$scanned, &$promoted, &$blocked, $dryRun): void {
            $scanned++;

            $reasons = $this->blockingReasons($product);

            if ($reasons !== []) {
                $blocked++;
                return;
            }

            $promoted++;

            if ($dryRun) {
                $this->line("  [{$product->sku}] score={$product->quality_score} → seria publicado");
                return;
            }

            $product->forceFill([
                'status'          => 'published',
                'curation_status' => 'ready',
            ])->save();
        });

        $this->newLine();
        $this->line("Escaneados: {$scanned}");
        $this->info("Publicados: {$promoted}");
        $this->warn("Bloqueados: {$blocked}");

        if (! $dryRun && $promoted > 0) {
            $this->newLine();
            $total = Product::query()->where('status', 'published')->count();
            $this->info("Catálogo publicado agora: {$total} produto(s).");
        }

        return self::SUCCESS;
    }

    /** @return string[] */
    private function blockingReasons(Product $product): array
    {
        $reasons = [];

        if (mb_strlen((string) $product->title) < 8) {
            $reasons[] = 'título muito curto';
        }

        if ($product->categories->isEmpty()) {
            $reasons[] = 'sem categoria';
        }

        if (blank($product->short_description)) {
            $reasons[] = 'sem descrição curta';
        }

        if (blank($product->featured_image)) {
            $reasons[] = 'sem imagem';
        }

        if (blank($product->slug)) {
            $reasons[] = 'slug inválido';
        }

        return $reasons;
    }
}
