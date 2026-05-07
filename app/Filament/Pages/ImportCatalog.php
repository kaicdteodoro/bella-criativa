<?php

namespace App\Filament\Pages;

use App\Models\ImportRun;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportCatalogRunner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ImportCatalog extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Catálogo';

    protected static ?string $navigationLabel = 'Importar catálogo';

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'importar-catalogo';

    protected string $view = 'filament.pages.import-catalog';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'dry_run' => false,
            'resume' => true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Arquivo de importação')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Planilha')
                            ->disk('local')
                            ->directory('imports/catalog')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'text/plain',
                                'application/csv',
                            ])
                            ->storeFileNamesIn('original_filename')
                            ->required(),
                        TextInput::make('source')
                            ->label('Fornecedor')
                            ->placeholder('Ex.: Jaqmouse'),
                        TextInput::make('limit')
                            ->label('Limite de produtos')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Opcional'),
                        Toggle::make('dry_run')
                            ->label('Rodar em dry-run')
                            ->helperText('Valida planilha, download e imagens sem gravar produtos.'),
                        Toggle::make('resume')
                            ->label('Ignorar SKUs existentes')
                            ->helperText('Retoma importações sem reprocessar produtos já importados.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(ImportCatalogRunner $runner): void
    {
        $state = $this->form->getState();
        $relativePath = (string) ($state['file'] ?? '');

        if ($relativePath === '') {
            Notification::make()
                ->title('Selecione uma planilha para importar.')
                ->danger()
                ->send();

            return;
        }

        try {
            $batch = $runner->run(Storage::disk('local')->path($relativePath), [
                'dry_run' => (bool) ($state['dry_run'] ?? false),
                'resume' => (bool) ($state['resume'] ?? false),
                'limit' => filled($state['limit'] ?? null) ? (int) $state['limit'] : null,
                'source' => $state['source'] ?? null,
                'initiated_via' => 'admin',
                'user_id' => Auth::id(),
                'file_path' => $relativePath,
                'original_filename' => $state['original_filename'] ?? basename($relativePath),
            ]);

            $this->notifyBatch($batch);

            $this->form->fill([
                'source' => $state['source'] ?? null,
                'dry_run' => false,
                'resume' => true,
            ]);
        } catch (Throwable $exception) {
            Notification::make()
                ->title('A importação falhou')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ImportRun>
     */
    public function getRecentRunsProperty()
    {
        return ImportRun::query()
            ->with('user:id,name')
            ->latest('started_at')
            ->limit(5)
            ->get();
    }

    public function getHistoryRunsProperty(): LengthAwarePaginator
    {
        return ImportRun::query()
            ->with('user:id,name')
            ->latest('started_at')
            ->paginate(10);
    }

    public function downloadSummary(int $runId): StreamedResponse
    {
        $run = ImportRun::query()->with('user:id,name')->findOrFail($runId);
        $filename = sprintf('import-summary-%d.txt', $run->id);
        $summary = $this->buildRunSummary($run);

        return response()->streamDownload(function () use ($summary): void {
            echo $summary;
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function formatRunSummary(ImportRun $run): string
    {
        return $this->buildRunSummary($run);
    }

    private function notifyBatch(ImportBatchResult $batch): void
    {
        $totals = $batch->totals();

        Notification::make()
            ->title('Importação concluída')
            ->body("Criados: {$totals['created']} | Atualizados: {$totals['updated']} | Pulados: {$totals['skipped']} | Falhas: {$totals['failed']}")
            ->color($batch->failedCount() > 0 ? 'warning' : 'success')
            ->send();
    }

    private function buildRunSummary(ImportRun $run): string
    {
        $lines = [
            'Resumo da importação',
            'Execução: #'.$run->id,
            'Status: '.$run->status_label,
            'Iniciado em: '.$this->formatDate($run->started_at),
            'Finalizado em: '.$this->formatDate($run->finished_at),
            'Origem: '.($run->source ?: 'não informado'),
            'Arquivo: '.($run->original_filename ?? basename($run->file_path)),
            'Modo: '.($run->dry_run ? 'dry-run' : 'importação real'),
            'Resume: '.($run->resume ? 'sim' : 'não'),
            'Limite: '.($run->limit ?: 'sem limite'),
            'Iniciado via: '.$run->initiated_via,
            'Operador: '.($run->user?->name ?: 'sistema'),
            '',
            'Totais',
            '- Total: '.$run->total_rows,
            '- Criados: '.$run->created_count,
            '- Atualizados: '.$run->updated_count,
            '- Pulados: '.$run->skipped_count,
            '- Falhas: '.$run->failed_count,
        ];

        if (filled($run->summary['message'] ?? null)) {
            $lines[] = '';
            $lines[] = 'Mensagem: '.$run->summary['message'];
        }

        if (filled($run->failed_items)) {
            $lines[] = '';
            $lines[] = 'SKUs com falha:';

            foreach ($run->failed_items as $item) {
                $lines[] = sprintf(
                    '- %s: %s',
                    (string) ($item['sku'] ?? 'sem-sku'),
                    (string) ($item['reason'] ?? 'Falha sem detalhe.')
                );
            }
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function formatDate(?Carbon $value): string
    {
        return $value?->format('d/m/Y H:i:s') ?? '-';
    }
}
