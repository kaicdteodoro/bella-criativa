# Bella Criativa — Especificação: Importação (Artisan Command)

Porto PHP do sana. Lê planilha XLSX do fornecedor e importa produtos via Eloquent.

---

## Uso

```bash
# Importação completa
php artisan catalog:import products.xlsx

# Validar sem escrever
php artisan catalog:import products.xlsx --dry-run

# Só os primeiros 5 (dev)
php artisan catalog:import products.xlsx --limit=5

# Pular SKUs já existentes
php artisan catalog:import products.xlsx --resume

# Fonte do fornecedor (opcional)
php artisan catalog:import products.xlsx --source=xbzbrindes
```

---

## Pipeline

```
products.xlsx
  ↓
SpreadsheetLoader::load()       → ProductRow[]
    │
    ▼ (por produto)
ZipDownloader::download()       → arquivo temporário em sys_get_temp_dir()
    │
    ▼
ImageProcessor::process()       → WebP gallery → storage/app/public/media/{sku}/
                                → og.webp (1200×630) → storage/app/public/media/{sku}/
                                → MediaData
    │
    ▼
ProductUpsert::upsert()         → Eloquent (create ou update por SKU)
    │
    ▼
ImportResult (action + warnings + reason)
    │
    ▼
relatório final (stdout)
```

---

## Contrato de Entrada — Planilha XLSX

Headers esperados (case-insensitive, trim automático):

| Header | Campo | Obrigatório |
|--------|-------|-------------|
| `post_title` | título do produto | sim |
| `codigo_sku` | SKU | sim |
| `codigo_fornecedor` | código do fornecedor | não |
| `categoria` | nome da categoria (português) | não |
| `descricao_curta` | descrição curta | não |
| `descricao_tecnica` | descrição técnica (pode conter HTML) | não |
| `imagens_zip_url` | URL para download do ZIP de imagens | sim |

---

## Classes

### `App\Console\Commands\ImportCatalog`

```php
class ImportCatalog extends Command
{
    protected $signature = 'catalog:import
        {file : Caminho para o arquivo XLSX}
        {--dry-run : Valida sem escrever}
        {--limit= : Processa só os primeiros N produtos}
        {--resume : Pula SKUs que já existem no banco}
        {--source= : Nome do fornecedor}';

    public function handle(
        SpreadsheetLoader $loader,
        ZipDownloader $downloader,
        ImageProcessor $processor,
        ProductUpsert $upsert,
    ): int
}
```

---

### `App\Services\Import\SpreadsheetLoader`

```php
readonly class ProductRow
{
    public function __construct(
        public string  $sku,
        public string  $title,
        public ?string $supplierCode,
        public ?string $category,
        public ?string $shortDescription,
        public ?string $technicalDescription,
        public string  $imagesZipUrl,
    ) {}
}

class SpreadsheetLoader
{
    public function load(string $path): array // ProductRow[]
    // - Usa Maatwebsite\Excel (PhpSpreadsheet)
    // - Normaliza headers: trim, strtolower
    // - Valida colunas obrigatórias
    // - Lança SpreadsheetException se coluna obrigatória ausente
}
```

---

### `App\Services\Import\ZipDownloader`

```php
class ZipDownloader
{
    public function download(string $sku, string $url): string // path do arquivo temporário
    // - Http::get() com timeout configurável (default: 30s)
    // - Retry com backoff: 3 tentativas, delay inicial 500ms
    // - Salva em tempnam(sys_get_temp_dir(), "catalog_{$sku}_")
    // - Lança DownloadException em falha definitiva
    // - Caller responsável por unlink() após uso
}
```

---

### `App\Services\Import\ImageProcessor`

```php
readonly class GalleryImage
{
    public string $file;        // path relativo: media/{sku}/{sku}-01.webp
    public string $checksum;    // SHA-256 hex
}

readonly class MediaData
{
    public string $featured;    // path relativo da imagem principal
    public string $ogImage;     // path relativo do crop OG (1200×630)
    /** @var GalleryImage[] */
    public array $gallery;
}

class ImageProcessor
{
    public function process(string $sku, string $zipPath, int $quality = 80): MediaData
    // 1. ZipArchive::open($zipPath)
    // 2. Para cada entrada de imagem no ZIP:
    //    a. ZipArchive::getFromName() → conteúdo da imagem
    //    b. Image::read($conteúdo)->toWebp($quality)->save($outputPath)
    //    c. SHA-256 do arquivo gerado
    // 3. featured = primeira imagem da galeria
    // 4. OG image: Image::read($featuredPath)->cover(1200, 630)->toWebp($quality)->save("{$sku}-og.webp")
    // 5. Detecta cor e material do nome do arquivo
    // 6. Lança ImageProcessingException se ZIP vazio ou sem imagens válidas
}
```

