# Manual do Operador — Bella Criativa
### Site institucional + catálogo de produtos personalizados

---

> **Como usar este documento com inteligência artificial**
>
> Você pode copiar este documento inteiro e colar em qualquer chat de IA
> (Claude em claude.ai, ChatGPT, Gemini, etc.) e fazer perguntas sobre como
> operar o seu site. O assistente vai responder com base no contexto da
> Bella Criativa especificamente — não com respostas genéricas sobre "sites em geral".
>
> Exemplo de uso: "Como faço para adicionar um novo produto ao catálogo?"
> ou "O que acontece quando eu mudo o status de um produto para rascunho?"

---

## H1. Informações do projeto

| Campo | Informação |
|-------|-----------|
| Site | Bella Criativa |
| URL do site | a confirmar após deploy |
| URL do painel administrativo | [URL do site]/admin |
| Data de entrega | junho de 2026 |
| Suporte técnico | Kaic — kaic.dtsolutions@gmail.com |
| WhatsApp do negócio | (16) 99449-2382 |
| Instagram | @bella_dpfc |

---

## H2. O que é este site

O site da Bella Criativa tem dois objetivos: apresentar a empresa para clientes em potencial e exibir o catálogo de produtos personalizados disponíveis para encomenda.

O site **não tem e-commerce** — o cliente não compra pelo site. Ele vê o produto, se interessa e entra em contato pelo WhatsApp para fechar o pedido. O site é uma vitrine, não uma loja.

O que o site faz:
- Mostra quem é a Bella Criativa e como ela trabalha
- Exibe o catálogo com filtros por categoria e busca por nome
- Leva o visitante direto ao WhatsApp para solicitar orçamento
- Aparece no Google quando alguém pesquisa por brindes personalizados

O que o site **não** faz:
- Não processa pagamentos
- Não tem área do cliente nem login para visitantes
- Não gerencia estoque ou pedidos
- Não envia e-mails automáticos de cotação

---

## H3. Como acessar o painel administrativo

**Endereço:** acesse `[URL do site]/admin` no navegador.

**Login:**
1. Digite seu e-mail: `kaic.dtsolutions@gmail.com`
2. Digite sua senha
3. Clique em "Entrar"

**Esqueceu a senha?** Clique em "Esqueci minha senha" na tela de login. Um e-mail será enviado com o link para redefinir.

**Quem tem acesso:** apenas usuários marcados como administrador. Para adicionar outro usuário, entre em contato com o suporte técnico.

---

## H4. Operações do dia a dia

### Gerenciar páginas do site (textos e blocos de conteúdo)

As páginas institucionais (Home, Sobre, Contato, etc.) são editadas em **Conteúdo → Páginas** no painel.

**Para editar uma página:**
1. Clique em **Conteúdo → Páginas** no menu lateral
2. Encontre a página que quer editar (Home, Sobre, Contato…)
3. Clique no ícone de lápis (editar)
4. Altere o título, o resumo ou os blocos de conteúdo
5. Clique em **Salvar** no canto superior direito

**Para ativar ou desativar um bloco dentro de uma página:**
- Cada página tem "Blocos da página" — seções como Hero, Texto rico, CTA, etc.
- Cada bloco tem um interruptor chamado "Bloco ativo"
- Desative o interruptor para esconder o bloco sem apagar o conteúdo

**Status da página:**
- **Publicado** = o visitante vê a página
- **Rascunho** = a página existe mas não aparece no site

---

### Gerenciar produtos do catálogo

Os produtos ficam em **Catálogo → Produtos**.

**Para ver todos os produtos:**
1. Clique em **Catálogo → Produtos** no menu lateral
2. Use os filtros de status (Publicado / Rascunho) para encontrar o que precisa

**Para editar um produto:**
1. Clique no ícone de lápis ao lado do produto
2. Edite o nome, a descrição curta, a descrição técnica ou as imagens
3. Clique em **Salvar**

**Status dos produtos:**
- **Publicado** = aparece no catálogo do site para os visitantes
- **Rascunho** = existe no sistema mas não aparece no site
- Você pode mudar o status a qualquer momento sem perder as informações

**Para destacar um produto (aparecer primeiro na home):**
- Na edição do produto, ative a opção **"Em destaque"**
- Produtos em destaque aparecem na seção "Seleção da Bella" da página inicial

**Para adicionar imagens a um produto:**
- Na edição do produto, procure o campo de galeria de imagens
- Faça upload das fotos diretamente pelo painel
- A primeira imagem da lista é usada como capa do produto

---

### Gerenciar categorias do catálogo

As categorias ficam em **Catálogo → Categorias**.

**Para criar uma categoria nova:**
1. Clique em **Catálogo → Categorias**
2. Clique em **Novo** no canto superior direito
3. Preencha o nome e, opcionalmente, uma descrição
4. O slug (endereço da URL) é gerado automaticamente — não precisa alterar
5. Clique em **Salvar**

**Para associar um produto a uma categoria:**
- Abra o produto para edição
- No campo **Categorias**, selecione uma ou mais categorias
- Um produto pode pertencer a mais de uma categoria

---

### Importar produtos em lote (via planilha do fornecedor)

A Bella Criativa recebe uma planilha de produtos do fornecedor e pode importá-la em lote pelo painel.

**Para importar:**
1. Vá em **Catálogo → Importar Catálogo** no menu lateral
2. Selecione a **API de origem** (fornecedor)
3. Escolha a **categoria de destino** e o limite de produtos
4. Clique em **Iniciar importação**
5. Aguarde — o processo roda em segundo plano e pode levar alguns minutos
6. O resultado aparece logo abaixo com quantos produtos foram importados e quais tiveram erro

