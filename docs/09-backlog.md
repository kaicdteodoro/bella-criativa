# 09. Backlog de Execução

## Objetivo

Levar a Bella Criativa do estado atual de base funcional para entrega pronta de piloto, com foco em tarefas que agentes de IA consigam executar com baixo acoplamento, boa previsibilidade e critérios claros de conclusão.

## Estado Atual

Já existe base Laravel operacional com:

- stack definida e instalada
- páginas públicas iniciais
- models, migrations e seeders principais
- catálogo e institucional em Blade/Livewire/Alpine
- importador `catalog:import`
- página Filament para importar catálogo
- histórico de execuções de import
- suíte inicial de testes do importador

Ainda não está pronto para entrega final porque faltam endurecimento funcional, acabamento de admin, QA, operação e publicação.

## Como Um Agente Deve Trabalhar

### Regras

- Cada tarefa deve produzir um resultado verificável no repositório.
- Cada tarefa deve mexer em uma área de responsabilidade bem definida.
- Antes de editar, o agente deve ler os docs relevantes e o código da área.
- Ao concluir, o agente deve deixar validação executada ou explicitar o que faltou validar.
- Se uma tarefa depender de outra não concluída, o agente deve parar no limite da dependência e registrar o bloqueio.

### Formato de Entrega Esperado

Para cada item executado, o agente deve registrar:

- o que mudou
- arquivos principais alterados
- validação executada
- risco residual

### Definição de Pronto Global

Um item só conta como concluído quando tiver:

- implementação feita
- comportamento validado
- sem regressão óbvia no fluxo existente
- sem deixar TODO implícito não documentado

## Ordem Recomendada de Execução

1. Endurecer admin e CMS
2. Fechar catálogo público
3. Fechar PDP e SEO
4. Fechar importação real e observabilidade
5. Fechar conteúdo institucional e contato
6. Fechar performance e cache
7. Fechar testes e QA
8. Fechar operação e deploy
9. Fechar polimento visual final

---

## Épico 1: Endurecer Admin e CMS

### 1.1 Melhorar UX do importador no Filament

**Objetivo:** tornar a tela de importação utilizável por operação não técnica.

**Tarefas:**

- adicionar detalhamento por execução com lista de SKUs com falha
- adicionar paginação ou tela dedicada para histórico completo
- permitir download ou cópia do resumo da execução
- exibir metadados melhores: fornecedor, nome do arquivo, duração, modo dry-run
- destacar diferença entre `completed`, `completed_with_errors` e `failed`

**Dependências:** nenhuma

**Critérios de aceite:**

- operador entende o resultado de uma importação sem abrir log técnico
- histórico permite identificar facilmente última execução válida e últimas falhas

### 1.2 Refinar `ProductResource`

**Objetivo:** deixar o CRUD manual de produto seguro e previsível.

**Tarefas:**

- revisar campos obrigatórios e validações
- melhorar edição de galeria e ordenação de mídia
- impedir estados inconsistentes entre `featured_image`, `og_image` e galeria
- revisar relacionamento com categorias
- adicionar ajuda contextual curta nos campos mais sensíveis

**Dependências:** nenhuma

**Critérios de aceite:**

- produto pode ser criado e editado manualmente sem quebrar catálogo público
- admin não consegue salvar um registro obviamente inconsistente

### 1.3 Refinar `PageResource`

**Objetivo:** tornar o institucional realmente editável sem mexer em código.

**Tarefas:**

- revisar schema dos blocos de `page_sections`
- melhorar labels e organização dos blocos
- limitar combinações inválidas por tipo de seção
- adicionar preview textual mínimo dos blocos no admin
- garantir ordenação estável de seções

**Dependências:** nenhuma

**Critérios de aceite:**

- home, sobre, contato e landings podem ser mantidas via admin
- um editor não técnico entende o que cada bloco faz

### 1.4 Controle de acesso do admin

**Objetivo:** reduzir risco operacional.

**Tarefas:**

- revisar autenticação do Filament
- remover credenciais de bootstrap inseguras do fluxo real
- definir estratégia mínima de usuários para produção
- documentar criação do primeiro admin em ambiente real

**Dependências:** nenhuma

**Critérios de aceite:**

- ambiente de produção não depende de `admin@bellacriativa.local`
- fluxo de acesso inicial está documentado

---

## Épico 2: Fechar Catálogo Público

### 2.1 Refinar grid do catálogo

**Objetivo:** entregar listagem com padrão visual final do piloto.

**Tarefas:**

- revisar card de produto
- ajustar hierarquia entre imagem, título e microtexto
- revisar densidade de grid desktop/mobile
- revisar empty states
- revisar comportamento de hover e foco

**Dependências:** nenhuma

**Critérios de aceite:**

- catálogo parece editorial/premium e não genérico
- navegação mobile e desktop está consistente

### 2.2 Fechar filtros e busca

**Objetivo:** tornar exploração do catálogo fluida.

**Tarefas:**

