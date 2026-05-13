# Bella Criativa

Site institucional + catalogo publico de produtos da Bella Criativa, com admin em Filament e pipeline de importacao/sincronizacao de catalogo.

## Stack

- Laravel 13
- Filament 5
- Livewire 4
- Blade + Tailwind CSS
- MySQL
- Laravel Excel
- Intervention Image
- Spatie Sitemap
- Spatie ResponseCache

## Objetivo do projeto

- Frontend server-rendered com foco em SEO e compartilhamento (WhatsApp/OG)
- Operacao por equipe nao tecnica via painel admin
- Importacao em lote e sincronizacao por fornecedores
- Catalogo sem checkout (CTA principal via WhatsApp)

## Ambiente local (Docker)

Subir app, banco, node e Ollama:

```bash
docker compose up -d
```

Instalacao inicial (primeira vez):

```bash
docker compose run --rm app composer install
docker compose run --rm node npm ci
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan storage:link
```

## Comandos uteis

```bash
# limpar caches
docker compose exec app php artisan optimize:clear

# rodar testes
docker compose exec app php artisan test

# sincronizar catalogo por API
docker compose exec app php artisan catalog:sync-api --source=xbz --limit=20

# auditar midia
docker compose exec app php artisan media:audit
```

## Deploy

Documentacao principal:

- `docs/07-deployment.md` - deploy manual no cPanel
- `docs/14-deploy-manual-cpanel.md` - runbook de deploy manual pelo terminal do cPanel
- `docs/15-prod-go-live-backlog.md` - backlog operacional para go-live

Workflow de deploy emergencial/manual:

- `.github/workflows/deploy-production.yml`

## Seguranca e SEO

Ja aplicados no projeto:

- headers de seguranca globais (CSP, frame/options, referrer/policy)
- endpoint de coleta de violacoes CSP report-only (`/csp-report`)
- metatags SEO/OG/Twitter dinamicas
- schema.org (Organization, Product, BreadcrumbList, CollectionPage/ItemList)
- `robots.txt` com referencia de sitemap

## Estrutura de docs

Pasta `docs/` contem requisitos, arquitetura, modelo de dados, importacao, frontend, deploy e backlog.

Leitura recomendada para onboarding:

1. `docs/01-requirements.md`
2. `docs/02-architecture.md`
3. `docs/03-data-model.md`
4. `docs/07-deployment.md`
