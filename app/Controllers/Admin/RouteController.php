<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\Request;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Route;
use App\Sections;

/** Rutas turísticas curadas. */
final class RouteController extends AdminController
{
    protected function permission(): ?string
    {
        return 'routes.manage';
    }

    public function index(): void
    {
        $this->render('routes/index', ['pageTitle' => 'Rutas turísticas', 'routes' => Route::list(false, 200)]);
    }

    public function create(): void
    {
        $this->render('routes/form', ['pageTitle' => 'Nueva ruta', 'route' => null, 'stops' => [], 'cover' => null]);
    }

    public function edit(string $id): void
    {
        $route = Route::find((int) $id) ?? throw new HttpException(404);
        $this->render('routes/form', [
            'pageTitle' => 'Editar ruta',
            'route' => $route,
            'stops' => Route::stops((int) $id, false),
            'cover' => $route['cover_media_id'] ? Media::find((int) $route['cover_media_id']) : null,
        ]);
    }

    public function store(): void
    {
        [$data, $stops] = $this->validated(null, url('/admin/rutas/nueva'));
        $id = Route::save(null, $data, $stops, (int) $this->user['id']);
        Audit::log('create', 'route', $id, 'Ruta: ' . $data['title']);
        flash('success', 'Ruta creada.');
        redirect(url('/admin/rutas/' . $id));
    }

    public function update(string $id): void
    {
        $route = Route::find((int) $id) ?? throw new HttpException(404);
        [$data, $stops] = $this->validated($route, url('/admin/rutas/' . $id));
        Route::save((int) $id, $data, $stops, (int) $this->user['id']);
        Audit::log('update', 'route', (int) $id, 'Ruta: ' . $data['title']);
        flash('success', 'Ruta actualizada.');
        redirect(url('/admin/rutas/' . $id));
    }

    public function destroy(string $id): void
    {
        $route = Route::find((int) $id) ?? throw new HttpException(404);
        DB::run('DELETE FROM routes WHERE id = ?', [(int) $id]);
        Audit::log('delete', 'route', (int) $id, 'Ruta: ' . $route['title']);
        flash('success', 'Ruta eliminada.');
        redirect(url('/admin/rutas'));
    }

    private function validated(?array $route, string $back): array
    {
        $errors = [];
        $title = Request::str('title', '', 250);
        if (mb_strlen($title) < 3 || mb_strlen($title) > 190) {
            $errors['title'] = 'El título debe tener entre 3 y 190 caracteres.';
        }
        $summary = Request::str('summary', '', 600);
        if (mb_strlen($summary) > 400) {
            $errors['summary'] = 'Máximo 400 caracteres.';
        }
        $status = in_array(Request::str('status'), ['draft', 'published', 'archived'], true) ? Request::str('status') : 'draft';
        $cover = Request::int('cover_media_id');
        if ($cover && (Media::find($cover)['kind'] ?? null) !== 'image') {
            $errors['cover_media_id'] = 'Selecciona una imagen.';
        }
        $ids = Request::ints('stops');
        $notes = (array) ($_POST['stop_notes'] ?? []);
        $valid = $ids ? array_map('intval', array_column(DB::all('SELECT id FROM entries WHERE id IN (' . DB::placeholders($ids) . ')', $ids), 'id')) : [];
        $stops = [];
        foreach ($ids as $i => $eid) {
            if (in_array($eid, $valid, true)) {
                $stops[] = ['entry_id' => $eid, 'note' => mb_substr(trim((string) ($notes[$i] ?? '')), 0, 255)];
            }
        }
        if ($status === 'published' && count($stops) < 2) {
            $errors['stops'] = 'Una ruta publicada necesita al menos dos paradas.';
        }
        if ($errors) {
            $this->fail($errors, $back);
        }
        return [[
            'title' => $title,
            'slug' => Route::uniqueSlug($title, $route['id'] ?? null),
            'summary' => $summary ?: null,
            'body' => Request::str('body', '', 20000) ?: null,
            'duration' => Request::str('duration', '', 60) ?: null,
            'cover_media_id' => $cover ?: null,
            'status' => $status,
            'is_featured' => Request::bool('is_featured') ? 1 : 0,
            'is_demo' => $route && (int) $route['is_demo'] === 1 && Request::bool('keep_demo') ? 1 : 0,
        ], $stops];
    }

    /** Búsqueda de publicaciones para agregar paradas o destacados (JSON). */
    public function apiEntries(): void
    {
        $q = Request::str('q', '', 100);
        $items = Entry::query(['q' => $q, 'limit' => 15, 'order' => 'title'], false);
        json_response(['items' => array_map(static fn ($e) => [
            'id' => (int) $e['id'], 'title' => $e['title'], 'section' => Sections::ALL[$e['section']]['label'],
            'place' => trim(($e['municipality_name'] ?? '') . ', ' . ($e['department_name'] ?? ''), ', '),
            'status' => status_label($e['effective_status']), 'thumb' => $e['cover'] ? media_url($e['cover'], 'thumb') : null,
            'hasCoords' => $e['lat'] !== null,
        ], $items)]);
    }
}
