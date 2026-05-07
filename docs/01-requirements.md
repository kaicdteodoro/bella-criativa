# Bella Criativa — Requisitos e Regras de Negócio

## 1. Contexto

Site institucional com catálogo digital público da Bella Criativa. Stack: Laravel + Filament + Blade + Tailwind + Alpine.js + Livewire. Hospedado no cPanel da agência. Canal primário de saída: WhatsApp.

---

## 2. Requisitos Funcionais

### 2.1 Site Institucional (frontend)

| ID | Requisito |
|----|-----------|
| INS-01 | Home editorial com hero, lançamentos/destaques, mosaico de categorias e CTA de contato |
| INS-02 | Página `Sobre` com história, processos, sustentabilidade e bastidores |
| INS-03 | Página `Contato` com WhatsApp, e-mail, formulário e informações da empresa |
| INS-04 | Página `Lançamentos` com curadoria visual de produtos recentes |
| INS-05 | Página `Linha Premium` com seleção editorial e posicionamento de marca |
| INS-06 | Páginas auxiliares: busca, 404, política de privacidade/LGPD e termos |
| INS-07 | Header fixo minimalista com navegação institucional + catálogo |
| INS-08 | Conteúdo institucional deve ser editável sem código |

### 2.2 Catálogo Público (frontend)

| ID | Requisito |
|----|-----------|
| CAT-01 | Listagem de produtos com scroll infinito ou botão "Ver mais" (sem paginação numérica) |
| CAT-02 | Página individual por produto com URL canônica por slug |
| CAT-03 | Filtragem por categoria via chips clicáveis (não dropdowns); URL reflete filtros ativos |
| CAT-04 | Busca por nome ou SKU em overlay (não nova página) |
| CAT-05 | Imagem destacada + galeria com thumbnails e troca por click |
| CAT-06 | Swatches visuais de cor disponível; tags de material |
| CAT-07 | Descrição curta sempre visível; descrição técnica e specs em acordeão |
| CAT-08 | Só produtos com status `publicado` aparecem no frontend |
| CAT-09 | URL: `/produtos/{slug}` |
| CAT-10 | Página de categoria: `/categorias/{slug}` |
| CAT-11 | Hover no card → troca para 2ª imagem do produto (Alpine.js) |
| CAT-12 | CTA sticky no bottom (mobile): preço + botão WhatsApp durante scroll no PDP |
| CAT-13 | Skeleton screens em carregamentos assíncronos (Livewire) |

### 2.3 Conectores de Saída

| ID | Requisito |
|----|-----------|
| OUT-01 | **WhatsApp (primário):** botão CTA gera mensagem pré-formatada com nome, variante, preço e link |
| OUT-02 | OG tags com imagem 1200×630px para preview rico no WhatsApp |
| OUT-03 | URL com filtros ativos compartilhável diretamente |
| OUT-04 | **Link público (secundário):** URL limpa sem login |
| OUT-05 | **E-mail (terciário — pós-piloto):** export HTML ou PDF da seleção |

### 2.4 SEO

| ID | Requisito |
|----|-----------|
| SEO-01 | `<title>` e `<meta description>` únicos por página de produto |
| SEO-02 | `<meta description>` derivada da descrição curta |
| SEO-03 | Open Graph tags por produto (og:title, og:description, og:image) |
| SEO-04 | JSON-LD `Product` por página de produto |
| SEO-05 | Sitemap XML automático (`/sitemap.xml`) |
| SEO-06 | `robots.txt` configurado |
| SEO-07 | Canonical URL em cada página |
| SEO-08 | HTML server-rendered (Blade) — sem client-side rendering |

### 2.5 Admin (Filament)

| ID | Requisito |
|----|-----------|
| ADM-01 | CRUD individual de produto: criar, editar, excluir |
| ADM-02 | CRUD de categorias |
| ADM-03 | Upload de imagens (featured + galeria) |
| ADM-04 | Status por produto: rascunho / publicado |
| ADM-05 | Busca por SKU ou nome no admin |
| ADM-06 | Só usuários autenticados acessam o admin |
| ADM-07 | Ação "Importar XLSX" disponível no admin (dispara Artisan command) |
| ADM-08 | Editor ou CRUD para páginas institucionais e blocos editoriais |