- revisar filtro por categoria
- revisar busca por texto
- persistir estado em URL quando fizer sentido
- validar estados combinados de busca + filtro
- revisar reset de filtros

**Dependências:** nenhuma

**Critérios de aceite:**

- filtros funcionam sem confusão
- URL compartilhável representa o estado relevante quando aplicável

### 2.3 Revisar paginação / carregamento incremental

**Objetivo:** fechar comportamento final da listagem.

**Tarefas:**

- decidir entre paginação clássica, `load more` ou infinito controlado
- ajustar implementação Livewire para o modelo escolhido
- validar SEO e navegabilidade

**Dependências:** 2.1 e 2.2

**Critérios de aceite:**

- navegação do catálogo é previsível
- o padrão escolhido funciona bem em mobile

---

## Épico 3: Fechar PDP e SEO

### 3.1 Refinar página de produto

**Objetivo:** transformar o PDP em página final de conversão.

**Tarefas:**

- revisar galeria de imagens
- revisar bloco técnico e descrição
- revisar CTA principal para WhatsApp
- implementar CTA sticky mobile final
- revisar produtos relacionados ou navegação contextual, se fizer sentido

**Dependências:** nenhuma

**Critérios de aceite:**

- PDP comunica bem o produto
- CTA principal está claro sem agressividade visual

### 3.2 Fechar metadata SEO/OG

**Objetivo:** garantir indexação e preview corretos.

**Tarefas:**

- revisar títulos e descriptions dinâmicos
- revisar Open Graph e Twitter cards
- garantir fallback de imagem OG
- revisar canonical por página
- validar preview de produto e páginas institucionais

**Dependências:** 3.1

**Critérios de aceite:**

- home, categorias, PDP e páginas institucionais geram metadata correta
- preview de WhatsApp de PDP funciona com consistência

### 3.3 Sitemap e robots finais

**Objetivo:** fechar camada básica de descoberta para busca.

**Tarefas:**

- revisar `SitemapController`
- garantir inclusão apenas de rotas públicas relevantes
- revisar `robots.txt`
- validar URLs finais do piloto

**Dependências:** 3.2

**Critérios de aceite:**

- sitemap inclui páginas certas
- robots não bloqueia páginas públicas indevidamente

---

## Épico 4: Fechar Importação Real e Observabilidade

### 4.1 Validar planilha real do fornecedor

**Objetivo:** sair de fixture e validar entrada real.

**Tarefas:**

- rodar import com amostra real da Jaqmouse
- mapear variações reais de headers e conteúdo
- endurecer normalização de categoria, SKU e descrições
- endurecer casos de ZIP inválido ou incompleto

**Dependências:** nenhuma

**Critérios de aceite:**

- import passa por uma amostra real com resultado previsível
- diferenças entre fixture e realidade foram absorvidas no parser

### 4.2 Melhorar diagnósticos do importador

**Objetivo:** tornar falhas operáveis.

**Tarefas:**

- padronizar mensagens de erro por tipo
- separar falha de download, falha de planilha e falha de imagem
- melhorar resumo persistido em `import_runs`
- considerar armazenamento de warnings por SKU

**Dependências:** 4.1

**Critérios de aceite:**

- operador consegue diferenciar rapidamente causa de falha
- histórico fica útil para suporte posterior

### 4.3 Estratégia para imports grandes

**Objetivo:** evitar timeout e travamento de uso real.

**Tarefas:**

- decidir se o import no admin permanece síncrono ou vai para fila
- se for fila, implementar job e polling de status
- revisar timeout e limites de memória
- documentar fluxo operacional do import em produção

**Dependências:** 4.1 e 4.2

**Critérios de aceite:**

- imports grandes têm estratégia compatível com o ambiente cPanel

---

## Épico 5: Fechar Institucional e Captação

### 5.1 Finalizar home editorial

**Objetivo:** transformar a home em peça principal da marca.

**Tarefas:**

- refinar composição hero
- fechar ordem e presença dos blocos
- revisar destaques de categorias e produtos
- revisar CTA institucionais

**Dependências:** nenhuma

**Critérios de aceite:**

- home comunica marca e catálogo sem parecer template genérico

### 5.2 Finalizar páginas `Sobre`, `Contato`, `Lançamentos` e `Linha Premium`

**Objetivo:** fechar o institucional editável previsto no escopo.

**Tarefas:**

- estruturar conteúdo final de cada página
- revisar blocos e responsividade
- revisar coerência editorial com home

**Dependências:** 1.3

**Critérios de aceite:**

- páginas públicas institucionais estão completas e consistentes

### 5.3 Fechar captação via WhatsApp/contato

**Objetivo:** garantir conversão simples e robusta.

**Tarefas:**

- revisar links para WhatsApp
- revisar CTA por contexto de página
- decidir e implementar formulário de contato, se existir
- se houver formulário, definir antispam e destino de envio

**Dependências:** nenhuma

**Critérios de aceite:**

- visitante consegue iniciar contato sem fricção
- não existe CTA quebrado ou inconsistente

---

## Épico 6: Performance, Cache e Robustez

