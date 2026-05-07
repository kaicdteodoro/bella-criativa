# Bella Criativa — Especificação: Frontend

Stack pública: Blade + Tailwind CSS + Alpine.js + Livewire. HTML server-rendered por padrão; Livewire entra apenas onde agrega UX sem perder SEO. O projeto combina páginas institucionais com catálogo.

---

## 1. Estrutura de Views

```text
resources/views/
├── layouts/
│   └── app.blade.php
├── pages/
│   ├── home.blade.php
│   ├── about.blade.php
│   ├── contact.blade.php
│   ├── launches.blade.php
│   ├── premium.blade.php
│   ├── products/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   └── categories/
│       └── show.blade.php
├── components/
│   ├── header.blade.php
│   ├── footer.blade.php
│   ├── breadcrumb.blade.php
│   ├── product-card.blade.php
│   ├── product-gallery.blade.php
│   ├── color-swatch.blade.php
│   ├── badge.blade.php
│   └── whatsapp-cta.blade.php
└── livewire/
    ├── catalog-filters.blade.php
    ├── catalog-grid.blade.php
    └── product-search.blade.php
```

---

## 2. Rotas Públicas

```php
Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/sobre', [PageController::class, 'about'])->name('about');
Route::get('/contato', [PageController::class, 'contact'])->name('contact');
Route::get('/lancamentos', [LandingController::class, 'launches'])->name('launches');
Route::get('/linha-premium', [LandingController::class, 'premium'])->name('premium');
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categorias/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
```

**Observação:** busca não ganha rota dedicada no piloto. Ela abre como overlay global e consulta produtos publicados via Livewire.

---

## 3. Páginas

### 3.1 Home (`/`)

- Hero editorial simples, sem carrossel
- Bloco `Como personalizamos`
- Logos de clientes ou prova social
- Bloco de categorias em destaque
- Grade enxuta de produtos publicados
- CTA para o catálogo completo
- Renderização Blade tradicional

### 3.2 Sobre (`/sobre`)

- História da marca
- Processo produtivo
- Sustentabilidade
- Bastidores
- Layout mais editorial que comercial

### 3.3 Contato (`/contato`)

- WhatsApp
- e-mail
- formulário simples
- mapa ou informações operacionais

### 3.4 Lançamentos (`/lancamentos`)

- Curadoria de produtos recentes
- Pode usar tag, categoria dedicada ou flag de destaque

### 3.5 Linha Premium (`/linha-premium`)

- Curadoria visual de produtos premium
- Landing page híbrida: conteúdo institucional + grade filtrada

### 3.6 Catálogo (`/produtos`)

- Header de página com título e texto curto
- `CatalogFilters` em chips, com categoria ativa refletida na URL
- `CatalogGrid` com botão `Ver mais`
- Sem paginação numérica
- Query base:

```php
Product::published()
    ->with(['categories', 'media'])
    ->latest('id');
```

**URL exemplo**

```text
/produtos?categoria=bbq-kits
```

### 3.7 Página de Categoria (`/categorias/{slug}`)

- Mesma estrutura do catálogo
- Header com nome e descrição da categoria
- Filtro inicial travado na categoria atual
- URL canônica própria por categoria

### 3.8 Produto Individual (`/produtos/{slug}`)

Seções:

1. Breadcrumb
2. Galeria de produto
3. Bloco informativo com título, SKU, categorias, swatches e materiais
4. Descrição curta
5. Acordeões de conteúdo técnico
6. CTA WhatsApp
7. Produtos relacionados

Controller:

```php
public function show(string $slug): View
{
    $product = Product::published()
        ->with(['categories', 'media'])
        ->where('slug', $slug)
        ->firstOrFail();

    seo()
        ->title("{$product->title} | Bella Criativa")
        ->description($product->short_description)
        ->canonical(route('products.show', $product->slug))
        ->openGraphImage($product->og_image_url)
        ->productJsonLd($product);

    $related = Product::published()
        ->whereKeyNot($product->id)
        ->whereHas('categories', fn ($query) =>
            $query->whereIn('categories.id', $product->categories->pluck('id'))
        )
        ->with(['categories', 'media'])
        ->limit(4)
        ->get();

    return view('pages.products.show', compact('product', 'related'));
}
```

---

## 4. Componentes Interativos

### 4.1 Card de Produto

- Hover troca para a segunda imagem, se existir
- Nome do produto sempre visível
- Swatches resumidos, sem poluir o card
- Clique no card inteiro leva ao PDP

