# Bella Criativa — Estrutura do Projeto

## Árvore Base

```text
bella-criativa/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── ImportCatalog.php
│   ├── Filament/
│   │   └── Resources/
│   │       ├── ProductResource.php
│   │       ├── CategoryResource.php
│   │       └── PageResource.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── CatalogController.php
│   │       ├── PageController.php
│   │       ├── LandingController.php
│   │       ├── ProductController.php
│   │       ├── CategoryController.php
│   │       └── SitemapController.php
│   ├── Livewire/
│   │   ├── CatalogFilters.php
│   │   ├── CatalogGrid.php
│   │   └── ProductSearch.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Page.php
│   │   ├── PageSection.php
│   │   └── ProductMedia.php
│   ├── Observers/
│   │   └── ProductObserver.php
│   └── Services/
│       ├── Import/
│       │   ├── SpreadsheetLoader.php
│       │   ├── ZipDownloader.php
│       │   ├── ImageProcessor.php
│       │   └── ProductUpsert.php
│       └── Seo/
│           └── SeoManager.php
├── bootstrap/
├── config/
│   └── catalog.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── build/
│   ├── favicon.ico
│   ├── index.php
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── layouts/
│       ├── pages/
│       ├── components/
│       └── livewire/
├── routes/
│   └── web.php
├── storage/
│   └── app/
│       └── public/
│           └── media/
├── tests/
├── docs/
├── artisan
├── composer.json
├── package.json
├── vite.config.js
└── .env.example
```

---

## Dependências Principais

### Composer

| Pacote | Uso |
|--------|-----|
| `laravel/framework` | base da aplicação |
| `filament/filament` | admin |
| `livewire/livewire` | componentes reativos |
| `intervention/image` | WebP e OG image |
| `maatwebsite/excel` | leitura XLSX |
| `artesaos/seotools` | meta tags e JSON-LD |
| `spatie/laravel-sitemap` | sitemap XML |
| `spatie/laravel-responsecache` | cache de páginas públicas |

### npm

| Pacote | Uso |
|--------|-----|
| `tailwindcss` | estilos utilitários |
| `@tailwindcss/forms` | formulários |
| `alpinejs` | micro-interações |
| `vite` | build de assets |
| `laravel-vite-plugin` | integração com Laravel |

---

## Variáveis de Ambiente

```bash
APP_NAME="Bella Criativa"
APP_ENV=production
APP_URL=https://belacriativa.com.br

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bela_criativa
DB_USERNAME=usuario
DB_PASSWORD=senha

FILESYSTEM_DISK=public

WHATSAPP_NUMBER=5516999999999
IMPORT_IMAGE_QUALITY=80
IMPORT_DOWNLOAD_TIMEOUT=30
IMPORT_DOWNLOAD_ATTEMPTS=3
```

---

## Organização de Responsabilidades

- `app/Models`: entidades Eloquent
- `app/Filament`: admin e formulários internos
- `app/Livewire`: filtros, grid e busca do catálogo
- `app/Services/Import`: pipeline de ingestão
- `resources/views`: HTML público
- `storage/app/public/media`: imagens persistidas por SKU

**Complemento institucional**

- `Page` representa páginas editáveis como `sobre`, `contato`, `lancamentos` e `linha-premium`
- `PageSection` representa blocos ordenáveis da home e das landings editoriais

---

## Scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  }
}
```

**Operação real do projeto**

```bash
# desenvolvimento local
php artisan serve
npm run dev

# assets de produção
npm run build

# banco
php artisan migrate

# importação
php artisan catalog:import storage/app/import/products.xlsx
php artisan catalog:import storage/app/import/products.xlsx --dry-run
php artisan catalog:import storage/app/import/products.xlsx --limit=5
```

---

## Convenções

- SKU é a chave de integração externa
- Slug não muda após publicação
- Arquivos de mídia ficam em `media/{sku}/`
- Todo produto publicado precisa ter `featured_image` e `og_image`
- Não introduzir API pública no piloto sem necessidade real
