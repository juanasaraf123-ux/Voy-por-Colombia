<?php
declare(strict_types=1);

// Versiones antiguas de XAMPP traen PHP 7: el sitio necesita PHP 8.1 o superior.
if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Voy por Colombia</title>'
        . '<body style="font:17px/1.6 system-ui,sans-serif;max-width:640px;margin:12vh auto;padding:0 20px">'
        . '<h1>Se necesita una versión más nueva de XAMPP</h1><p>Este sitio necesita PHP 8.1 o superior y tu XAMPP tiene PHP '
        . htmlspecialchars(PHP_VERSION) . '. Descarga XAMPP 8.2 desde apachefriends.org, instálalo y vuelve a copiar la carpeta.</p></body>';
    exit;
}

// Servidor embebido de PHP: entrega los archivos estáticos directamente.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !str_ends_with($file, '.php')) {
        return false;
    }
}

require __DIR__ . '/../app/bootstrap.php';

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

Session::start();

$router = new Router();
require APP_PATH . '/routes.php';

try {
    $router->dispatch(Request::method(), Request::path());
} catch (HttpException $e) {
    $messages = [
        401 => ['Necesitas iniciar sesión', 'Inicia sesión para continuar.'],
        403 => ['Acceso no permitido', 'No tienes permiso para ver esta página o realizar esta acción.'],
        404 => ['No encontramos esta página', 'Puede que el enlace haya cambiado o que el contenido ya no esté publicado.'],
        405 => ['Método no permitido', 'La acción solicitada no está disponible.'],
        419 => ['La sesión expiró', 'Recarga la página e inténtalo de nuevo.'],
        422 => ['Datos no válidos', 'Revisa la información enviada.'],
    ];
    [$title, $message] = $messages[$e->status] ?? ['Error', 'Ocurrió un problema.'];
    if ($e->getMessage() !== '') {
        $message = $e->getMessage();
    }
    if (Request::wantsJson()) {
        json_response(['error' => $message], $e->status);
    }
    View::errorPage($e->status, $title, $message);
}
