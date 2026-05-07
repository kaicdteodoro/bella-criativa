<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\PageResource\Pages\ListPages;
use App\Models\Page;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?string $navigationLabel = 'Páginas';

    protected static ?string $modelLabel = 'página';

    protected static ?string $pluralModelLabel = 'páginas';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Página')
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
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Slugs fixos do site: home, sobre, contato, lancamentos, linha-premium.'),
                    Select::make('template')
                        ->label('Template')
                        ->options([
                            'home' => 'Home',
                            'about' => 'Sobre',
                            'contact' => 'Contato',
                            'launches' => 'Lançamentos',
                            'premium' => 'Linha Premium',
                            'default' => 'Padrão',
                        ])
                        ->live()
                        ->helperText('Define quais tipos de bloco podem ser usados na seção abaixo.')
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft'     => 'Rascunho',
                            'published' => 'Publicado',
                        ])
                        ->default('draft')
                        ->required(),
                    Textarea::make('excerpt')->label('Resumo')->rows(3)->columnSpanFull(),
                    RichEditor::make('body')->label('Corpo principal')->columnSpanFull(),
                    FileUpload::make('hero_image')->label('Imagem hero')->disk('public')->directory('media/pages'),
                    TextInput::make('seo_title')->label('SEO — Título')->maxLength(255),
                    Textarea::make('seo_description')->label('SEO — Descrição')->rows(3),
                ])
                ->columns(2),
            Section::make('Seções')
                ->schema([
                    Repeater::make('sections')
                        ->relationship()
                        ->label('Blocos da página')
                        ->collapsed()
                        ->cloneable()
                        ->reorderableWithButtons()
                        ->orderColumn('position')
                        ->itemLabel(fn (array $state): ?string => static::buildSectionItemLabel($state))
                        ->schema(static::sectionSchema())
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected static function sectionSchema(): array
    {
        return [
            Select::make('type')
                ->label('Tipo do bloco')
                ->options(fn (Get $get): array => static::allowedTypeOptionsForTemplate(
                    static::normalizeTemplate((string) $get('../../template'))
                ))
                ->required()
                ->live()
                ->helperText('Escolha o tipo de bloco com base no template da página.'),
            TextInput::make('heading')
                ->label('Título do bloco')
                ->maxLength(255)
                ->helperText('Titulo exibido para o visitante. Alguns blocos usam este campo como titulo principal.'),
            Textarea::make('content.text')
                ->label('Texto de apoio')
                ->rows(3)
                ->columnSpanFull()
                ->visible(fn (Get $get): bool => in_array((string) $get('type'), ['hero', 'rich_text', 'category_mosaic', 'featured_products', 'cta'], true)),
            TextInput::make('content.eyebrow')
                ->label('Eyebrow')
                ->maxLength(120)
                ->visible(fn (Get $get): bool => (string) $get('type') === 'hero'),
            TextInput::make('content.primary_label')
                ->label('CTA primario - texto')
                ->maxLength(80)
                ->visible(fn (Get $get): bool => in_array((string) $get('type'), ['hero', 'cta'], true)),
            TextInput::make('content.primary_url')
                ->label('CTA primario - URL')
                ->helperText('Use URL absoluta ou rota interna (ex.: /produtos).')
                ->maxLength(255)
                ->visible(fn (Get $get): bool => in_array((string) $get('type'), ['hero', 'cta'], true)),
            TextInput::make('content.secondary_label')
                ->label('CTA secundario - texto')
                ->maxLength(80)
                ->visible(fn (Get $get): bool => (string) $get('type') === 'hero'),
            TextInput::make('content.secondary_url')
                ->label('CTA secundario - URL')
                ->helperText('Use URL absoluta ou rota interna (ex.: /contato).')
                ->maxLength(255)
                ->visible(fn (Get $get): bool => (string) $get('type') === 'hero'),
            Select::make('content.categories')
                ->label('Categorias destacadas')
                ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'slug')->all())
                ->multiple()
                ->searchable()
                ->visible(fn (Get $get): bool => (string) $get('type') === 'category_mosaic')
                ->helperText('Opcional. Se vazio, o bloco usa as primeiras categorias cadastradas.'),
            Placeholder::make('preview')
                ->label('Pré-visualização')
                ->content(fn (Get $get): string => static::buildSectionPreviewText($get))
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label('Bloco ativo')
                ->default(true),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function sectionTypeOptions(): array
    {
        return [
            'hero' => 'Hero',
            'rich_text' => 'Texto rico',
            'category_mosaic' => 'Mosaico de categorias',
            'featured_products' => 'Produtos em destaque',
            'cta' => 'Chamada para ação',
        ];
    }

    protected static function normalizeTemplate(string $template): string
    {
        return in_array($template, ['home', 'about', 'contact', 'launches', 'premium', 'default'], true)
            ? $template
            : 'default';
    }

    /**
     * @return array<string, string>
     */
    protected static function allowedTypeOptionsForTemplate(string $template): array
    {
        $allowedByTemplate = [
            'home' => ['hero', 'rich_text', 'category_mosaic', 'featured_products', 'cta'],
            'about' => ['hero', 'rich_text', 'cta'],
            'contact' => ['hero', 'rich_text', 'cta'],
            'launches' => ['hero', 'rich_text', 'featured_products', 'cta'],
            'premium' => ['hero', 'rich_text', 'category_mosaic', 'featured_products', 'cta'],
            'default' => ['rich_text', 'cta'],
        ];

        $allowed = $allowedByTemplate[$template] ?? $allowedByTemplate['default'];

        return collect(static::sectionTypeOptions())
            ->only($allowed)
            ->all();
    }

    protected static function buildSectionPreviewText(Get $get): string
    {
        $type = (string) $get('type');
        $heading = trim((string) $get('heading'));
        $text = trim((string) $get('content.text'));
        $primaryLabel = trim((string) $get('content.primary_label'));
        $primaryUrl = trim((string) $get('content.primary_url'));

        $parts = [
            'Tipo: '.($type !== '' ? $type : 'nao definido'),
            'Titulo: '.($heading !== '' ? $heading : 'sem titulo'),
        ];

        if ($text !== '') {
            $parts[] = 'Texto: '.mb_strimwidth(strip_tags($text), 0, 140, '...');
        }

        if ($primaryLabel !== '' || $primaryUrl !== '') {
            $parts[] = 'CTA: '.trim($primaryLabel.' '.$primaryUrl);
        }

        return implode(PHP_EOL, $parts);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected static function buildSectionItemLabel(array $state): ?string
    {
        $type = (string) ($state['type'] ?? '');
        $heading = trim((string) ($state['heading'] ?? ''));
        $text = trim((string) ($state['content']['text'] ?? ''));
        $status = (bool) ($state['is_active'] ?? true) ? 'ativo' : 'inativo';

        $typeLabel = static::sectionTypeOptions()[$type] ?? 'Bloco';
        $snippet = $heading !== '' ? $heading : mb_strimwidth(strip_tags($text), 0, 42, '...');

        return $snippet !== ''
            ? "{$typeLabel} - {$snippet} ({$status})"
            : "{$typeLabel} ({$status})";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('template')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('updated_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
