# Bella Criativa — Contexto para Agentes

## O que é este projeto

Site institucional + catálogo público de produtos da Bella Criativa. Migração do WordPress para Laravel com frontend server-rendered, admin para operação não técnica e importação em lote via planilha de fornecedor.

## Fase atual

**Base funcional já implementada.** O projeto não está mais em pré-codificação. Já existem stack instalada, models, migrations, seeders, páginas públicas, recursos do Filament, importador `catalog:import`, tela de import no admin e histórico de execuções.

## Leia antes de qualquer tarefa

0. `docs/00-brand-brief.md` — contexto de marca, cliente e posicionamento
1. `docs/01-requirements.md`
2. `docs/02-architecture.md`
3. `docs/03-data-model.md`
4. `docs/04-import-spec.md`
5. `docs/05-frontend-spec.md`
6. `docs/06-project-structure.md`
7. `docs/07-deployment.md`
8. `docs/08-design-system.md`
9. `docs/09-backlog.md`
10. `docs/10-regua-pensando-bem-aplicada.md` — padrão de qualidade aplicado
11. `docs/11-pipeline-produtos.md` — pipeline de enriquecimento do catálogo
12. `docs/12-institutional-content.md` — copy e conteúdo das páginas institucionais
13. `docs/13-media-pipeline.md` — diagnóstico e proposta do pipeline de imagens

## Stack atual do projeto

- Laravel 13
- Filament 5
- Livewire 4
- Blade + Tailwind CSS
- Alpine.js
- MySQL
- Laravel Excel
- Intervention Image
- Spatie Sitemap
- Spatie ResponseCache

## Constraints críticos

- frontend público server-rendered
- sem Inertia/Vue/React no piloto público
- SEO e preview de WhatsApp dependem de HTML pronto no servidor
- produção em cPanel, sem Node.js rodando em produção
- build de assets antes do deploy
- importação em lote via app Laravel
- isolamento de erro por SKU no importador
- sem e-commerce no piloto
- WhatsApp como CTA principal

## Estado funcional resumido

- catálogo público já existe em base inicial
- páginas institucionais já existem em base inicial
- admin Filament já existe
- importador CLI já existe
- página Filament de importação já existe
- histórico de import já existe
- testes do importador já existem

## Referências relacionadas

- `../sana/` — referência histórica para parsing e pipeline legado
- `SITE BELLA CRIATIVA.docx` — ATA de estrutura original do site (referência histórica; conteúdo já extraído em `docs/`)
- `docs/00-brand-brief.md` — brief completo da cliente extraído do briefing
- `docs/12-institutional-content.md` — copy e textos para as páginas do site

## Regra prática para novos agentes

Antes de começar, identifique em qual épico do `docs/09-backlog.md` a tarefa cai. Se a tarefa não couber em um épico existente, trate isso como sinal de escopo novo e documente a decisão antes de implementar.
