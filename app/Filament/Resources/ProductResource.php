<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $modelLabel = 'produto';

    protected static ?string $pluralModelLabel = 'produtos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Produto')
                ->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old) {
                            $slug = $get('slug') ?? '';
                            if ($slug === '' || $slug === Str::slug((string) $old)) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Identificador único do produto. Evite alterar após a publicação.'),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Gerado automaticamente pelo título. Mantenha estável após publicação.'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft'     => 'Rascunho',
                            'published' => 'Publicado',
                        ])
                        ->default('draft')
                        ->live()
                        ->required(),
                    Select::make('curation_status')
                        ->label('Curadoria')
                        ->options([
                            'raw'       => 'Bruto',
                            'processed' => 'Processado',
                            'ready'     => 'Pronto',
                            'blocked'   => 'Bloqueado',
                        ])
                        ->default('raw')
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('short_description')
                        ->label('Descrição curta')
                        ->rows(4)
                        ->helperText('Resumo exibido nas listagens e no SEO.')
                        ->required(fn (Get $get): bool => $get('status') === 'published')
                        ->columnSpanFull(),
                    RichEditor::make('technical_description')
                        ->label('Descrição técnica')
                        ->columnSpanFull(),
                    FileUpload::make('featured_image')
                        ->label('Imagem destacada')
                        ->disk('public')
                        ->directory('media/featured')
                        ->helperText('Obrigatória para produto publicado sem imagens na galeria.')
                        ->required(fn (Get $get): bool => $get('status') === 'published' && count((array) ($get('media') ?? [])) === 0),
                    FileUpload::make('og_image')
                        ->label('Imagem OG (social)')
                        ->disk('public')
                        ->directory('media/og')
                        ->helperText('Opcional — usa a imagem destacada como fallback.'),
                    TagsInput::make('available_colors')
                        ->label('Cores disponíveis')
                        ->helperText('Valores hex, ex.: #000000')
                        ->placeholder('#000000')
                        ->nestedRecursiveRules(['regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']),
                    TagsInput::make('materials')
                        ->label('Materiais')
                        ->helperText('Ex.: metal, madeira, plástico'),
                    TextInput::make('supplier_code')
                        ->label('Código do fornecedor')
                        ->maxLength(255),
                    TextInput::make('source_supplier')
                        ->label('Fornecedor origem')
                        ->maxLength(255),
                    CheckboxList::make('categories')
                        ->label('Categorias')
                        ->relationship('categories', 'name')
                        ->helperText('Obrigatório para produtos publicados.')
                        ->required(fn (Get $get): bool => $get('status') === 'published')
                        ->columns(2)
                        ->searchable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Visibilidade')
                ->description('Controla onde o produto aparece no site.')
                ->schema([
                    Toggle::make('is_featured')
                        ->label('Destaque na home')
                        ->helperText('Aparece na seção curada da home quando o produto estiver publicado.'),
                    Toggle::make('is_launch')
                        ->label('Lançamento')
                        ->helperText('Aparece na página de Lançamentos quando o produto estiver publicado.'),
                    Toggle::make('is_premium')
                        ->label('Linha Premium')
                        ->helperText('Aparece na página Linha Premium quando o produto estiver publicado.'),
                ])
                ->columns(3),

            Section::make('Galeria')
                ->schema([
                    Repeater::make('media')
                        ->relationship()
                        ->label('Imagens da galeria')
                        ->reorderableWithButtons()
                        ->orderColumn('order')
                        ->itemLabel(fn (array $state): ?string => static::mediaItemLabel($state))
                        ->schema([
                            FileUpload::make('file')
                                ->label('Arquivo')
                                ->disk('public')
                                ->directory('media/gallery')
                                ->required(),
                            TextInput::make('color_hex')
                                ->label('Cor (hex)')
                                ->maxLength(7)
                                ->placeholder('#000000')
                                ->helperText('Opcional — vincula a imagem a um seletor de cor.'),
                            TextInput::make('order')
                                ->label('Ordem')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Curadoria')
                ->collapsed()
                ->schema([
                    Placeholder::make('checklist')
                        ->label('Checklist de publicação')
                        ->content(fn (Get $get): string => static::publicationChecklist($get))
                        ->columnSpanFull(),
                    Placeholder::make('quality_score')
                        ->label('Score de qualidade')
                        ->content(fn (Get $get): string => (string) ($get('quality_score') ?? 'Não calculado')),
                    Placeholder::make('processed_at')
                        ->label('Último processamento')
                        ->content(fn (Get $get): string => filled($get('processed_at')) ? (string) $get('processed_at') : 'Não processado'),
                    Placeholder::make('quality_notes')
                        ->label('Sinais da curadoria')
                        ->content(fn (Get $get): string => static::formatQualityNotes($get))
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected static function mediaItemLabel(array $state): ?string
    {
        $file  = (string) ($state['file'] ?? '');
        $order = (int) ($state['order'] ?? 0);

        return $file !== '' ? "Imagem {$order}: " . basename($file) : "Imagem {$order}";
    }

    protected static function publicationChecklist(Get $get): string
    {
        $hasCategory      = count((array) ($get('categories') ?? [])) > 0;
        $hasFeatured      = filled($get('featured_image'));
        $hasGallery       = count((array) ($get('media') ?? [])) > 0;
        $hasShortDesc     = filled($get('short_description'));

        $checks = [
            ($hasCategory ? '✓' : '✗') . ' Categoria selecionada',
            (($hasFeatured || $hasGallery) ? '✓' : '✗') . ' Imagem destacada ou galeria preenchida',
            ($hasShortDesc ? '✓' : '✗') . ' Descrição curta preenchida',
        ];

        return implode(PHP_EOL, $checks);
    }

    protected static function formatQualityNotes(Get $get): string
    {
        $notes = array_values(array_filter((array) ($get('quality_notes') ?? [])));

        return $notes !== [] ? implode(PHP_EOL, $notes) : 'Sem observações registradas.';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        default     => 'gray',
                    }),
                TextColumn::make('categories.name')
                    ->label('Categorias')
                    ->listWithLineBreaks()
                    ->limitList(2),
                ToggleColumn::make('is_featured')->label('Home'),
                ToggleColumn::make('is_launch')->label('Lançamento'),
                ToggleColumn::make('is_premium')->label('Premium'),
                TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Rascunho',
                        'published' => 'Publicado',
                    ]),
                TernaryFilter::make('is_featured')->label('Destaque na home'),
                TernaryFilter::make('is_launch')->label('Lançamento'),
                TernaryFilter::make('is_premium')->label('Premium'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_launch')
                        ->label('Marcar como Lançamento')
                        ->icon('heroicon-o-sparkles')
                        ->action(fn (Collection $records) => $records->each->update(['is_launch' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unmark_launch')
                        ->label('Remover de Lançamentos')
                        ->icon('heroicon-o-minus-circle')
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['is_launch' => false]))
                        ->deselectRecordsAfterCompletion(),
                ])->label('Lançamentos'),
                BulkActionGroup::make([
                    BulkAction::make('mark_premium')
                        ->label('Marcar como Premium')
                        ->icon('heroicon-o-star')
                        ->action(fn (Collection $records) => $records->each->update(['is_premium' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unmark_premium')
                        ->label('Remover de Premium')
                        ->icon('heroicon-o-minus-circle')
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['is_premium' => false]))
                        ->deselectRecordsAfterCompletion(),
                ])->label('Premium'),
                BulkActionGroup::make([
                    BulkAction::make('mark_featured')
                        ->label('Marcar como Destaque')
                        ->icon('heroicon-o-home')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unmark_featured')
                        ->label('Remover do Destaque')
                        ->icon('heroicon-o-minus-circle')
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => false]))
                        ->deselectRecordsAfterCompletion(),
                ])->label('Home'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
