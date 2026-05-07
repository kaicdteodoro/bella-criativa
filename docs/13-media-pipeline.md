# 13. Pipeline de Mídia — Diagnóstico e Proposta

## Estado Atual (maio/2026)

### Inventário

| Métrica | Valor |
|---------|-------|
| Pastas de SKU em storage | 751 |
| Produtos no banco com `featured_image` | 13 |
| Produtos publicados | 13 |
| Registros em `product_media` | 46 |
| Imagens de galeria (02+) por produto, média | 3.5 |
| OG images (1200×630) | 749 |
| Total de arquivos `.webp` | 1.532 |
| Tamanho total em disco | ~22MB |

### Distribuição de peso (imagens de produto, excl. OG)

| Métrica | Valor |
|---------|-------|
| Mínimo | 4 KB |
| Mediana | 8 KB |
| P90 | 40 KB |
| Máximo | 176 KB |
| Total | ~13 MB |

---

## Problemas Identificados

### P1 — Pastas órfãs (crítico)

751 pastas de SKU no storage, mas apenas 13 produtos no banco. As demais são resíduos de syncs/testes anteriores sem produto correspondente. Ocupam espaço e poluem o diretório.

### P2 — Case inconsistente nas pastas (médio)

15 SKUs têm duplicatas de case (`x000256/` e `X000256/` coexistindo no mesmo diretório). O `sanitizeSkuForPath()` em `UrlImageProcessor` normaliza para uppercase, mas o pipeline ZIP normaliza diferente. Resultado: dois caminhos para o mesmo SKU, risco de arquivo errado sendo servido.

**Raiz:** `ImageProcessor.sanitizeSkuForPath()` e `UrlImageProcessor.sanitizeSkuForPath()` são cópias com comportamento levemente diferente — deveriam ser um único método compartilhado.

### P3 — Sem dimensões responsivas (médio)

O pipeline gera apenas duas versões por imagem:
- `-01.webp` (dimensão original, convertida para WebP)
- `-og.webp` (crop central 1200×630)

Não há thumbnails nem `srcset`. O card do catálogo carrega a imagem full-size mesmo em mobile, onde precisaria de ~400px no máximo.

### P4 — Sem limite de dimensão máxima (baixo)

A conversão para WebP não redimensiona — apenas converte o formato. Imagens originais grandes (até 176KB após conversão) são servidas sem redução de dimensão. O `scaleDown()` do Intervention Image não está sendo chamado antes do `toWebp()`.

### P5 — `color_hex` não populado na maioria dos registros

O `UrlImageProcessor` extrai `color_hex` do hint na URL (`url#HEXCODE`). Se a API da XBZ não enviar o hint, o campo fica nulo. Com 46 registros de media e apenas parte com hex preenchido, os swatches ficam incompletos para a maioria dos produtos.

---

## Proposta de Melhoria

### Curto prazo (antes de publicar o catálogo)

**1. Unificar `sanitizeSkuForPath()` num trait/helper compartilhado**

```php
// App\Services\Import\SkuPathHelper (trait)
private function sanitizeSkuForPath(string $sku): string
{
    // uppercase, somente alfanumérico + hífen
    $normalized = Str::of($sku)->ascii()->upper()->replaceMatches('/[^A-Z0-9]+/', '-')->trim('-')->value();
    return $normalized ?: 'SKU-'.substr(hash('sha1', $sku), 0, 8);
}
```

Aplicar em `ImageProcessor` e `UrlImageProcessor`. Isso elimina o P2 para novos syncs.

**2. Adicionar `scaleDown` antes do `toWebp()` nos dois processadores**

```php
// dimensão máxima razoável para produto: 1200px na maior dimensão
$encoded = (string) $manager->read($binary)->scaleDown(1200, 1200)->toWebp($quality);
```

Resolve P4. Não quebra nada existente — só evita armazenar imagens maiores que necessário.

**3. Gerar thumbnail `_sm.webp` (400×500px) junto com cada imagem**

```php
$thumb = (string) $manager->read($binary)->scaleDown(400, 500)->toWebp(75);
$thumbPath = sprintf('media/%s/%s-%02d_sm.webp', $safeSku, $safeSku, $index);
Storage::disk('public')->put($thumbPath, $thumb);
```

O card do catálogo passa a usar o `_sm.webp` via `srcset` ou atributo `src` direto. P90 do thumbnail estimado em ~12KB vs ~40KB atual.

**4. Limpar pastas órfãs com comando artisan**

```
php artisan media:prune-orphans [--dry-run]
```

Lógica: listar todas as pastas em `storage/app/public/media/`, cruzar com `products.featured_image`, remover o que não tiver produto correspondente.

### Médio prazo (após catálogo publicado)

**5. Comando `media:audit`**

Gera relatório CSV com:
- SKU, quantidade de imagens, peso total, color_hex preenchido (s/n), tem OG (s/n)
- Flag para SKUs com case duplicado no storage
- Flag para SKUs com imagem acima de X KB

**6. Regenerar thumbnails dos 13 produtos publicados**

Após implementar o thumbnail no pipeline, rodar um comando pontual para gerar `_sm.webp` retroativamente das imagens já existentes.

---

## Estrutura de Arquivo Proposta (pós-melhoria)

```
storage/app/public/media/{SKU-UPPERCASE}/
├── {SKU}-01.webp        # imagem principal (máx 1200px)
├── {SKU}-01_sm.webp     # thumbnail do card (máx 400×500px)
├── {SKU}-02.webp
├── {SKU}-02_sm.webp
├── ...
└── {SKU}-og.webp        # OG 1200×630 para WhatsApp/OpenGraph
```

---

## Uso no Frontend

```blade
{{-- card: usa thumbnail --}}
<img
    src="{{ Storage::url($product->featured_image) }}"
    srcset="{{ Storage::url(str_replace('.webp', '_sm.webp', $product->featured_image)) }} 400w,
            {{ Storage::url($product->featured_image) }} 1200w"
    sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"
    alt="{{ $product->title }}"
    loading="lazy"
    class="h-full w-full object-cover"
>
```

---

## Ordem de Execução Recomendada

```
1. Unificar sanitizeSkuForPath (não quebra nada, baixo risco)
2. Adicionar scaleDown ao pipeline (afeta apenas novos syncs)
3. Gerar thumbnails _sm no pipeline
4. Implementar media:prune-orphans --dry-run → validar → rodar
5. Retroativamente gerar _sm dos 13 produtos publicados
6. Atualizar product-card.blade.php para usar srcset
7. Implementar media:audit
```
