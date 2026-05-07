# Bella Criativa — Modelo de Dados

## Visão Geral

```
pages ──── has-many ──── page_sections

products ──── many-to-many ──── categories
    │               (product_category)
    └── has-many ──── product_media
```

---

## Migration: `products`

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('sku')->unique();
    $table->string('title');
    $table->string('slug')->unique();
    $table->enum('status', ['draft', 'published'])->default('published');
    $table->text('short_description')->nullable();
    $table->longText('technical_description')->nullable(); // HTML
    $table->string('featured_image')->nullable();         // path relativo ao storage
    $table->string('og_image')->nullable();               // crop 1200×630 para OG/WhatsApp
    $table->json('available_colors')->nullable();         // ["#000000", "#FFFFFF"]
    $table->json('materials')->nullable();                // ["wood", "metal"]
    $table->string('supplier_code')->nullable();
    $table->string('source_supplier')->nullable();
    $table->timestamps();

    $table->index('status');
});
```

---

## Migration: `categories`

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('featured_image')->nullable();
    $table->timestamps();
});
```

---

## Migration: `product_category` (pivot)

```php
Schema::create('product_category', function (Blueprint $table) {
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->primary(['product_id', 'category_id']);
});
```

---

## Migration: `product_media` (galeria)

```php
Schema::create('product_media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('file');         // path relativo: media/{sku}/{sku}-01.webp
    $table->string('checksum');     // SHA-256 hex para deduplicação
    $table->integer('order')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'checksum']); // dedup por checksum
});
```

---

## Migration: `pages`

```php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();               // sobre, contato, lancamentos, linha-premium
    $table->string('template')->default('default'); // default, about, contact, launches, premium, home
    $table->enum('status', ['draft', 'published'])->default('published');
    $table->text('excerpt')->nullable();
    $table->longText('body')->nullable();           // HTML rico para blocos simples
    $table->string('hero_image')->nullable();
    $table->string('seo_title')->nullable();
    $table->text('seo_description')->nullable();
    $table->timestamps();

    $table->index(['status', 'template']);
});
```

**Uso no piloto:**

- `home`
- `sobre`
- `contato`
- `lancamentos`
- `linha-premium`
- auxiliares como `politica-de-privacidade`

---

## Migration: `page_sections`

```php
Schema::create('page_sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('page_id')->constrained()->cascadeOnDelete();
    $table->string('type');                // hero, rich_text, category_mosaic, logo_cloud, featured_products, cta
    $table->string('heading')->nullable();
    $table->json('content')->nullable();   // payload estruturado por tipo
    $table->unsignedInteger('position')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['page_id', 'position']);
});
```

**Exemplo de `content` por tipo:**

```json
{
  "hero": {
    "kicker": "Brindes com presença",
    "title": "Presentes corporativos com acabamento premium",
    "text": "Seleção editorial da Bella Criativa.",
    "primary_cta_label": "Ver catálogo",
    "primary_cta_url": "/produtos"
  }
}
```

---

## Model: `Product`

```php
class Product extends Model
{
    protected $fillable = [
        'sku', 'title', 'slug', 'status',
        'short_description', 'technical_description',
        'featured_image', 'og_image',
        'available_colors', 'materials',
        'supplier_code', 'source_supplier',
    ];

    protected $casts = [
        'available_colors' => 'array',
        'materials'        => 'array',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('order');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    // URL helpers
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image
            ? Storage::url($this->featured_image)
            : null;
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image
            ? Storage::url($this->og_image)
            : null;
    }
}
```

---

## Model: `Category`

```php
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'featured_image'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
```

---

## Model: `Page`

```php
class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'template',
        'status',
        'excerpt',
        'body',
        'hero_image',
        'seo_title',
        'seo_description',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
```

---

## Model: `PageSection`

```php
class PageSection extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'heading',
        'content',
        'position',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
```

---

## Model: `ProductMedia`

```php
class ProductMedia extends Model
{
    protected $fillable = ['product_id', 'file', 'checksum', 'order'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file);
    }
}
```

---

## Compatibilidade com manifest.json (sana/hana legado)

| manifest.json | Campo no banco |
|---|---|
| `sku` | `products.sku` |
| `product.title` | `products.title` |
| `product.slug` | `products.slug` |
| `product.status` | `products.status` |
| `descriptions.short` | `products.short_description` |
| `descriptions.technical` | `products.technical_description` |
| `taxonomy.item-category[]` | `product_category` (via slug) |
| `attributes.available_colors[]` | `products.available_colors` (JSON) |
| `attributes.materials[]` | `products.materials` (JSON) |
| `media.featured` | `products.featured_image` |
| `media.gallery[].file` | `product_media.file` |
| `media.gallery[].checksum` | `product_media.checksum` |
| `meta.source` | `products.source_supplier` |
| `meta.supplier_code` | `products.supplier_code` |
| *(gerado no import)* | `products.og_image` |

---

## Conteúdo Institucional

| Necessidade | Estrutura |
|---|---|
| Home editorial | `pages.slug = home` + `page_sections` |
| Sobre | `pages.slug = sobre` |
| Contato | `pages.slug = contato` |
| Lançamentos | `pages.slug = lancamentos` + seção de produtos destacados |
| Linha Premium | `pages.slug = linha-premium` + seção de produtos destacados |
| Políticas/termos | `pages` simples com `body` |

**Decisão prática:** usar `pages` + `page_sections` evita criar tabela separada para cada landing institucional e mantém o Filament simples de operar.
