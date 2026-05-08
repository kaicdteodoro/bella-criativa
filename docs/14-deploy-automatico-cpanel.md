# Deploy Automático no cPanel (GitHub Actions)

Este fluxo automatiza o deploy de `main` para produção no cPanel via SSH, com build de assets no GitHub.

## Como funciona

1. GitHub Actions roda em push na `main` (ou manual via `workflow_dispatch`).
2. Faz `npm ci` e `npm run build` no runner.
3. Envia arquivos para o servidor via `rsync` (sem `.env`, sem `vendor`).
4. No servidor, executa:
   - `composer install --no-dev`
   - `php artisan migrate --force`
   - `php artisan storage:link`
   - caches (`config/route/view`)

## Pré-requisitos no cPanel

- SSH habilitado.
- Composer disponível no servidor.
- Projeto Laravel em um caminho fixo (ex.: `/home/usuario/bella-criativa`).
- Domínio apontando para `public/` (ou `public_html` configurado conforme `docs/07-deployment.md`).

## Secrets necessários no GitHub

Configurar em **Repository > Settings > Secrets and variables > Actions**:

- `CPANEL_HOST`  
  Ex.: `server123.hostgator.com`

- `CPANEL_SSH_USER`  
  Usuário SSH do cPanel.

- `CPANEL_SSH_PRIVATE_KEY`  
  Chave privada (sem senha) correspondente à chave pública autorizada no servidor.

- `CPANEL_PROJECT_PATH`  
  Caminho absoluto do projeto no servidor. Ex.: `/home/usuario/bella-criativa`

## Primeiro provisionamento (manual, uma vez)

No servidor (SSH):

```bash
cd ~/bella-criativa
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan filament:user
```

Depois disso, os próximos deploys ficam automáticos.

Importante: em deploys posteriores, nao recrie nem sobrescreva o `.env`. Preserve o `APP_KEY` existente e apenas ajuste variaveis pontuais quando necessario.

## Go-live rápido (ordem recomendada)

1. Garantir `.env` de produção:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://seu-dominio`
   - `SESSION_SECURE_COOKIE=true`
2. Confirmar HTTPS ativo no domínio.
3. Executar o workflow manual na primeira vez (`workflow_dispatch`).
4. Validar:
   - Home, catálogo e produto com imagens
   - `/sitemap.xml`
   - `robots.txt`
   - Login admin (Filament)

## Rollback rápido

Se precisar voltar:

1. Reverter o commit problemático na `main`.
2. Fazer push.
3. Workflow roda novamente e restaura a versão anterior.
