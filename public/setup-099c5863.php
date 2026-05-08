<?php
if (($_GET['token'] ?? '') !== '099c5863da2698db66adf41c') {
    http_response_code(403);
    die('Forbidden');
}

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@ini_set('max_execution_time', 300);
while (@ob_end_flush()) {}

$root = dirname(__DIR__);
$php  = PHP_BINARY;
$home = '/home2/pensandobem';

putenv("HOME=$home");
putenv("COMPOSER_HOME=$home/.composer");

function run(string $label, string $cmd): int
{
    echo "<h3 style='color:#ff0'>▶ $label</h3>";
    echo "<pre style='background:#111;color:#0f0;padding:12px;white-space:pre-wrap'>$ $cmd\n\n";
    ob_flush(); flush();

    $handle = popen($cmd . ' 2>&1', 'r');
    while (!feof($handle)) {
        $line = fgets($handle, 4096);
        if ($line !== false) {
            echo htmlspecialchars($line);
            ob_flush(); flush();
        }
    }
    $code = pclose($handle);
    $color = $code === 0 ? '#0f0' : '#f44';
    echo "\n<b style='color:$color'>Exit: $code</b></pre>";
    ob_flush(); flush();
    return $code;
}

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Bella Criativa Setup</title></head>';
echo '<body style="font-family:monospace;background:#1a1a1a;color:#eee;padding:20px">';
echo '<h1>Bella Criativa — Setup</h1>';
ob_flush(); flush();

// 1. Composer
run('1. Composer install', "cd $root && composer install --no-dev --optimize-autoloader");

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

XBZ_CNPJ=
XBZ_TOKEN=

ASIA_IMPORT_API_KEY=
ASIA_IMPORT_SECRET_KEY=

CATALOG_AI_CURATION_ENABLED=false
CATALOG_AI_PROVIDER=groq
CATALOG_AI_MODEL=llama-3.3-70b-versatile
GROQ_API_KEY=
CATALOG_AI_TIMEOUT=300
CATALOG_AI_BATCH_SIZE=3

RESPONSE_CACHE_ENABLED=true
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="contato@bellacriativa.com.br"
MAIL_FROM_NAME="Bella Criativa"
ENV;

file_put_contents("$root/.env", $env);
echo '<h3 style="color:#ff0">▶ 2. .env criado</h3><pre style="background:#111;color:#0f0;padding:12px">OK</pre>';
ob_flush(); flush();

// 3. Key generate
run('3. php artisan key:generate', "$php $root/artisan key:generate --force");

// 4. Migrate
run('4. php artisan migrate', "$php $root/artisan migrate --force");

// 5. Seed
run('5. php artisan db:seed', "$php $root/artisan db:seed --force");

// 6. Storage link
run('6. php artisan storage:link', "$php $root/artisan storage:link --force");

// 7. Optimize
run('7. php artisan optimize', "$php $root/artisan optimize");

echo '<h2 style="color:#0f0">✓ Setup concluído!</h2>';
echo '<p style="color:#f80"><b>IMPORTANTE:</b> Delete este arquivo imediatamente.</p>';
echo '<p>Próximo passo: criar o usuário admin no Filament.</p>';
echo '</body></html>';