**Por que OG no import:**
WhatsApp cacheia a OG image na primeira vez que o link é compartilhado. Arquivo estático gerado no import garante preview rico sem latência de geração dinâmica.

**Detecção de cor** (do nome do arquivo):

| Padrão no stem | Hex |
|----------------|-----|
| `PRETO` | `#000000` |
| `BRANCO` | `#FFFFFF` |
| `PRATA` | `#C0C0C0` |
| `AZUL` | `#0057FF` |
| `VERMELHO` | `#FF0000` |
| `AMARELO` | `#FFD700` |
| `VERDE` | `#008000` |
| `LARANJA` | `#FF6600` |
| `ROSA` | `#FF69B4` |

**Detecção de material:**

| Keyword | Material |
|---------|----------|
| `MADEIRA` | `wood` |
| `METAL` | `metal` |
| `PLASTICO` | `plastic` |
| `COURO` | `leather` |
| `TECIDO` | `fabric` |

---

### `App\Services\Import\ProductUpsert`

```php
enum ImportAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Skipped = 'skipped';
    case Failed  = 'failed';
    case DryRun  = 'dry_run';
}

readonly class ImportResult
{
    public string        $sku;
    public ImportAction  $action;
    public int           $imagesProcessed;
    public array         $warnings;
    public ?string       $reason;
}

class ProductUpsert
{
    public function upsert(ProductRow $row, MediaData $media, array $termMap): ImportResult
    // 1. Product::where('sku', $row->sku)->first()
    // 2. Se não existe → Product::create([...])
    // 3. Se existe → $product->update([...])
    // 4. Sync categories: Category::firstOrCreate(['slug' => $slug], ['name' => $name])
    //    $product->categories()->sync($categoryIds)
    // 5. Sync gallery: dedup por checksum
    //    ProductMedia::where('product_id', $id)->where('checksum', $checksum)->exists()
    //    → se não existe: ProductMedia::create([...])
}
```

**Deduplicação de imagem:** antes de inserir cada imagem da galeria, verifica se já existe um registro com o mesmo `checksum` para o produto. Se sim, pula o insert — não duplica.

---

## Configuração

`config/catalog.php`:

```php
return [
    'import' => [
        'quality'       => env('IMPORT_IMAGE_QUALITY', 80),
        'timeout'       => env('IMPORT_DOWNLOAD_TIMEOUT', 30),
        'max_attempts'  => env('IMPORT_DOWNLOAD_ATTEMPTS', 3),
    ],
    'term_map' => [
        'Kits Churrasco' => 'bbq-kits',
        'Kits Queijo'    => 'cheese-kits',
        'Canetas'        => 'pens',
    ],
    'whatsapp_number' => env('WHATSAPP_NUMBER'),
];
```

---

## Erros Tipados

```php
class ImportException extends RuntimeException {}
class SpreadsheetException extends ImportException {}  // XLSX inválido
class DownloadException extends ImportException {}     // falha de download
class ImageProcessingException extends ImportException {} // ZIP inválido / sem imagens
```

Erros por SKU são capturados individualmente — nunca abortam o run.

---

## Relatório Final

```
Importação concluída
  total:      150
  criados:    120
  atualizados: 20
  pulados:      8
  falhas:       2

SKUs com falha:
  PB-KIT-CH-05184B: download timeout após 3 tentativas
  PB-CAN-AZ-00231A: ZIP não contém imagens válidas
```

---

## Pacotes PHP equivalentes ao sana (Python)

| sana (Python) | catalog:import (PHP) |
|---|---|
| openpyxl | Maatwebsite/Excel (PhpSpreadsheet) |
| Pillow | Intervention Image 3 |
| requests | Laravel HTTP Client (Guzzle) |
| zipfile | ZipArchive (PHP built-in) |
| hashlib | hash_file('sha256', ...) |
