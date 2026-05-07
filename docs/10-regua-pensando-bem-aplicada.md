# Regua Pensando Bem Aplicada ao Bella Criativa

## Objetivo

Aplicar no projeto a mesma regua de qualidade dos novos projetos da Pensando Bem:
- diferente + claro + estrategico ao mesmo tempo;
- sem linguagem institucional generica;
- consistencia entre conteudo, direcao visual e implementacao;
- acabamento premium sem sacrificar semantica, desempenho e conversao.

## Principios adotados neste projeto

1. Estrutura antes de estetica:
   - cada bloco precisa ter funcao clara (impacto, prova, logica, conversao ou respiro).
2. Copy com intencao:
   - remover termos internos/tecnicos e frases vagas;
   - manter texto orientado ao cliente final.
3. Sistema visual consistente:
   - evitar estilos soltos repetidos em componentes;
   - centralizar superficies, overlays e botoes em classes reutilizaveis.
4. Originalidade com referencia:
   - inspiracao editorial/minimalista sem replicar identidade de terceiros.
5. Guardrails tecnicos:
   - legibilidade, SEO server-rendered, acessibilidade basica e desempenho.

## Aplicacao inicial concluida

- Estilos compartilhados criados em `resources/css/app.css`:
  - `.pb-surface-warm`
  - `.pb-card-hover`
  - `.pb-overlay-dark`
  - `.pb-btn-primary`
  - `.pb-btn-outline`
- Componentes atualizados para usar esses estilos globais:
  - `resources/views/components/product-card.blade.php`
  - `resources/views/components/page-sections/blocks/category_mosaic.blade.php`
  - `resources/views/components/page-sections/blocks/hero.blade.php`
  - `resources/views/components/page-sections/blocks/cta.blade.php`
  - `resources/views/pages/products/show.blade.php`

## Segunda passada concluida

- Microtipografia padronizada com classes:
  - `.pb-eyebrow`
  - `.pb-eyebrow-dense`
- Fallbacks com linguagem de producao (sem "em construcao"):
  - `resources/views/pages/about.blade.php`
  - `resources/views/pages/contact.blade.php`
  - `resources/views/pages/products/show.blade.php`
- CTA de contato alinhado ao botao primario do sistema:
  - `resources/views/pages/contact.blade.php`
- Eyebrows de paginas-chave alinhados ao mesmo padrao:
  - `resources/views/pages/home.blade.php`
  - `resources/views/pages/categories/show.blade.php`
  - `resources/views/pages/products/index.blade.php`
  - `resources/views/pages/products/show.blade.php`

## Terceira passada concluida (QA visual + acessibilidade)

- Navegacao por teclado reforcada:
  - skip link no layout (`Ir para o conteudo`);
  - alvo principal com `id="main-content"`.
- Foco visivel padronizado com `.pb-focus-ring` em:
  - navegacao do header;
  - CTAs primarios e secundarios;
  - cards de produto;
  - modal de busca e filtros.
- Consistencia de CTA no header:
  - botao WhatsApp usando a base `.pb-btn-outline`.
- Ajuste de microcopy:
  - botao de busca alterado para `Buscar produtos`;
  - copy de introducao do catalogo com acentuacao correta (`Catálogo`).

## Proximos passos sugeridos (segunda passada)

1. Checklist de copy anti-generica em todas as paginas institucionais.
2. Auditoria de tokens/classes para reduzir valores arbitrarios restantes.
3. Revisao de ritmo visual por pagina (compressao x respiro, prova x conversao).
4. QA final com foco em:
   - consistencia de CTA;
   - navegacao por teclado;
   - contraste e performance percebida.
