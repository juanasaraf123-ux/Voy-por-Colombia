<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
| Panel administrativo. Cada grupo exige sesión iniciada, cuenta activa y el permiso correspondiente,
| verificado en el servidor (no basta con ocultar botones en la interfaz).
*/
Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:login');
        Route::get('/instalar', [Admin\AuthController::class, 'setupForm'])->name('setup');
        Route::post('/instalar', [Admin\AuthController::class, 'setup'])->middleware('throttle:login');
    });

    Route::middleware(['auth', 'auth.session', 'active'])->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/perfil', [Admin\UserController::class, 'profile']);
        Route::post('/perfil', [Admin\UserController::class, 'updateProfile']);

        Route::middleware('can:content.manage')->group(function () {
            Route::get('/contenido/{section}', [Admin\EntryController::class, 'index']);
            Route::get('/contenido/{section}/nuevo', [Admin\EntryController::class, 'create']);
            Route::post('/contenido/{section}/nuevo', [Admin\EntryController::class, 'store']);
            Route::get('/publicaciones/{entry}/editar', [Admin\EntryController::class, 'edit']);
            Route::post('/publicaciones/{entry}/editar', [Admin\EntryController::class, 'update']);
            Route::post('/publicaciones/{entry}/estado', [Admin\EntryController::class, 'status']);
            Route::post('/publicaciones/{entry}/eliminar', [Admin\EntryController::class, 'destroy']);
            Route::get('/publicaciones/{entry}/vista-previa', [Admin\EntryController::class, 'preview']);
        });

        Route::middleware('can:media.manage')->group(function () {
            Route::get('/multimedia', [Admin\MediaController::class, 'index']);
            Route::post('/multimedia/subir', [Admin\MediaController::class, 'upload']);
            Route::post('/multimedia/video-en-linea', [Admin\MediaController::class, 'embed']);
            Route::get('/multimedia/{media}', [Admin\MediaController::class, 'edit']);
            Route::post('/multimedia/{media}', [Admin\MediaController::class, 'update']);
            Route::post('/multimedia/{media}/eliminar', [Admin\MediaController::class, 'destroy']);
            Route::get('/api/multimedia', [Admin\MediaController::class, 'apiList']);
        });

        Route::middleware('can:map.manage')->group(function () {
            Route::get('/mapa', [Admin\MapController::class, 'index']);
            Route::get('/mapa/departamentos/{department}', [Admin\MapController::class, 'editDepartment']);
            Route::post('/mapa/departamentos/{department}', [Admin\MapController::class, 'updateDepartment']);
            Route::post('/mapa/publicaciones/{entry}/ubicacion', [Admin\MapController::class, 'updateLocation']);
        });
        Route::get('/api/municipios', [Admin\MapController::class, 'apiMunicipalities']);

        Route::middleware('can:home.manage')->group(function () {
            Route::get('/inicio', [Admin\HomeController::class, 'edit']);
            Route::post('/inicio', [Admin\HomeController::class, 'update']);
        });

        Route::middleware('can:routes.manage')->group(function () {
            Route::get('/rutas', [Admin\RouteController::class, 'index']);
            Route::get('/rutas/nueva', [Admin\RouteController::class, 'create']);
            Route::post('/rutas/nueva', [Admin\RouteController::class, 'store']);
            Route::get('/rutas/{route}', [Admin\RouteController::class, 'edit'])->whereNumber('route');
            Route::post('/rutas/{route}', [Admin\RouteController::class, 'update'])->whereNumber('route');
            Route::post('/rutas/{route}/eliminar', [Admin\RouteController::class, 'destroy'])->whereNumber('route');
        });
        Route::get('/api/publicaciones', [Admin\RouteController::class, 'apiEntries'])->middleware('can:content.manage');

        Route::middleware('can:categories.manage')->group(function () {
            Route::get('/categorias', [Admin\CategoryController::class, 'index']);
            Route::post('/categorias', [Admin\CategoryController::class, 'store']);
            Route::post('/categorias/{category}', [Admin\CategoryController::class, 'update']);
            Route::post('/categorias/{category}/eliminar', [Admin\CategoryController::class, 'destroy']);
        });

        Route::middleware('can:users.manage')->group(function () {
            Route::get('/usuarios', [Admin\UserController::class, 'index']);
            Route::get('/usuarios/nuevo', [Admin\UserController::class, 'create']);
            Route::post('/usuarios/nuevo', [Admin\UserController::class, 'store']);
            Route::get('/usuarios/{user}', [Admin\UserController::class, 'edit']);
            Route::post('/usuarios/{user}', [Admin\UserController::class, 'update']);
            Route::post('/usuarios/{user}/estado', [Admin\UserController::class, 'toggle']);
        });
        Route::get('/auditoria', [Admin\AuditController::class, 'index'])->middleware('can:audit.view');
        Route::middleware('can:settings.manage')->group(function () {
            Route::get('/configuracion', [Admin\SettingsController::class, 'edit']);
            Route::post('/configuracion', [Admin\SettingsController::class, 'update']);
            Route::post('/configuracion/eliminar-demo', [Admin\SettingsController::class, 'removeDemo']);
        });
    });
});
