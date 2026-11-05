<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\PublicController;
use App\Support\Sections;
use Illuminate\Support\Facades\Route;

// ---------- Sitio público ----------
Route::get('/', [PublicController::class, 'home'])->name('home');
foreach (Sections::ALL as $key => $def) {
    Route::get('/' . $def['path'], fn (\Illuminate\Http\Request $r) => app(PublicController::class)->section($r, $key))->name('section.' . $key);
}
Route::get('/galeria', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/buscar', [PublicController::class, 'search'])->name('search');
Route::get('/favoritos', [PublicController::class, 'favorites']);
Route::get('/mi-ruta', [PublicController::class, 'myRoute']);
Route::get('/rutas', [PublicController::class, 'routes']);
Route::get('/rutas/{slug}', [PublicController::class, 'route']);
Route::get('/departamentos/{slug}', [PublicController::class, 'department']);

// ---------- API pública (JSON, solo lectura) ----------
Route::prefix('api')->middleware('throttle:api')->group(function () {
    Route::get('/mapa', [ApiController::class, 'markers']);
    Route::get('/buscar', [ApiController::class, 'search']);
    Route::get('/galeria', [ApiController::class, 'gallery']);
    Route::get('/publicaciones', [ApiController::class, 'entries']);
    Route::get('/publicaciones/{id}/multimedia', [ApiController::class, 'entryMedia'])->whereNumber('id');
    Route::get('/rutas', [ApiController::class, 'routes']);
    Route::get('/departamentos/{slug}', [ApiController::class, 'department']);
    Route::get('/departamentos/{dep}/municipios/{slug}', [ApiController::class, 'municipality']);
});

require __DIR__ . '/admin.php';

// Ficha de cualquier publicación: /turismo/{slug}, /cafe/{slug}, etc. (al final).
$paths = implode('|', array_column(Sections::ALL, 'path'));
Route::get('/{path}/{slug}', [PublicController::class, 'entry'])->where(['path' => $paths, 'slug' => '[a-z0-9-]+']);
