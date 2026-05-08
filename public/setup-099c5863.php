<?php
if (($_GET['token'] ?? '') !== '099c5863da2698db66adf41c') {
    http_response_code(403);
    die('Forbidden');
}

$root = dirname(__DIR__);
$php  = PHP_BINARY;

function run(string $label, string $cmd): void
{
    echo "<h3>$label</h3><pre style='background:#111;color:#0f0;padding:12px;white-space:pre-wrap'>";
    flush();
    $output = [];
    exec($cmd . ' 2>&1', $output, $code);
    echo htmlspecialchars(implode("\n", $output));
    echo "\n\n<b>Exit: $code</b></pre>";
    flush();
}

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Bella Criativa Setup</title></head><body style="font-family:monospace;background:#1a1a1a;color:#eee;padding:20px">';
echo '<h1>Bella Criativa — Setup</h1>';

// 1. Composer
run(
    '1. Composer install',
    "cd $root && composer install --no-dev --optimize-autoloader"
);

// 2. .env
$env = <<<ENV
APP_NAME="Bella Criativa"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://bellacria.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pensandobem_bella_criativa
DB_USERNAME=pensandobem_bella_criativa
DB_PASSWORD="\$B3ll4Cr14t1v4"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

WHATSAPP_NUMBER=5516994492382
IMPORT_IMAGE_QUALITY=80
IMPORT_DOWNLOAD_TIMEOUT=30
IMPORT_DOWNLOAD_ATTEMPTS=3

CATALOG_AI_CURATION_ENABLED=false

CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="contato@bellacriativa.com.br"
MAIL_FROM_NAME="Bella Criativa"
ENV;

file_put_contents("$root/.env", $env);
echo '<h3>2. .env criado</h3><pre style="background:#111;color:#0f0;padding:12px">OK</pre>';

// 3. Key generate
run('3. php artisan key:generate', "$php $root/artisan key:generate --force");

// 4. Migrate
run('4. php artisan migrate', "$php $root/artisan migrate --force");

// 5. Seed
run('5. php artisan db:seed', "$php $root/artisan db:seed --force");

// 6. Storage link
run('6. php artisan storage:link', "$php $root/artisan storage:link --force");

// 7. Cache
run('7. php artisan optimize', "$php $root/artisan optimize");

echo '<h2 style="color:#0f0">✓ Setup concluído</h2>';
echo '<p style="color:#f80"><b>IMPORTANTE:</b> Delete este arquivo imediatamente após verificar que tudo funcionou.</p>';
echo '<p>Próximo passo: <code>php artisan filament:user</code> para criar o primeiro admin.</p>';
echo '</body></html>';