### 2.6 Importação em Lote (Artisan command)

| ID | Requisito |
|----|-----------|
| IMP-01 | `php artisan catalog:import {file}` lê planilha XLSX |
| IMP-02 | Download de ZIP de imagens por produto via URL |
| IMP-03 | Conversão de imagens para WebP (Intervention Image) |
| IMP-04 | Geração de OG image 1200×630px por produto |
| IMP-05 | Upsert: cria se SKU não existe, atualiza se existe |
| IMP-06 | Isolamento de erro por SKU — falha nunca aborta o run |
| IMP-07 | `--dry-run`: valida sem escrever |
| IMP-08 | `--limit=N`: processa só os primeiros N |
| IMP-09 | `--resume`: pula SKUs que já existem |
| IMP-10 | Log estruturado por SKU e relatório final |

---

## 3. Regras de Negócio

### 3.1 SKU
- Identificador único, imutável após criação
- String livre (ex: `PB-KIT-CH-05184B`)
- Duplicado na importação → upsert

### 3.2 Slug
- Derivado do título se não fornecido (`Str::slug()`)
- Colisão → sufixo numérico (`-2`, `-3`)
- Não deve mudar após publicação

### 3.3 Status

| Status | Frontend | Admin |
|--------|----------|-------|
| `draft` | não exibe | editável |
| `published` | exibe | editável |

### 3.4 Taxonomia
- Taxonomy: `item-category`
- Muitos-para-muitos (produto pode ter várias categorias)
- Categoria inexistente na importação → criada automaticamente
- `term_map` no config: nome PT → slug EN

### 3.5 Mídia
- Featured: primeira imagem da galeria se não especificada
- Formato obrigatório de saída: WebP
- OG image: crop central 1200×630px da featured
- Checksum SHA-256 por arquivo para deduplicação
- Storage: `storage/app/public/media/{sku}/`
- URL pública: `/storage/media/{sku}/{arquivo}.webp`

### 3.6 Atributos
- `available_colors`: array de hex strings
- `materials`: array de strings descritivas
- Detectados do nome do arquivo na importação
- Editáveis manualmente no admin

---

## 4. Critérios de Aceitação

### OUT — Saída
- [ ] Botão WhatsApp gera mensagem com nome, variante selecionada, preço e link
- [ ] Preview do link no WhatsApp mostra imagem, título e descrição
- [ ] URL com filtro abre corretamente ao compartilhar

### INS — Institucional
- [ ] Home exibe hero editorial, categorias em destaque e CTA de contato
- [ ] Sobre e Contato existem como páginas próprias
- [ ] Lançamentos e Linha Premium funcionam como landings de curadoria
- [ ] Header expõe navegação institucional + catálogo de forma clara

### CAT — Catálogo
- [ ] Listagem renderiza só produtos publicados
- [ ] Filtro por categoria atualiza lista e URL sem reload de página
- [ ] Busca retorna resultados em overlay
- [ ] Hover no card troca imagem
- [ ] PDP mobile: CTA sticky no bottom durante scroll
- [ ] Produto `draft` não aparece no frontend

### SEO
- [ ] Source HTML contém `<title>`, `<meta description>`, og tags e JSON-LD por produto
- [ ] `/sitemap.xml` lista todas as URLs de produtos publicados
- [ ] Lighthouse SEO ≥ 90 numa página de produto

### ADM
- [ ] CRUD de produto completo no Filament
- [ ] Upload de imagem reflete no frontend após salvar
- [ ] Importação via admin dispara o command e retorna resultado

### IMP
- [ ] `--dry-run` valida sem escrever
- [ ] Upsert: re-importar mesma planilha não duplica produtos
- [ ] Falha num SKU não aborta o run

---

## 5. Non-Goals (fora do escopo)

- E-commerce / carrinho / checkout
- Multi-tenancy (pós-piloto)
- WhatsApp number configurável pelo admin (pós-piloto)
- Export e-mail / PDF (pós-piloto)
- Autenticação de usuário no frontend
