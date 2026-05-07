<?php

namespace App\Console\Commands;

use App\Models\ProductMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class GenerateMediaThumbs extends Command
{
    protected $signature = 'media:generate-thumbs {--dry-run : Mostra o que seria feito sem gravar}';
    protected $description = 'Gera thumbnails _sm.webp retroativos para registros de product_media sem thumb_file';

    public function handle(): int
    {
        $records = ProductMedia::whereNull('thumb_file')->get();

        if ($records->isEmpty()) {
            $this->info('Nenhum registro sem thumb_file encontrado.');
            return self::SUCCESS;
        }

        $this->info("Encontrados {$records->count()} registros sem thumbnail.");
        $dry = $this->option('dry-run');
        $manager = new ImageManager(new Driver());
        $done = 0;
        $failed = 0;

        foreach ($records as $media) {
            $srcPath = Storage::disk('public')->path($media->file);

            if (! file_exists($srcPath)) {
                $this->warn("  [SKIP] arquivo não encontrado: {$media->file}");
                $failed++;
                continue;
            }

            $thumbPath = preg_replace('/(-\d+)\.webp$/', '$1_sm.webp', $media->file);

            if ($thumbPath === $media->file) {
                $this->warn("  [SKIP] não foi possível derivar caminho de thumb: {$media->file}");
                $failed++;
                continue;
            }

            if ($dry) {
                $this->line("  [DRY] {$media->file} → {$thumbPath}");
                $done++;
                continue;
            }

            try {
                $thumb = (string) $manager->read($srcPath)->scaleDown(400, 500)->toWebp(75);
                Storage::disk('public')->put($thumbPath, $thumb);
                $media->update(['thumb_file' => $thumbPath]);
                $this->line("  [OK] {$thumbPath}");
                $done++;
            } catch (\Throwable $e) {
                $this->warn("  [ERRO] {$media->file}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Concluído — {$done} gerados, {$failed} falhas.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
