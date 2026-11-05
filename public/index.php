<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Servidor embebido de PHP (desarrollo): los archivos reales se entregan directamente.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Primera visita: comprobaciones, .env con clave propia y base de datos incluida (ver bootstrap/first-run.php).
require __DIR__.'/../bootstrap/first-run.php';

// Instalación en subcarpeta sin apuntar el servidor a public/ (XAMPP): el .htaccess de la raíz envía todo a public/,
// pero las URL del sitio no deben mostrar «/public».
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (str_ends_with($script, '/public/index.php') && !str_starts_with($uri, substr($script, 0, -strlen('index.php')))) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($script, 0, -strlen('/public/index.php')) . '/index.php';
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
