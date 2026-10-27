<?php
declare(strict_types=1);

/**
 * Arranque de la aplicación: entorno, autoload, base de datos, errores y sesión.
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/helpers.php';

App\Core\Env::load(BASE_PATH . '/.env');

date_default_timezone_set((string) env('APP_TIMEZONE', 'America/Bogota'));
mb_internal_encoding('UTF-8');

$debug = filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

set_exception_handler(static function (Throwable $e) use ($debug): void {
    error_log((string) $e);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $e->getMessage() . PHP_EOL . ($debug ? $e->getTraceAsString() . PHP_EOL : ''));
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    // Base de datos no disponible o sin importar: guía de instalación en lugar de un error genérico.
    if (($e instanceof PDOException || $e instanceof RuntimeException) && !App\Core\Request::wantsJson()) {
        $code = $e instanceof PDOException ? (string) ($e->errorInfo[1] ?? $e->getCode()) : '';
        $hint = match (true) {
            str_contains($e->getMessage(), 'pdo_sqlite') => 'Falta activar SQLite en PHP. En el panel de XAMPP, en la fila de Apache, pulsa <b>Config → PHP (php.ini)</b>, busca <b>;extension=pdo_sqlite</b>, borra el <b>;</b> del inicio, guarda y reinicia Apache (Stop y Start).',
            str_contains($e->getMessage(), 'storage/database.sqlite') => e($e->getMessage()),
            str_contains($e->getMessage(), '2002') || $code === '2002' => 'MySQL no está encendido. Abre el panel de XAMPP y pulsa <b>Start</b> junto a <b>MySQL</b>.',
            $code === '1049' || $code === '1146' || $e->getCode() === '42S02' => 'Falta importar la base de datos. En phpMyAdmin (<b>http://localhost/phpmyadmin</b>) entra en <b>Importar</b> y elige el archivo <b>database/voy_por_colombia.sql</b> de esta carpeta.',
            $code === '1045' => 'MySQL rechazó el usuario o la contraseña. Si tu MySQL no usa el usuario <b>root</b> sin contraseña de XAMPP, copia <b>.env.example</b> como <b>.env</b> y escribe ahí tus datos.',
            default => null,
        };
        if ($hint !== null) {
            echo '<!doctype html><meta charset="utf-8"><title>Voy por Colombia · Instalación</title>'
                . '<body style="font:17px/1.6 system-ui,sans-serif;max-width:640px;margin:12vh auto;padding:0 20px;color:#1f2a24">'
                . '<h1 style="font-size:1.6rem">Casi listo</h1><p>' . $hint . '</p><p>Luego recarga esta página.</p></body>';
            return;
        }
    }
    if (App\Core\Request::wantsJson()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['error' => 'Error interno del servidor.'] + ($debug ? ['detalle' => $e->getMessage()] : []));
        return;
    }
    // En el propio computador (localhost) se muestra el detalle técnico para poder corregirlo; en internet nunca.
    $local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
        && !isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !isset($_SERVER['HTTP_X_REAL_IP']) && !isset($_SERVER['HTTP_FORWARDED']);
    App\Core\View::errorPage(500, 'Algo salió mal', 'Tuvimos un problema al procesar tu solicitud. Inténtalo de nuevo en unos minutos.', $debug || $local ? (string) $e : null);
});
