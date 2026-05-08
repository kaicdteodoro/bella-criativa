<?php

namespace App\Filament\Pages;

use App\Models\ImportRun;
use App\Services\Import\ImportBatchResult;
use App\Services\Import\ImportCatalogRunner;
use App\Services\Import\SyncApiRunner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Collection;
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

    public ?array $importData = [];

    public ?array $apiSyncData = [];

    public function mount(): void
    {
        $this->importForm->fill([
            'dry_run' => false,
            'resume' => true,
        ]);

        $this->apiSyncForm->fill([
            'source' => (string) config('catalog.suppliers.default', 'xbz'),
            'categoria' => [],
            'busca' => null,
            'limit' => 50,
            'dry_run' => true,
        ]);
    }

    protected function getForms(): array
    {
        return [
            'importForm',
            'apiSyncForm',
        ];
    }

    public function importForm(Schema $schema): Schema
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
            ->statePath('importData');
    }

    public function apiSyncForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sincronizar via API')
                    ->description('Importe lotes da XBZ ou de outros fornecedores sem subir planilha.')
                    ->schema([
                        Select::make('source')
                            ->label('Fornecedor')
                            ->options([
                                'xbz' => 'XBZ',
                                'asia' => 'Asia Import',
                                'all' => 'Todos',
                            ])
                            ->required()
                            ->native(false),
                        Select::make('categoria')
                            ->label('Preset de categoria')
                            ->options($this->getApiSyncPresetOptions())
                            ->placeholder('Sem preset')
                            ->helperText('Você pode selecionar várias categorias. O limite será aplicado para cada uma.')
                            ->multiple()
                            ->searchable()
                            ->native(false),
                        TextInput::make('busca')
                            ->label('Busca livre')
                            ->placeholder('Ex.: kit vinho, caneca, speaker'),
                        TextInput::make('limit')
                            ->label('Limite de produtos')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Ex.: 50'),
                        Toggle::make('dry_run')
                            ->label('Rodar em dry-run')
                            ->helperText('Busca produtos e processa imagens sem gravar no banco.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('apiSyncData');
    }

    public function submitImport(ImportCatalogRunner $runner): void
    {
        $state = $this->importForm->getState();
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

            $this->notifyBatch($batch, 'Importação concluída');

            $this->importForm->fill([
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

    public function submitApiSync(SyncApiRunner $runner): void
    {
        $state = $this->apiSyncForm->getState();
        $source = (string) ($state['source'] ?? config('catalog.suppliers.default', 'xbz'));
        $categoryKeys = $this->normalizeApiSyncCategoryKeys($state['categoria'] ?? []);
        $search = trim((string) ($state['busca'] ?? ''));

        try {
            $batches = collect();

            if ($categoryKeys !== []) {
                foreach ($categoryKeys as $categoryKey) {
                    $batches->push($runner->run($source, [
                        'dry_run' => (bool) ($state['dry_run'] ?? false),
                        'limit' => filled($state['limit'] ?? null) ? (int) $state['limit'] : null,
                        'search_terms' => $this->resolveApiSyncSearchTerms($categoryKey, null),
                        'initiated_via' => 'admin',
                    ]));
                }
            } else {
                $batches->push($runner->run($source, [
                    'dry_run' => (bool) ($state['dry_run'] ?? false),
                    'limit' => filled($state['limit'] ?? null) ? (int) $state['limit'] : null,
                    'search_terms' => $this->resolveApiSyncSearchTerms([], $search),
                    'initiated_via' => 'admin',
                ]));
            }

            $batch = $this->mergeBatches($batches);
            $title = $categoryKeys !== []
                ? 'Sincronização em lote concluída'
                : 'Sincronização concluída';

            $this->notifyBatch($batch, $title);

            $this->apiSyncForm->fill([
                'source' => $source,
                'categoria' => $categoryKeys,
                'busca' => $state['busca'] ?? null,
                'limit' => $state['limit'] ?? 50,
                'dry_run' => false,
            ]);
        } catch (Throwable $exception) {
            Notification::make()
                ->title('A sincronização da API falhou')
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

    /**
     * @return array<string, string>
     */
    public function apiPresetOptions(): array
    {
        return $this->getApiSyncPresetOptions();
    }

    /**
     * @return string[]
     */
    public function selectedApiCategories(): array
    {
        return $this->normalizeApiSyncCategoryKeys($this->apiSyncData['categoria'] ?? []);
    }

    public function estimatedApiItems(): int
    {
        $limit = max(0, (int) ($this->apiSyncData['limit'] ?? 0));
        $categories = $this->selectedApiCategories();

        if ($categories === []) {
            return $limit;
        }

        return count($categories) * $limit;
    }

    public function fillSafeTest(): void
    {
        $this->apiSyncForm->fill([
            'source' => (string) config('catalog.suppliers.default', 'xbz'),
            'categoria' => ['canetas'],
            'busca' => null,
            'limit' => 10,
            'dry_run' => true,
        ]);
    }

    public function fillStarterBatch(): void
    {
        $this->apiSyncForm->fill([
            'source' => (string) config('catalog.suppliers.default', 'xbz'),
            'categoria' => ['canetas', 'cadernos', 'canecas', 'copos', 'garrafas', 'mochilas'],
            'busca' => null,
            'limit' => 50,
            'dry_run' => true,
        ]);
    }

    private function notifyBatch(ImportBatchResult $batch, string $title): void
    {
        $totals = $batch->totals();

        Notification::make()
            ->title($title)
            ->body("Criados: {$totals['created']} | Atualizados: {$totals['updated']} | Pulados: {$totals['skipped']} | Falhas: {$totals['failed']}")
            ->color($batch->failedCount() > 0 ? 'warning' : 'success')
            ->send();
    }

    /**
     * @param  Collection<int, ImportBatchResult>  $batches
     */
    private function mergeBatches(Collection $batches): ImportBatchResult
    {
        return new ImportBatchResult(
            totalRows: $batches->sum(fn (ImportBatchResult $batch): int => $batch->totalRows),
            results: $batches->flatMap(fn (ImportBatchResult $batch) => $batch->results)->values(),
        );
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

    /**
     * @return array<string, string>
     */
    private function getApiSyncPresetOptions(): array
    {
        return collect((array) config('catalog.api_sync.category_search_presets', []))
            ->keys()
            ->mapWithKeys(fn (string $key): array => [$key => str($key)->replace('-', ' ')->title()->value()])
            ->all();
    }

    /**
     * @return string[]
     */
    private function resolveApiSyncSearchTerms(mixed $categoria, mixed $busca): array
    {
        $search = trim((string) $busca);

        if ($search !== '') {
            return collect(explode(',', $search))
                ->map(fn (string $term): string => trim(mb_strtolower($term)))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (is_array($categoria)) {
            return [];
        }

        $categoryKey = trim(mb_strtolower((string) $categoria));

        if ($categoryKey === '') {
            return [];
        }

        return collect((array) config("catalog.api_sync.category_search_presets.{$categoryKey}", []))
            ->map(fn (mixed $term): string => trim(mb_strtolower((string) $term)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    private function normalizeApiSyncCategoryKeys(mixed $categoria): array
    {
        if (! is_array($categoria)) {
            $value = trim(mb_strtolower((string) $categoria));

            return $value !== '' ? [$value] : [];
        }

        return collect($categoria)
            ->map(fn (mixed $value): string => trim(mb_strtolower((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
