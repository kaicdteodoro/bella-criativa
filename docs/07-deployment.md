# Bella Criativa — Guia de Deploy

## Estratégia do Piloto

Deploy em **cPanel da agência** com stack PHP/MySQL nativa.

- Laravel roda no mesmo hosting da agência
- MySQL é provisionado no próprio cPanel
- `public/` da aplicação aponta para o document root
- Assets são compilados antes do envio
- Não existe processo Node persistente em produção

---

## 1. Pré-requisitos no cPanel

- PHP 8.2+
- Extensões: `mbstring`, `fileinfo`, `gd` ou `imagick`, `pdo_mysql`, `zip`, `exif`
- MySQL disponível
- Acesso SSH ou Terminal do cPanel
- Composer habilitado

---

## 2. Estrutura de Publicação

Opção preferida:

```text
/home/usuario/bella-criativa/        ← projeto Laravel completo
/home/usuario/public_html/          ← aponta para bella-criativa/public
```

Se o cPanel não permitir apontar o document root:

1. manter o projeto fora do `public_html`
2. publicar o conteúdo de `public/` dentro de `public_html`
3. ajustar `index.php` para referenciar `../bella-criativa/vendor` e `../bella-criativa/bootstrap`

---

## 3. Build e Upload

Fluxo recomendado:

```bash
# local
composer install --no-dev --optimize-autoloader
npm install
npm run build

# depois enviar os arquivos gerados ao servidor
```

No servidor:

```bash
cd ~/bella-criativa
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Variáveis de Produção

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://belacriativa.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=bela_criativa
DB_USERNAME=usuario
DB_PASSWORD=senha

FILESYSTEM_DISK=public
WHATSAPP_NUMBER=5516999999999
```

---

## 5. Primeiro Provisionamento

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan filament:user
```

**Observação:** `filament:user` cria o primeiro acesso administrativo se o projeto usar o comando padrão do Filament.

---

## 6. Deploy de Atualização

**Importante:** `php artisan config:cache` (chamado por `optimize`/`optimize:clear` e também
implícito no `composer install --optimize-autoloader`) reescreve `bootstrap/cache/config.php`
com `file_put_contents()` — **não é atômico**. Rodar isso direto no servidor **ao vivo, com
tráfego real**, cria uma janela de milissegundos em que o arquivo está truncado/vazio. Um
visitante que bater o site nesse instante recebe `MissingAppKeyException` — é isso, e não
"cache do navegador", que já causou o site aparecer sem CSS mais de uma vez em produção.

Por isso, **todo deploy/atualização em produção deve ser envolvido em modo de manutenção**:

```bash
cd ~/bella-criativa
php artisan down --retry=5

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up
```

`php artisan down` serve a página de manutenção do Laravel (estática, não depende de
config/vite) pra qualquer visitante que bater o site durante essa janela, em vez de deixar
a exceção estourar crua. Nunca rode `composer install`/`optimize`/`config:cache` direto no
diretório servido, sem `artisan down` antes, mesmo que pareça "só uma correção rápida".

Se houver mudança de CSS/JS:

1. rodar `npm run build` localmente
2. subir `public/build/` atualizado junto do deploy

---

## 7. Importação em Produção

Via SSH:

```bash
php artisan catalog:import storage/app/import/products.xlsx
php artisan catalog:import storage/app/import/products.xlsx --dry-run
php artisan catalog:import storage/app/import/products.xlsx --limit=20
```

Via admin:

- o usuário envia a planilha no Filament
- o sistema salva o arquivo temporariamente
- uma action dispara o command internamente
- o resultado volta com resumo do lote

---

## 8. Cache e Invalidação

- `responsecache` cacheia páginas públicas
- salvar produto ou categoria invalida cache relacionado
- em incidentes operacionais:

```bash
php artisan responsecache:clear
php artisan optimize:clear
```

---

## 9. Backups

Mínimo do piloto:

- dump diário do MySQL
- backup de `storage/app/public/media/`
- backup do `.env`

Se o cPanel permitir cron:

```bash
mysqldump -u USUARIO -p'SENHA' bela_criativa > ~/backups/bela_criativa.sql
```

---

## 10. Checklist de Go-Live

- [ ] domínio apontado corretamente
- [ ] HTTPS ativo
- [ ] `.env` configurado
- [ ] `php artisan migrate --force` executado
- [ ] `php artisan storage:link` criado
- [ ] primeiro usuário Filament criado
- [ ] importação de teste com `--limit=5` concluída
- [ ] produto publicado acessível no frontend
- [ ] `/sitemap.xml` responde
- [ ] preview de WhatsApp validado em um PDP
