# Bella Criativa — Arquitetura

## 1. Visão Geral

Projeto Laravel monolítico: backend + admin + frontend institucional/catalogo num único codebase, hospedado no cPanel da agência.

```
bella-criativa/
├── app/                    ← lógica de negócio (Models, Controllers, Livewire, Filament, Commands)
├── database/               ← migrations e seeders
├── resources/views/        ← Blade templates + componentes
├── storage/app/public/     ← imagens dos produtos (via storage:link)
├── routes/web.php          ← rotas públicas
└── composer.json
```

---

## 2. Stack

| Camada | Tecnologia | Justificativa |
|--------|-----------|---------------|
| Framework | Laravel 11 | PHP nativo no cPanel, ecosistema maduro, Eloquent, Artisan |
| Admin | Filament 3 | Admin gerado automaticamente, UI elegante, gratuito, usa Livewire |
| Frontend | Blade + Tailwind CSS | Server-rendered para institucional + catálogo, SEO perfeito, WhatsApp previews nativos |
| Reatividade | Livewire 3 | Componentes reativos sem JavaScript framework |
| Micro-interações | Alpine.js 3 | Hover, acordeão, galeria, swatches — sem build step |
| Banco | MySQL | Nativo no cPanel, Laravel suporte completo |
| Imagens | Intervention Image 3 | Conversão WebP + crop OG em PHP |
| Planilha | Maatwebsite/Excel | Leitura XLSX no PHP, wraps PhpSpreadsheet |
| HTTP | Laravel HTTP Client | Download de ZIPs (Guzzle sob o capô, já incluso) |
| SEO | artesaos/seotools | Meta tags, OG, JSON-LD |
| Sitemap | spatie/laravel-sitemap | Sitemap XML automático |
| Cache | spatie/laravel-responsecache | Cache de resposta para páginas públicas |

---

## 3. Decisões Arquiteturais (ADRs)

### ADR-01: Laravel monolítico (não API + frontend separado)

**Decisão:** um único projeto Laravel serve o admin (Filament), as páginas institucionais, o catálogo público e a importação (Artisan).

**Motivo:** cPanel não suporta dois processos distintos de forma confiável. Monolito elimina essa restrição, simplifica deploy e mantém tudo em PHP — linguagem nativa do ambiente.

**Trade-off:** menos separação de responsabilidades. Aceitável para um site institucional com catálogo sem requisitos de escala independente.

---

### ADR-02: Blade + Alpine.js + Livewire (não Inertia + Vue/React)

**Decisão:** frontend server-rendered com Blade, Alpine.js para micro-interações e Livewire para componentes reativos.

**Motivo central:** WhatsApp é o canal primário de saída. Crawlers do WhatsApp não executam JavaScript — precisam de HTML pronto no servidor. Inertia.js sem SSR renderiza no cliente e quebra os previews. Inertia com SSR precisa de Node.js persistente, inviável no cPanel.

**Blade + Alpine + Livewire entrega:**
- HTML server-rendered (SEO + WhatsApp nativos)
- Páginas institucionais rápidas e simples de manter
- Reatividade para filtros e busca (Livewire)
- Micro-interações fluidas (Alpine.js)
- Zero build step de JavaScript (Alpine via CDN ou bundle simples)
- Stack 100% consistente com Filament (que já usa Livewire)

---

### ADR-03: MySQL (não PostgreSQL)

**Decisão:** MySQL, banco padrão do cPanel.

**Motivo:** PostgreSQL não está disponível em shared hosting cPanel. MySQL suportado nativamente pelo Laravel com todas as features necessárias para um catálogo de produto.

---

### ADR-04: Artisan command para importação (não script externo)

**Decisão:** `php artisan catalog:import` em vez de script Node.js/Python separado.

**Motivo:** integração nativa com Eloquent (upsert direto), sem dependência de runtime externo, executável via SSH no cPanel ou disparado pelo Filament. Maatwebsite/Excel lê XLSX, Intervention Image converte WebP — mesma lógica do sana, em PHP.

---

### ADR-05: Storage local no cPanel (não Cloudinary)

**Decisão:** imagens armazenadas em `storage/app/public/media/`, acessíveis via `storage:link`.

**Motivo:** cPanel tem sistema de arquivos persistente — diferente do Vercel. Storage local elimina dependência externa, latência de CDN de terceiro e custo. URL previsível: `/storage/media/{sku}/{arquivo}.webp`.

**Fallback pós-piloto:** se houver necessidade de CDN, `spatie/laravel-medialibrary` com driver S3 é plug-in.

---

### ADR-06: OG image gerada na importação (Intervention Image)

**Decisão:** crop 1200×630px da imagem featured gerado durante o `catalog:import`, salvo como `{sku}-og.webp`.

**Motivo:** WhatsApp cacheia a OG image na primeira vez que o link é compartilhado. Arquivo estático elimina latência de geração dinâmica e garante que o preview sempre funciona.

**Implementação:** `Image::read($featuredPath)->cover(1200, 630)->toWebp()->save(...)` com Intervention Image 3.

---

### ADR-07: Cache de resposta para páginas públicas

**Decisão:** `spatie/laravel-responsecache` para cachear HTML das páginas institucionais e de catálogo.

**Motivo:** shared hosting tem recursos limitados. Cache de resposta elimina queries de banco e renderização Blade em requests repetidas — LCP cai significativamente.

**Invalidação:** cache limpo automaticamente quando produto é salvo no Filament (via Observer ou hook do model).

---

### Decisões Pós-Piloto (não implementar agora)

| Decisão | Descrição |
|---------|-----------|
| WhatsApp configurável | Número hardcoded em `.env` no piloto |
| Multi-cliente / template | Arquitetura single-tenant no piloto |
| Export e-mail / PDF | Após validar fluxo WhatsApp |
| CDN para imagens | `spatie/laravel-medialibrary` + S3 se necessário |

---

## 4. Fluxo de Dados

### 4.1 Importação em Lote

```
products.xlsx
  ↓
php artisan catalog:import
  ├── SpreadsheetLoader::load()   → ProductRow[]
  ├── ZipDownloader::download()  → arquivo temporário
  ├── ImageProcessor::process()  → WebP + og.webp → storage/app/public/media/{sku}/
  └── ProductUpsert::upsert()    → MySQL via Eloquent
```

### 4.2 Requisição Pública (Blade)

```
Browser → Nginx/Apache (cPanel)
  → Laravel Router
  → Controller (query Eloquent)
  → Blade view renderizado
  → HTML completo → Browser
  (cacheado pelo responsecache na 2ª requisição)
```

### 4.3 Admin → Frontend

```
Admin Filament salva produto
  → Eloquent Observer dispara
  → responsecache::forget() limpa cache do produto
  → próxima requisição renderiza HTML atualizado
```

### 4.4 WhatsApp CTA

```
Usuário clica "Consultar via WhatsApp"
  → Alpine.js monta URL: https://wa.me/{WHATSAPP_NUMBER}?text={mensagem}
  → abre WhatsApp com mensagem pré-formatada
```

---

## 5. Estrutura de URLs

| Rota | Controller |
|------|-----------|
| `/` | `CatalogController@home` |
| `/sobre` | `PageController@about` |
| `/contato` | `PageController@contact` |
| `/lancamentos` | `LandingController@launches` |
| `/linha-premium` | `LandingController@premium` |
| `/produtos` | `ProductController@index` |
| `/produtos/{slug}` | `ProductController@show` |
| `/categorias/{slug}` | `CategoryController@show` |
| `/sitemap.xml` | `SitemapController@index` |
| `/admin` | Filament (protegido) |