```blade
<article
    x-data="{ hovered: false }"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
>
    <a href="{{ route('products.show', $product->slug) }}">
        <div class="aspect-[3/4] overflow-hidden bg-[--color-bg-soft]">
            <img
                x-show="!hovered"
                src="{{ $product->featured_image_url }}"
                alt="{{ $product->title }}"
                class="h-full w-full object-cover"
            >

            @if($product->media->count() > 1)
                <img
                    x-cloak
                    x-show="hovered"
                    src="{{ $product->media[1]->url }}"
                    alt="{{ $product->title }}"
                    class="h-full w-full object-cover"
                >
            @endif
        </div>

        <h3 class="mt-3 text-sm font-medium">{{ $product->title }}</h3>
        <x-color-swatch :colors="$product->available_colors" />
    </a>
</article>
```

### 4.2 Galeria do PDP

- Thumbnail clique-to-swap
- Sem lightbox no piloto
- Mobile prioriza swipe horizontal simples

```blade
<div x-data="{ active: 0, images: @js($images) }">
    <div class="aspect-[3/4] overflow-hidden bg-[--color-bg-soft]">
        <img :src="images[active].url" :alt="images[active].alt" class="h-full w-full object-cover">
    </div>

    <div class="mt-3 flex gap-2 overflow-x-auto">
        <template x-for="(image, index) in images" :key="index">
            <button @click="active = index" class="h-16 w-16 shrink-0 overflow-hidden border border-[--color-border]">
                <img :src="image.url" :alt="image.alt" class="h-full w-full object-cover">
            </button>
        </template>
    </div>
</div>
```

### 4.3 Acordeões

- `Descrição técnica`
- `Especificações`
- `Informações adicionais`
- Fechados por padrão no mobile

### 4.4 CTA WhatsApp

- Desktop: inline no bloco informativo
- Mobile: sticky no rodapé com preço e botão
- Mensagem inclui nome, SKU e link do produto

```blade
@php
    $text = rawurlencode(
        "Olá! Tenho interesse no produto {$product->title} (SKU {$product->sku}). " .
        route('products.show', $product->slug)
    );
@endphp

<a
    href="https://wa.me/{{ config('catalog.whatsapp_number') }}?text={{ $text }}"
    target="_blank"
    rel="noopener"
>
    Consultar via WhatsApp
</a>
```

---

## 5. Livewire

### 5.1 `CatalogFilters`

Responsável por:

- ativar/desativar chips
- sincronizar estado com query string
- disparar evento para o grid

```php
class CatalogFilters extends Component
{
    #[Url(as: 'categoria')]
    public ?string $category = null;

    public function toggleCategory(string $slug): void
    {
        $this->category = $this->category === $slug ? null : $slug;
        $this->dispatch('catalog-category-changed', category: $this->category);
    }
}
```

### 5.2 `CatalogGrid`

Responsável por:

- montar a query publicada
- responder ao filtro de categoria
- expandir a grade com `Ver mais`

```php
class CatalogGrid extends Component
{
    public int $perPage = 24;
    public ?string $category = null;

    #[On('catalog-category-changed')]
    public function setCategory(?string $category): void
    {
        $this->category = $category;
        $this->perPage = 24;
    }

    public function loadMore(): void
    {
        $this->perPage += 24;
    }
}
```

### 5.3 `ProductSearch`

Responsável por:

- abrir/fechar o overlay global
- consultar nome ou SKU com debounce
- navegar direto para o produto

---

## 6. Layout Global

```blade
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {!! seo()->render() !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[--color-bg] text-[--color-text-primary]">
    <x-header />
    <livewire:product-search />

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-footer />
    @livewireScripts
</body>
</html>
```

**Regra operacional:** Vite/Tailwind existem para build de assets, mas o deploy de produção não depende de processo Node persistente.

**Header previsto no piloto:**

- Logo
- Produtos
- Categorias
- Lançamentos
- Linha Premium
- Sobre
- Contato
- Busca

---

## 7. SEO

- `<title>` único por página
- `<meta description>` derivada da descrição curta
- OG tags por produto
- JSON-LD `Product` no PDP
- canonical em catálogo, categoria e produto
- sitemap apenas com produtos `published`

---

## 8. Responsividade

| Breakpoint | Catálogo | PDP |
|------------|----------|-----|
| `<640px` | 2 colunas | 1 coluna + CTA sticky |
| `640–1023px` | 3 colunas | 1 coluna |
| `>=1024px` | 4 colunas | 2 colunas |
