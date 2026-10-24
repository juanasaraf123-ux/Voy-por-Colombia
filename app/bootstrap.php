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
    if (App\Core\Request::wantsJson()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['error' => 'Error interno del servidor.'] + ($debug ? ['detalle' => $e->getMessage()] : []));
        return;
    }
    App\Core\View::errorPage(500, 'Algo salió mal', 'Tuvimos un problema al procesar tu solicitud. Inténtalo de nuevo en unos minutos.', $debug ? (string) $e : null);
});