### 6.1 Revisar cache público

**Objetivo:** melhorar performance sem servir conteúdo errado.

**Tarefas:**

- revisar `responsecache`
- definir exclusões por rota e por contexto
- invalidar cache quando produto/categoria/página muda
- validar comportamento em catálogo e institucional

**Dependências:** catálogo e institucional estáveis

**Critérios de aceite:**

- páginas públicas cacheadas respondem rápido
- conteúdo atualizado aparece quando precisa aparecer

### 6.2 Revisar imagens e tamanhos

**Objetivo:** reduzir peso e manter boa qualidade.

**Tarefas:**

- revisar tamanhos de imagens do front
- revisar peso gerado pelo importador
- garantir boas dimensões para OG
- revisar placeholders/fallbacks quando imagem falta

**Dependências:** 3.1 e 4.1

**Critérios de aceite:**

- páginas principais carregam bem em mobile
- mídia importada não explode storage ou banda sem necessidade

### 6.3 Revisar falhas silenciosas

**Objetivo:** evitar que erro em produção passe despercebido.

**Tarefas:**

- revisar logging mínimo
- revisar fallback para páginas vazias ou produtos sem mídia
- revisar tratamento de 404 e páginas inexistentes

**Dependências:** nenhuma

**Critérios de aceite:**

- aplicação falha de forma controlada e observável

---

## Épico 7: Testes e QA

### 7.1 Cobrir fluxos públicos principais

**Objetivo:** aumentar segurança de evolução.

**Tarefas:**

- testes feature para home, categoria, PDP e páginas institucionais
- testes para metadata principal quando aplicável
- testes para filtros/busca Livewire

**Dependências:** catálogo e institucional estáveis

**Critérios de aceite:**

- fluxos públicos centrais têm cobertura mínima automatizada

### 7.2 Cobrir admin crítico

**Objetivo:** reduzir regressão no backoffice.

**Tarefas:**

- teste da página Filament de importação
- teste do histórico de imports
- testes básicos de recursos centrais do admin

**Dependências:** 1.1, 1.2 e 1.3

**Critérios de aceite:**

- regressões óbvias no admin são detectadas automaticamente

### 7.3 QA manual de piloto

**Objetivo:** validar comportamento final com checklist humano.

**Tarefas:**

- mobile iPhone/Android
- desktop Chrome/Safari
- WhatsApp preview
- navegação pública completa
- import real com amostra curta
- edição manual de conteúdo no admin

**Dependências:** épicos 1 a 6 substancialmente concluídos

**Critérios de aceite:**

- checklist manual executado sem blocker aberto

---

## Épico 8: Deploy, Operação e Handover

### 8.1 Fechar setup de produção no cPanel

**Objetivo:** preparar deploy real sem improviso.

**Tarefas:**

- revisar `.env` de produção
- revisar storage, symlink e permissões
- revisar cron/queue se necessário
- revisar build de assets antes do deploy

**Dependências:** base funcional estável

**Critérios de aceite:**

- existe procedimento de deploy reproduzível

### 8.2 Documentar runbook operacional

**Objetivo:** deixar uso e manutenção claros.

**Tarefas:**

- como subir nova importação
- como corrigir produto manualmente
- como publicar conteúdo institucional
- como limpar cache
- como validar preview de produto

**Dependências:** 8.1

**Critérios de aceite:**

- alguém da operação consegue tocar o básico sem depender de desenvolvedor

### 8.3 Fechar backlog de lançamento

**Objetivo:** encerrar o projeto com critérios explícitos.

**Tarefas:**

- revisar pendências abertas
- separar nice-to-have de blocker
- congelar escopo do piloto
- registrar versão pronta para deploy

**Dependências:** épicos anteriores

**Critérios de aceite:**

- existe decisão clara de “pronto para publicar”

---

## Itens Nice-to-Have

Executar só depois do piloto estar pronto.

- fila assíncrona completa para importações grandes
- analytics e eventos de clique em CTA
- variações mais sofisticadas de bloco editorial
- busca mais avançada com sinônimos
- biblioteca de componentes mais genérica para reaproveitar em outros clientes
- painéis/resumos operacionais no dashboard do Filament

---

## Tarefas Recomendadas para os Próximos Agentes

### Lote A: Alto impacto imediato

1. Melhorar UX do importador no Filament.
2. Refinar `PageResource` para edição institucional real.
3. Refinar grid do catálogo e PDP com direção visual final.
4. Fechar metadata SEO/OG em todas as páginas públicas.

### Lote B: Fechamento operacional

1. Validar import com planilha real do fornecedor.
2. Revisar cache e invalidação.
3. Criar testes feature dos fluxos públicos.
4. Fechar runbook de deploy e operação.

## Critério de Priorização

Se houver dúvida entre duas tarefas, priorizar nesta ordem:

1. algo que remove risco de publicação
2. algo que evita retrabalho estrutural
3. algo que melhora operação do admin
4. algo que melhora conversão pública
5. polimento visual secundário
