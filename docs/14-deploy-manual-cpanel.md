# Deploy Manual no cPanel com Terminal

Este é o fluxo recomendado para produção quando há acesso ao terminal do cPanel.

A produção não depende mais de deploy automático por push. O GitHub Actions ficou apenas como execução manual emergencial (`workflow_dispatch`), para evitar publicação acidental.

## Estratégia

1. O código fica em um diretório fora do `public_html`, por exemplo:

```text
/home/USUARIO/bella-criativa
```

2. O domínio aponta para:

```text
/home/USUARIO/bella-criativa/public
```

3. O deploy é feito dentro do servidor com:

```bash
git pull
composer install
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. O `.env`, uploads e banco ficam preservados no servidor.

## Primeira Configuração

No terminal do cPanel:

```bash
cd ~
git clone git@github.com:kaicdteodoro/bella-criativa.git bella-criativa
cd bella-criativa
```

Se o cPanel não tiver chave SSH configurada para GitHub, use HTTPS:

```bash
git clone https://github.com/kaicdteodoro/bella-criativa.git bella-criativa
cd bella-criativa
```

Instale dependências PHP:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

Crie o `.env` somente na primeira vez:

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` no cPanel e confira principalmente:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_do_banco
DB_PASSWORD=senha_do_banco

FILESYSTEM_DISK=public
SESSION_SECURE_COOKIE=true
RESPONSE_CACHE_ENABLED=true
```

Depois rode:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Se ainda não existir usuário admin:

```bash
php artisan filament:user
```

## Deploy de Atualização

Antes de deployar, faça o build local e suba o commit para a `main`:

```bash
npm ci
npm run build
git add public/build resources/css resources/js resources/views
git commit -m "Mensagem do ajuste"
git push origin main
```

No terminal do cPanel:

```bash
cd ~/bella-criativa
php artisan down --render="errors::503" || true
git fetch origin main
git status --short
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan responsecache:clear || true
php artisan up
```

Se `git status --short` mostrar arquivos modificados em produção, pare e verifique antes do `git pull`.

## Validação Depois do Deploy

Abra no navegador:

- Home
- Catálogo
- Um produto publicado
- Admin Filament
- `/sitemap.xml`

No terminal:

```bash
php artisan about
```

Se o CSS não carregar, confira:

```bash
test -f public/build/manifest.json && echo "manifest ok"
ls -la public/build/assets | tail
```

## Rollback Seguro

Veja os commits recentes:

```bash
git log --oneline -5
```

Volte para o commit anterior conhecido:

```bash
php artisan down --render="errors::503" || true
git checkout HASH_DO_COMMIT
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan responsecache:clear || true
php artisan up
```

Depois, corrija a `main` no repositório com um revert e faça novo deploy normal.

## Regras de Segurança

- Nunca rode `cp .env.example .env` em atualização.
- Nunca commite `.env`.
- Não edite arquivos versionados direto em produção.
- Antes de `git pull`, sempre rode `git status --short`.
- Produção deve usar `APP_DEBUG=false`.
- O domínio deve apontar para `public/`, não para a raiz do projeto.
- `public/build/` precisa estar versionado ou enviado junto do commit para o CSS/JS carregar sem Node em produção.
