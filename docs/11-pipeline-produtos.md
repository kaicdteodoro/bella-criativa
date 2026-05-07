# 11. Pipeline de Produtos: Do Sync ao Catálogo Publicado

## Contexto

Cerca de 659 produtos da XBZ foram importados e estão com status `draft` no banco. As informações vieram diretamente da API do fornecedor — títulos longos/brutos, descrições fracas, sem categorias. Nenhum está publicado ainda.

O objetivo deste documento é registrar as etapas necessárias para transformar esse material bruto em catálogo pronto para o público.

---

## Estado Atual dos Produtos Importados

| Campo              | Situação atual                                        |
|--------------------|-------------------------------------------------------|
| `status`           | `draft` — correto, nenhum publicado                   |
| `title`            | Descrição bruta do fornecedor (pode ser longa/técnica) |
| `short_description`| Mesmo conteúdo do título, sem refinamento             |
| `technical_description` | Vazio                                          |
| `categories`       | Nenhuma categoria associada                           |
| `featured_image`   | WebP gerado e salvo localmente (quando havia imagem)  |
| `og_image`         | Gerado junto ao featured_image                        |
| `source_supplier`  | `xbz`                                                |

---

## Próximos Passos

### Passo 1 — Enriquecimento com IA (`catalog:enrich`)

**Objetivo:** melhorar título, descrição e categoria de cada produto em lote usando Gemini Flash (gratuito, 1.500 req/dia).

**O que o comando deve fazer:**

1. Buscar produtos `draft` sem `enriched_at` (campo novo a ser adicionado)
2. Agrupar em lotes de ~50 produtos por requisição
3. Para cada lote, enviar prompt estruturado ao Gemini com:
   - título bruto do fornecedor
   - descrição curta bruta
   - instrução de saída em JSON com campos: `title`, `short_description`, `technical_description`, `category`
4. Receber resposta JSON com os campos enriquecidos
5. Atualizar produto com os dados retornados
6. Marcar `enriched_at = now()`
7. Atribuir categoria usando o campo retornado pela IA:
   - tentar match com categorias existentes por slug/nome
   - criar categoria nova caso não exista
8. Logar resultado por produto (enriquecido / falhou / sem alteração)

**Campos a adicionar na migration:**

```php
$table->timestamp('enriched_at')->nullable();
```

**Assinatura do comando:**

```
catalog:enrich {--limit=} {--dry-run} {--force}
```

- `--limit=N`: processar só N produtos (útil para testes)
- `--dry-run`: exibir o que seria feito sem gravar
- `--force`: processar mesmo produtos já enriquecidos

**Variável de ambiente necessária:**

```
GEMINI_API_KEY=
```

**Critérios de aceite:**

- Produto com `enriched_at` preenchido tem título e descrição melhores que o original
- Categorias atribuídas fazem sentido para o catálogo
- Comando pode ser re-executado sem duplicar trabalho
- Falhas por produto não interrompem o lote inteiro

---

### Passo 2 — Revisão Manual pela Bella no Filament

**Objetivo:** Bella revisa produtos enriquecidos antes de publicar.

**O que preparar no admin:**

- Filtro de `status = draft` e `enriched_at is not null` no `ProductResource`
- Ação em lote "Publicar selecionados" (bulk action)
- Ação em lote "Rejeitar / Arquivar selecionados"
- Exibição do `enriched_at` na tabela de listagem
- Campo de comparação opcional: título original (via `supplier_code` ou log)

**Critérios de aceite:**

- Bella consegue revisar e publicar lotes de produtos sem abrir cada um
- Publicação em lote muda `status` de `draft` para `published`

---

### Passo 3 — Refinamento de Categorias

**Objetivo:** consolidar as categorias criadas pela IA e ajustar o que não fizer sentido.

**Quando executar:** após o enriquecimento de pelo menos uma amostra representativa.

**O que fazer:**

- Revisar categorias criadas automaticamente
- Renomear, mesclar ou remover categorias duplicadas/confusas
- Ajustar associações de produto que ficaram erradas
- Confirmar que as categorias do seeder (`Linha Premium`, `Lançamentos`, `Brindes Funcionais`) estão sendo usadas

**Critérios de aceite:**

- Estrutura de categorias é coerente e navegável
- Filtro do catálogo público retorna agrupamentos com sentido comercial

---

### Passo 4 — Otimização de Imagens (Opcional mas Recomendado)

**Objetivo:** garantir que as imagens importadas estão em tamanho adequado para web.

**O que avaliar:**

- Peso médio dos WebPs gerados na importação
- Se necessário, um `catalog:optimize-media` que reprocessa com qualidade mais baixa
- Geração de thumbnails ou versões responsivas se o Intervention Image suportar

**Critério:** páginas do catálogo carregam bem em mobile em conexão 4G.

---

## Ordem de Execução Recomendada

```
1. Adicionar campo enriched_at (migration)
2. Implementar catalog:enrich
3. Rodar catalog:enrich --limit=50 --dry-run  (validar output)
4. Rodar catalog:enrich --limit=50             (amostra real)
5. Bella revisa amostra no Filament
6. Rodar catalog:enrich (lote completo)
7. Bella revisa e publica em lote
8. Revisar e consolidar categorias
9. Avaliar peso de imagens
```

---

## Dependências Externas

| Serviço       | Para quê                          | Status         |
|---------------|-----------------------------------|----------------|
| Gemini Flash  | Enriquecimento de título/descrição | Chave necessária |
| XBZ API       | Próximos syncs diários            | Credenciais ok  |
| Asia Import   | Sync complementar futuro          | Credenciais ok  |

---

## Notas Técnicas

- O sync da XBZ está agendado para rodar diariamente às 02:00 via scheduler.
- Novos produtos importados chegam sempre como `draft` e precisam passar pelo mesmo pipeline.
- O `catalog:enrich` deve ser seguro para rodar em qualquer momento sem impactar o catálogo publicado.
- Produtos sem imagem falham no sync (esperado — sem imagem, sem produto).
