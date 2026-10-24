<?php
declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\ApiController;
use App\Controllers\PublicController;

/** @var App\Core\Router $router */

// ---------- Sitio público ----------
$router->get('/', [PublicController::class, 'home']);
$router->get('/turismo', [PublicController::class, 'tourism']);
$router->get('/gastronomia', [PublicController::class, 'gastronomy']);
$router->get('/cafe', [PublicController::class, 'coffee']);
$router->get('/emprendimiento', [PublicController::class, 'entrepreneurship']);
$router->get('/cultura', [PublicController::class, 'culture']);
$router->get('/noticias', [PublicController::class, 'news']);
$router->get('/galeria', [PublicController::class, 'gallery']);
$router->get('/buscar', [PublicController::class, 'search']);
$router->get('/favoritos', [PublicController::class, 'favorites']);
$router->get('/mi-ruta', [PublicController::class, 'myRoute']);
$router->get('/rutas', [PublicController::class, 'routes']);
$router->get('/rutas/{slug}', [PublicController::class, 'route']);
$router->get('/departamentos/{slug}', [PublicController::class, 'department']);

// ---------- API pública (JSON, solo lectura) ----------
$router->get('/api/mapa', [ApiController::class, 'markers']);
$router->get('/api/buscar', [ApiController::class, 'search']);
$router->get('/api/galeria', [ApiController::class, 'gallery']);
$router->get('/api/publicaciones', [ApiController::class, 'entries']);
$router->get('/api/departamentos/{slug}', [ApiController::class, 'department']);
$router->get('/api/departamentos/{dep}/municipios/{slug}', [ApiController::class, 'municipality']);

// ---------- Panel administrativo ----------
$router->get('/admin/login', [Admin\AuthController::class, 'loginForm']);
$router->post('/admin/login', [Admin\AuthController::class, 'login']);
$router->post('/admin/logout', [Admin\AuthController::class, 'logout']);
$router->get('/admin/instalar', [Admin\AuthController::class, 'setupForm']);
$router->post('/admin/instalar', [Admin\AuthController::class, 'setup']);

$router->get('/admin', [Admin\DashboardController::class, 'index']);

$router->get('/admin/contenido/{section}', [Admin\EntryController::class, 'index']);
$router->get('/admin/contenido/{section}/nuevo', [Admin\EntryController::class, 'create']);
$router->post('/admin/contenido/{section}/nuevo', [Admin\EntryController::class, 'store']);
$router->get('/admin/publicaciones/{id}/editar', [Admin\EntryController::class, 'edit']);
$router->post('/admin/publicaciones/{id}/editar', [Admin\EntryController::class, 'update']);
$router->post('/admin/publicaciones/{id}/estado', [Admin\EntryController::class, 'status']);
$router->post('/admin/publicaciones/{id}/eliminar', [Admin\EntryController::class, 'destroy']);
$router->get('/admin/publicaciones/{id}/vista-previa', [Admin\EntryController::class, 'preview']);

$router->get('/admin/multimedia', [Admin\MediaController::class, 'index']);
$router->post('/admin/multimedia/subir', [Admin\MediaController::class, 'upload']);
$router->post('/admin/multimedia/video-en-linea', [Admin\MediaController::class, 'embed']);
$router->get('/admin/multimedia/{id}', [Admin\MediaController::class, 'edit']);
$router->post('/admin/multimedia/{id}', [Admin\MediaController::class, 'update']);
$router->post('/admin/multimedia/{id}/eliminar', [Admin\MediaController::class, 'destroy']);
$router->get('/admin/api/multimedia', [Admin\MediaController::class, 'apiList']);
$router->post('/admin/api/multimedia', [Admin\MediaController::class, 'apiUpload']);
$router->get('/admin/api/municipios', [Admin\MapController::class, 'apiMunicipalities']);
$router->get('/admin/api/publicaciones', [Admin\RouteController::class, 'apiEntries']);

$router->get('/admin/mapa', [Admin\MapController::class, 'index']);
$router->get('/admin/mapa/departamentos/{id}', [Admin\MapController::class, 'editDepartment']);
$router->post('/admin/mapa/departamentos/{id}', [Admin\MapController::class, 'updateDepartment']);
$router->post('/admin/mapa/publicaciones/{id}/ubicacion', [Admin\MapController::class, 'updateLocation']);

$router->get('/admin/inicio', [Admin\HomeController::class, 'edit']);
$router->post('/admin/inicio', [Admin\HomeController::class, 'update']);

$router->get('/admin/rutas', [Admin\RouteController::class, 'index']);
$router->get('/admin/rutas/nueva', [Admin\RouteController::class, 'create']);
$router->post('/admin/rutas/nueva', [Admin\RouteController::class, 'store']);
$router->get('/admin/rutas/{id}', [Admin\RouteController::class, 'edit']);
$router->post('/admin/rutas/{id}', [Admin\RouteController::class, 'update']);
$router->post('/admin/rutas/{id}/eliminar', [Admin\RouteController::class, 'destroy']);

$router->get('/admin/categorias', [Admin\CategoryController::class, 'index']);
$router->post('/admin/categorias', [Admin\CategoryController::class, 'store']);
$router->post('/admin/categorias/{id}', [Admin\CategoryController::class, 'update']);
$router->post('/admin/categorias/{id}/eliminar', [Admin\CategoryController::class, 'destroy']);

$router->get('/admin/usuarios', [Admin\UserController::class, 'index']);
$router->get('/admin/usuarios/nuevo', [Admin\UserController::class, 'create']);
$router->post('/admin/usuarios/nuevo', [Admin\UserController::class, 'store']);
$router->get('/admin/usuarios/{id}', [Admin\UserController::class, 'edit']);
$router->post('/admin/usuarios/{id}', [Admin\UserController::class, 'update']);
$router->post('/admin/usuarios/{id}/estado', [Admin\UserController::class, 'toggle']);

$router->get('/admin/perfil', [Admin\UserController::class, 'profile']);
$router->post('/admin/perfil', [Admin\UserController::class, 'updateProfile']);

$router->get('/admin/auditoria', [Admin\AuditController::class, 'index']);
$router->get('/admin/configuracion', [Admin\SettingsController::class, 'edit']);
$router->post('/admin/configuracion', [Admin\SettingsController::class, 'update']);
$router->post('/admin/configuracion/eliminar-demo', [Admin\SettingsController::class, 'removeDemo']);

// Ficha de cualquier publicación: /turismo/{slug}, /cafe/{slug}, etc. (debe ir al final).
$router->get('/{section}/{slug}', [PublicController::class, 'entry']);
