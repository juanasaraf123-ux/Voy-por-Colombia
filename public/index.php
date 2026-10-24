<?php
declare(strict_types=1);

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
