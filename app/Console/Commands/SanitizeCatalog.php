<?php

namespace App\Console\Commands;

use App\Services\Import\SanitizeImportedProductContent;
use Illuminate\Console\Command;

class SanitizeCatalog extends Command
{
    protected $signature = 'catalog:sanitize
        {--source=all : Fornecedor (xbz, asia, all)}
        {--limit= : Processar só os primeiros N produtos}
        {--dry-run : Mostra o que seria alterado sem salvar}';

    protected $description = 'Remove textos B2B (OBS, pedido mínimo, atacado) das descrições públicas dos produtos.';

    public function handle(SanitizeImportedProductContent $sanitizer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhuma alteração será salva.');
        }

        $result = $sanitizer->run([
            'source'  => $this->option('source') ?? 'all',
            'limit'   => $this->option('limit'),
            'dry_run' => $dryRun,
        ]);

        $this->newLine();
        $this->line("Escaneados:  {$result->scanned}");
        $this->line("Atualizados: {$result->updated}");
        $this->line("Sem mudança: {$result->unchanged}");

        if ($result->samples !== []) {
            $this->newLine();
            $this->line('Amostras:');
            foreach ($result->samples as $sample) {
                $fields = implode(', ', $sample['changes']);
                $this->line("  [{$sample['sku']}] → {$fields}");
            }
        }

        return self::SUCCESS;
    }
}
