# Bella Criativa — Design System & UX Reference

Referência visual para o catálogo. O princípio central é: **o produto domina; a interface não compete**.

---

## 1. Filosofia Visual

- whitespace como estrutura, não como sobra
- imagem primeiro, texto depois, ação por último
- pouca ornamentação e nenhuma linguagem promocional agressiva
- aparência lapidável para reaproveitar o shell em outros clientes

---

## 2. Tokens

### Cores

```css
--color-bg: #ffffff;
--color-bg-soft: #faf9f7;
--color-text-primary: #1d1d1b;   /* quase-preto da paleta da marca */
--color-text-secondary: #626272; /* cinza da paleta da marca */
--color-border: #e8e5e0;
--color-accent: #c10040;         /* rosa/magenta — cor primária da marca */
--color-accent-hover: #a8003a;
--color-accent-soft: #fce8ef;
--color-bege: #ede5da;           /* bege da paleta da marca */
--color-surface-warm-start: #faf6f1;
--color-surface-warm-end: #f3ece3;
```

**Paleta completa da marca:**
- Rosa/Magenta `#c10040` — **primário**, CTAs, logo principal
- Laranja `#e86e00` — secundário
- Azul `#1212af` — secundário
- Bege `#ede5da` — superfícies neutras
- Cinza `#626272` — texto secundário
- Quase-preto `#1d1d1b` — texto primário, fundos escuros

**Regra:** uma cor de acento principal por tela. No site, o acento é sempre o rosa.

### Tipografia

```css
--font-editorial: "Cormorant Garamond", "Playfair Display", serif;
--font-ui: "DM Sans", "Helvetica Neue", sans-serif;

--text-xs: 12px;
--text-sm: 13px;
--text-base: 14px;
--text-lg: 18px;
--text-xl: 24px;
--text-2xl: 32px;
--text-3xl: 48px;
```

### Espaçamento

```css
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-6: 24px;
--space-8: 32px;
--space-12: 48px;
--space-16: 64px;
```

---

## 3. Grid e Responsividade

| Breakpoint | Catálogo | PDP |
|------------|----------|-----|
| 320–639px | 2 colunas | 1 coluna |
| 640–1023px | 3 colunas | 1 coluna |
| 1024px+ | 4 colunas | 2 colunas |

Regras:

- cards sem borda pesada
- proporção de imagem `3:4`
- gap menor no mobile, mais ar no desktop

---

## 4. Componentes-Chave

### 4.1 Card de Produto

- foto dominante
- nome do produto em 1 ou 2 linhas
- swatches discretos
- hover troca para segunda imagem
- sem CTA explícito no card

### 4.2 Filtros

- chips clicáveis
- sem dropdown no piloto
- URL reflete o estado do filtro
- posição sticky quando fizer sentido no mobile

### 4.3 PDP

Layout:

- galeria de mídia
- bloco informativo
- descrição curta sempre aberta
- acordeões para conteúdo técnico
- CTA WhatsApp em destaque

### 4.4 CTA WhatsApp

- texto direto
- botão cheio com cor de acento
- sticky bottom no mobile
- nunca competir com mais de um CTA primário

---

## 5. Motion e Interação

- transições curtas: `150ms` a `300ms`
- fade simples na troca de imagem
- acordeões com abertura suave
- evitar microanimações decorativas

---

## 6. Conteúdo e Tom

- títulos curtos
- descrições objetivas
- metadata secundária visualmente quieta
- SKU visível no PDP
- linguagem comercial sem urgência artificial

---

## 7. Conectores de Saída

### WhatsApp

Canal primário. O botão deve gerar mensagem pré-formatada:

```text
Olá! Tenho interesse no produto [NOME] (SKU [SKU]).
[LINK DO PRODUTO]
```

Preview rico depende de:

- `og:title`
- `og:description`
- `og:image` 1200×630
- `og:url`

### Link público

Canal secundário. Regras:

- URL limpa e canônica
- sem login
- compartilhável com filtros ativos no catálogo

### E-mail/PDF

Canal terciário e fora do piloto.

---

## 8. Performance

Metas:

| Métrica | Meta |
|---------|------|
| LCP | < 2.5s |
| CLS | < 0.1 |
| INP | < 200ms |

Regras:

- WebP obrigatório
- featured image otimizada para o card e para o PDP
- OG image gerada no import
- fonts com `font-display: swap`
- preferir skeletons a spinners

---

## 9. Acessibilidade

- área de toque mínima de `44x44px`
- contraste suficiente entre texto e fundo
- inputs com `font-size` mínimo de `16px`
- foco visível em links, chips e botões
- `alt` significativo para imagens de produto

---

## 10. Variáveis Lapidáveis por Cliente

```css
--client-accent: #ff5c35;
--client-logo-url: url('/logo.svg');
--client-font-editorial: "Cormorant Garamond";
--client-font-ui: "DM Sans";
```

Também podem variar:

- texto do CTA
- número do WhatsApp
- logo
- favicon
- tom das descrições institucionais
