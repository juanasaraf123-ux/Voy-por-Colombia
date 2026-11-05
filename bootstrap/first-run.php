<?php

/**
 * Preparación automática en la primera visita (pensado para XAMPP: copiar la carpeta y abrir).
 * - Comprueba la versión de PHP y las extensiones necesarias, con un mensaje claro si falta algo.
 * - Si no existe .env, lo crea a partir de .env.example con una clave secreta aleatoria propia de este equipo
 *   (la clave nunca está en el repositorio).
 * - Con SQLite (valor por defecto), copia la base incluida (departamentos, municipios y demostración)
 *   a storage/database.sqlite si aún no existe o está vacía.
 */

function vpc_setup_fail(string $title, string $html): never
{
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Voy por Colombia · Instalación</title>'
        . '<body style="font:17px/1.6 system-ui,sans-serif;max-width:660px;margin:12vh auto;padding:0 20px;color:#1f2a24">'
        . '<h1 style="font-size:1.6rem">' . $title . '</h1>' . $html . '<p>Luego recarga esta página.</p></body>';
    exit;
}

if (PHP_VERSION_ID < 80200) {
    vpc_setup_fail('Se necesita una versión más nueva de XAMPP',
        '<p>Este sitio necesita PHP 8.2 o superior y tu XAMPP tiene PHP ' . htmlspecialchars(PHP_VERSION) . '. Descarga XAMPP 8.2 desde apachefriends.org.</p>');
}

$missing = array_filter(['pdo', 'mbstring', 'openssl', 'fileinfo', 'tokenizer', 'ctype'], fn ($e) => !extension_loaded($e));
if ($missing) {
    vpc_setup_fail('Falta activar extensiones de PHP',
        '<p>Activa en <b>php.ini</b> (panel de XAMPP → Apache → Config → PHP (php.ini)) estas extensiones quitando el <b>;</b> del inicio de su línea, guarda y reinicia Apache: <b>'
        . htmlspecialchars(implode(', ', $missing)) . '</b>.</p>');
}

$root = dirname(__DIR__);
$envFile = $root . '/.env';

if (!is_file($envFile)) {
    $example = is_file($root . '/.env.example') ? (string) file_get_contents($root . '/.env.example') : "APP_KEY=\n";
    $key = 'base64:' . base64_encode(random_bytes(32));
    $env = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $example, 1) ?? $example;
    if (@file_put_contents($envFile, $env) === false) {
        vpc_setup_fail('No se pudo crear la configuración',
            '<p>La carpeta del sitio no permite escribir. Revisa que <b>' . htmlspecialchars($root) . '</b> no sea de solo lectura.</p>');
    }
}

// .env copiado a mano sin clave: se completa con una clave propia de este equipo.
$envText = (string) file_get_contents($envFile);
if (preg_match('/^APP_KEY=\s*$/m', $envText)) {
    $envText = preg_replace('/^APP_KEY=\s*$/m', 'APP_KEY=base64:' . base64_encode(random_bytes(32)), $envText, 1) ?? $envText;
    @file_put_contents($envFile, $envText);
}

// Valor de DB_CONNECTION en .env (sin cargar Laravel todavía).
$connection = 'sqlite';
if (preg_match('/^DB_CONNECTION=(\w+)/m', (string) file_get_contents($envFile), $m)) {
    $connection = strtolower($m[1]);
}
if ($connection === 'sqlite' && !preg_match('/^DB_DATABASE=\S+/m', (string) file_get_contents($envFile))) {
    $db = $root . '/storage/database.sqlite';
    $bundled = $root . '/database/voy-por-colombia.sqlite';
    if (is_file($db) && is_file($bundled) && extension_loaded('pdo_sqlite')) {
        // Una base vacía (sin tablas) se reemplaza: suele quedar de un intento anterior.
        try {
            $check = new PDO('sqlite:' . $db);
            $tables = (int) $check->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table'")->fetchColumn();
            $check = null;
            if ($tables === 0) {
                foreach (['', '-wal', '-shm', '-journal'] as $suffix) {
                    @unlink($db . $suffix);
                }
            }
        } catch (Throwable) {
            // Si no se puede leer, Laravel mostrará el error.
        }
    }
    if (!extension_loaded('pdo_sqlite')) {
        vpc_setup_fail('Falta activar SQLite en PHP',
            '<p>En el panel de XAMPP, en la fila de Apache, pulsa <b>Config → PHP (php.ini)</b>, busca <b>;extension=pdo_sqlite</b>, borra el <b>;</b> del inicio, guarda y reinicia Apache (Stop y Start).</p>');
    }
    if (!is_file($db) && is_file($bundled) && !@copy($bundled, $db)) {
        vpc_setup_fail('No se pudo crear la base de datos',
            '<p>Revisa que la carpeta <b>storage</b> permita escribir.</p>');
    }
}
