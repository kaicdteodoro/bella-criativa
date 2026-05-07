<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PruneOrphanMedia extends Command
{
    protected $signature = 'media:prune-orphans {--dry-run : Mostra o que seria removido sem deletar}';
    protected $description = 'Remove pastas de SKU em storage/media sem produto correspondente no banco';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $allDirs = collect($disk->directories('media'));

        if ($allDirs->isEmpty()) {
            $this->info('Nenhuma pasta em storage/media.');
            return self::SUCCESS;
        }

        $usedPaths = Product::query()
            ->whereNotNull('featured_image')
            ->pluck('featured_image')
            ->merge(ProductMedia::query()->pluck('file'))
            ->filter()
            ->map(fn (string $path) => trim(Str::beforeLast($path, '/'), '/'))
            ->filter()
            ->unique()
            ->values();

        $orphans = $allDirs->filter(fn ($dir) => ! $usedPaths->contains($dir));

        if ($orphans->isEmpty()) {
            $this->info("Nenhuma pasta órfã encontrada ({$allDirs->count()} pastas, todas referenciadas).");
            return self::SUCCESS;
        }

        $dry = $this->option('dry-run');
        $this->info(($dry ? '[DRY-RUN] ' : '')."Encontradas {$orphans->count()} pastas órfãs de {$allDirs->count()} totais.");

        $removed = 0;
        $freed = 0;

        foreach ($orphans as $dir) {
            $files = $disk->files($dir);
            $bytes = collect($files)->sum(fn ($f) => $disk->size($f));
            $freed += $bytes;

            if ($dry) {
                $this->line("  [DRY] {$dir} (" . count($files) . " arquivos, " . round($bytes / 1024) . "KB)");
            } else {
                $disk->deleteDirectory($dir);
                $this->line("  [DEL] {$dir} (" . count($files) . " arquivos, " . round($bytes / 1024) . "KB)");
            }
            $removed++;
        }

        $this->newLine();
        $action = $dry ? 'a liberar' : 'liberados';
        $this->info("Total: {$removed} pastas {$action} — " . round($freed / 1024 / 1024, 1) . "MB.");

        return self::SUCCESS;
    }
}