**Histórico de importações:**
- Acesse **Catálogo → Histórico de Importações** para ver todas as importações anteriores
- Cada linha mostra: data, origem, quantos produtos foram processados, quantos publicados, quantos com erro

> **Importante:** produtos importados passam por uma avaliação automática de qualidade.
> Produtos com descrição incompleta ou sem imagem entram como **Rascunho** e não aparecem
> no site até você revisá-los e publicá-los manualmente.

---

## H5. Situações comuns

**"Quero mudar o texto da página inicial"**
Vá em Conteúdo → Páginas → Home → clique em editar → altere o bloco "Hero" ou o texto desejado → Salvar.

**"Quero que um produto apareça em destaque na home"**
Edite o produto → ative a opção "Em destaque" → Salvar. Ele vai aparecer na seção "Seleção da Bella" da página inicial (máximo 8 produtos destacados).

**"Quero tirar um produto do catálogo temporariamente"**
Edite o produto → mude o status de "Publicado" para "Rascunho" → Salvar. O produto some do site mas continua no sistema. Para colocar de volta, mude para "Publicado".

**"Quero criar uma categoria nova para uma linha de produto"**
Vá em Catálogo → Categorias → Novo → preencha o nome → Salvar. Depois edite os produtos dessa linha e adicione a nova categoria a eles.

**"Um produto apareceu com uma descrição estranha (ex: 'pedido mínimo de 50 peças')"**
Isso é texto interno do fornecedor que não deveria aparecer. Edite o produto → apague o trecho errado da descrição → Salvar. Se o problema aparecer em muitos produtos, entre em contato com o suporte técnico para rodar a limpeza automática.

**"O site abriu lento hoje"**
Espere alguns minutos e tente novamente. Se continuar lento por mais de 15 minutos, entre em contato com o suporte técnico. Não tente mexer em configurações para "resolver" — isso pode piorar.

**"Quero adicionar um produto manualmente (não via importação)"**
Vá em Catálogo → Produtos → clique em **Novo** → preencha todos os campos → faça upload das imagens → defina a categoria → mude o status para "Publicado" → Salvar.

**"Recebi um e-mail dizendo que o site foi hackeado ou está com problema"**
Não clique em links desse e-mail. Entre em contato diretamente com o suporte técnico informando o conteúdo do e-mail. Golpes desse tipo são comuns e muitas vezes falsos.

---

## H6. O que não mexer (importante)

As áreas abaixo existem no painel mas não devem ser alteradas sem suporte técnico:

**Configurações do servidor e hospedagem**
— Qualquer mudança errada pode tirar o site do ar. Se precisar de algo nessa área, peça ao suporte.

**Usuários e permissões**
— Não crie usuários administradores sem necessidade. Não compartilhe a senha de acesso ao painel.

**Slugs de páginas fixas**
— As páginas Home, Sobre, Contato e outras têm endereços fixos (slugs como `home`, `sobre`, `contato`). Alterar esses slugs quebra os links do site. Nunca mude o slug de uma página já existente.

**Códigos e scripts nas páginas**
— Não adicione scripts, códigos HTML ou iframes no conteúdo das páginas sem orientação técnica. Isso pode gerar brechas de segurança.

**Configurações do importador**
— As configurações de API e fornecedores estão no servidor, não no painel. Qualquer alteração nessa área requer suporte técnico.

---

## H7. Manutenção mensal (3 minutos)

Uma vez por mês, faça uma verificação rápida:

1. **Abra o site no celular** e veja se está carregando normalmente, se os botões de WhatsApp funcionam e se as imagens aparecem
2. **Confira o catálogo** — veja se há produtos publicados com informações desatualizadas (preços, nomes, imagens)
3. **Verifique as páginas institucionais** — confira se horários, dados de contato e textos estão corretos

Se encontrar qualquer problema, anote e trate um de cada vez pelo painel ou chame o suporte.

---

## H8. Quando chamar o suporte técnico

Chame o suporte técnico nas seguintes situações:

- O site ficou fora do ar por mais de 15 minutos
- Apareceu uma mensagem de erro no painel que você não entende
- Você ou alguém suspeita que o site foi invadido
- Você quer adicionar uma funcionalidade nova (ex: formulário, área nova, integração)
- Você não conseguiu resolver uma dúvida nem com este manual nem com AI
- Algo que você mudou no painel causou um comportamento inesperado no site

**Contato:** Kaic — kaic.dtsolutions@gmail.com

Ao entrar em contato, descreva: o que você tentou fazer, o que aconteceu, e se possível, uma captura de tela do erro.

---

## H9. Glossário

**Painel administrativo:** a área interna do site onde você gerencia conteúdo. Acessada em `/admin`. Visitantes comuns não têm acesso.

**Status Publicado:** o item (produto, página, bloco) está visível para os visitantes do site.

**Status Rascunho:** o item existe no sistema mas não aparece no site. Útil para preparar conteúdo antes de publicar.

**Slug:** o trecho da URL que identifica uma página ou produto. Ex: em `belladir.com.br/produtos/caneta-ecologica`, o slug é `caneta-ecologica`. Não deve ser alterado após publicação.

**Bloco:** seção dentro de uma página. Ex: Hero (o banner principal), Texto rico, CTA (chamada para ação), Mosaico de categorias.

**Em destaque:** marcação que faz o produto aparecer na seção especial da página inicial ("Seleção da Bella").

**Importação:** processo de trazer produtos em lote da planilha do fornecedor para o catálogo do site.

**Rascunho (produto importado):** produto que foi importado mas não passou nos critérios de qualidade automáticos. Fica invisível no site até ser revisado e publicado manualmente.

**WhatsApp como CTA:** o site não tem carrinho de compras. O botão "Solicitar orçamento" abre o WhatsApp da Bella Criativa diretamente. Todos os pedidos são fechados por lá.
