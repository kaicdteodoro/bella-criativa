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
$home = '/home2/pensandobem';

putenv("HOME=$home");
putenv("COMPOSER_HOME=$home/.composer");

$php = '/opt/cpanel/ea-php84/root/usr/bin/php';
if (!file_exists($php)) {
    foreach (['/usr/bin/php', '/usr/local/bin/php'] as $c) {
        if (file_exists($c)) { $php = $c; break; }
    }
}

$style = 'font-family:monospace;background:#1a1a1a;color:#eee;padding:20px';
$pre   = 'background:#111;color:#0f0;padding:12px;white-space:pre-wrap;height:220px;overflow-y:auto';

function run(string $label, string $cmd, string $pre): int
{
    echo "<h3 style='color:#ff0'>▶ $label</h3>";
    echo "<pre style='$pre'>$ $cmd\n\n";
    ob_flush(); flush();
    $handle = popen($cmd . ' 2>&1', 'r');
    while (!feof($handle)) {
        $line = fgets($handle, 4096);
        if ($line !== false) { echo htmlspecialchars($line); ob_flush(); flush(); }
    }
    $code  = pclose($handle);
    $color = $code === 0 ? '#0f0' : '#f44';
    echo "\n<b style='color:$color'>Exit: $code</b></pre>";
    ob_flush(); flush();
    return $code;
}

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Bella Criativa Setup</title></head><body style='$style'>";
echo "<h1>Bella Criativa — Setup</h1>";
echo "<p><a href='?token=099c5863da2698db66adf41c&step=user' style='color:#0af'>→ Criar usuário admin</a> &nbsp;|&nbsp; <a href='?token=099c5863da2698db66adf41c&step=diag' style='color:#0af'>→ Diagnóstico 500</a></p>";

$step = $_GET['step'] ?? 'setup';

// ─── DIAGNÓSTICO ─────────────────────────────────────────────
if ($step === 'diag') {
    echo "<h2>Diagnóstico</h2>";

    // Storage writable?
    $dirs = ['storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache'];
    echo "<h3 style='color:#ff0'>Permissões de escrita</h3><pre style='$pre'>";
    foreach ($dirs as $d) {
        $path = "$root/$d";
        $ok   = is_writable($path) ? '✓' : '✗ NAO GRAVAVEL';
        echo "$d → $ok\n";
    }
    echo "</pre>";

    // .env readable?
    echo "<h3 style='color:#ff0'>.env</h3><pre style='$pre'>";
    echo file_exists("$root/.env") ? "✓ existe\n" : "✗ NAO EXISTE\n";
    $env = file_get_contents("$root/.env");
    preg_match('/APP_KEY=(.+)/', $env, $m);
    echo "APP_KEY: " . ($m[1] ?? '(vazio)') . "\n";
    preg_match('/DB_DATABASE=(.+)/', $env, $m);
    echo "DB_DATABASE: " . ($m[1] ?? '(vazio)') . "\n";
    echo "</pre>";

    // Fix cache
    echo "<h3 style='color:#ff0'>Limpar e re-cachear config</h3>";
    run('optimize:clear + optimize', "$php $root/artisan optimize:clear && $php $root/artisan optimize", $pre);

    // DB connection
    echo "<h3 style='color:#ff0'>Conexão com banco</h3><pre style='$pre'>";
    run('teste de conexão', "$php $root/artisan migrate:status 2>&1 | head -5", $pre);
    echo "</pre>";

    // Laravel log tail
    $log = "$root/storage/logs/laravel.log";
    echo "<h3 style='color:#ff0'>Causa raiz do erro (últimas exceções)</h3><pre style='$pre'>";
    if (file_exists($log)) {
        $content = file_get_contents($log);
        // Pega os últimos 3 blocos de log (separados por newline+{)
        $entries = preg_split('/\n(?=\{)/', $content);
        $last    = array_slice($entries, -3);
        foreach ($last as $entry) {
            $decoded = json_decode($entry, true);
            if ($decoded) {
                echo "── " . ($decoded['datetime'] ?? '') . " ──\n";
                echo "Nível:   " . ($decoded['level_name'] ?? '') . "\n";
                echo "Mensagem: " . ($decoded['message'] ?? '') . "\n";
                if (!empty($decoded['context']['exception'])) {
                    echo "Exceção:  " . $decoded['context']['exception'] . "\n";
                }
            } else {
                echo htmlspecialchars(substr($entry, 0, 500)) . "\n";
            }
            echo "\n";
        }
    } else {
        echo "Sem log ainda.";
    }
    echo "</pre>";

    echo "</body></html>";
    exit;
}

// ─── CRIAR USUÁRIO ADMIN ─────────────────────────────────────
if ($step === 'user') {
    echo "<h2>Criar usuário admin</h2>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = escapeshellarg($_POST['name'] ?? '');
        $email    = escapeshellarg($_POST['email'] ?? '');
        $password = escapeshellarg($_POST['password'] ?? '');

        $code = <<<PHP
\$u = App\Models\User::firstOrNew(['email' => $email]);
\$u->name = $name;
\$u->password = bcrypt($password);
\$u->save();
echo 'OK: ' . \$u->email;
PHP;
        $escaped = escapeshellarg($code);
        run('Criar usuário', "$php $root/artisan tinker --execute=$escaped", $pre);
    } else {
        echo <<<HTML
<form method="POST" style="max-width:400px">
  <input type="hidden" name="token" value="099c5863da2698db66adf41c">
  <p><label>Nome<br><input name="name" style="width:100%;padding:6px;background:#222;color:#eee;border:1px solid #555" value="Admin"></label></p>
  <p><label>E-mail<br><input name="email" type="email" style="width:100%;padding:6px;background:#222;color:#eee;border:1px solid #555"></label></p>
  <p><label>Senha<br><input name="password" type="password" style="width:100%;padding:6px;background:#222;color:#eee;border:1px solid #555"></label></p>
  <p><button type="submit" style="background:#0a0;color:#fff;padding:10px 20px;border:none;cursor:pointer">Criar admin</button></p>
</form>
HTML;
    }
    echo "</body></html>";
    exit;
}

// ─── SETUP PRINCIPAL ─────────────────────────────────────────
run('1. Composer install', "cd $root && composer install --no-dev --optimize-autoloader", $pre);

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
echo "<h3 style='color:#ff0'>▶ 2. .env criado</h3><pre style='$pre'>OK</pre>";
ob_flush(); flush();

run('3. php artisan key:generate', "$php $root/artisan key:generate --force", $pre);
run('4. php artisan migrate',      "$php $root/artisan migrate --force", $pre);
run('5. php artisan db:seed',      "$php $root/artisan db:seed --force", $pre);
run('6. php artisan storage:link', "$php $root/artisan storage:link --force", $pre);
run('7. php artisan optimize',     "$php $root/artisan optimize", $pre);

echo "<h2 style='color:#0f0'>✓ Setup concluído!</h2>";
echo "<p><a href='?token=099c5863da2698db66adf41c&step=user' style='color:#0af'>→ Criar usuário admin agora</a></p>";
echo "<p><a href='?token=099c5863da2698db66adf41c&step=diag' style='color:#0af'>→ Diagnóstico se der 500</a></p>";
echo "<p style='color:#f80'><b>IMPORTANTE:</b> Delete este arquivo após criar o usuário admin.</p>";
echo "</body></html>";
