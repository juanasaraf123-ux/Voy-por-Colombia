<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\Request;
use App\Models\Entry;
use App\Models\Geo;
use App\Models\Media;

/** Gestión del mapa: ubicación de publicaciones y datos de departamentos. */
final class MapController extends AdminController
{
    protected function permission(): ?string
    {
        return 'map.manage';
    }

    public function index(): void
    {
        $located = Entry::query(['geo' => 1, 'limit' => 500, 'order' => 'title'], false);
        $missing = DB::all("SELECT id, title, section, status FROM entries WHERE section <> 'news' AND (lat IS NULL OR lng IS NULL) ORDER BY title");
        $this->render('map/index', [
            'pageTitle' => 'Gestión del mapa',
            'markers' => array_map(static fn ($e) => [
                'id' => (int) $e['id'], 'title' => $e['title'], 'lat' => (float) $e['lat'], 'lng' => (float) $e['lng'],
                'status' => $e['status'], 'section' => $e['section'], 'edit' => url('/admin/publicaciones/' . $e['id'] . '/editar'),
            ], $located),
            'missing' => $missing,
            'departments' => Geo::departments(),
            'counts' => array_column(DB::all('SELECT department_id, COUNT(*) AS n FROM entries GROUP BY department_id'), 'n', 'department_id'),
        ]);
    }

    public function editDepartment(string $id): void
    {
        $dep = Geo::department((int) $id) ?? throw new HttpException(404);
        $this->render('map/department', [
            'pageTitle' => 'Departamento: ' . $dep['name'],
            'dep' => $dep,
            'regions' => Geo::regions(),
            'cover' => $dep['cover_media_id'] ? Media::find((int) $dep['cover_media_id']) : null,
            'municipalities' => Geo::municipalities((int) $id),
        ]);
    }

    public function updateDepartment(string $id): void
    {
        $dep = Geo::department((int) $id) ?? throw new HttpException(404);
        $errors = [];
        $region = Request::int('region_id');
        if ($region && !DB::value('SELECT id FROM regions WHERE id = ?', [$region])) {
            $errors['region_id'] = 'Región no válida.';
        }
        $cover = Request::int('cover_media_id');
        if ($cover && (Media::find($cover)['kind'] ?? null) !== 'image') {
            $errors['cover_media_id'] = 'Selecciona una imagen.';
        }
        $summary = Request::str('summary', '', 600);
        if (mb_strlen($summary) > 500) {
            $errors['summary'] = 'Máximo 500 caracteres.';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/mapa/departamentos/' . $id));
        }
        DB::update('departments', [
            'region_id' => $region ?: null,
            'capital' => Request::str('capital', '', 100) ?: null,
            'summary' => $summary ?: null,
            'cover_media_id' => $cover ?: null,
        ], 'id = :did', ['did' => (int) $id]);
        Audit::log('update', 'department', (int) $id, 'Departamento: ' . $dep['name']);
        flash('success', 'Departamento actualizado.');
        redirect(url('/admin/mapa/departamentos/' . $id));
    }

    /** Actualiza coordenadas arrastrando el marcador en el mapa (AJAX). */
    public function updateLocation(string $id): void
    {
        $entry = Entry::find((int) $id) ?? throw new HttpException(404);
        $lat = Request::float('lat');
        $lng = Request::float('lng');
        if ($lat === null || $lng === null || $lat < -5 || $lat > 14 || $lng < -83 || $lng > -66) {
            json_response(['error' => 'Coordenadas fuera de Colombia.'], 422);
        }
        DB::update('entries', ['lat' => round($lat, 6), 'lng' => round($lng, 6), 'updated_at' => now(), 'updated_by' => (int) $this->user['id']], 'id = :eid', ['eid' => (int) $id]);
        Audit::log('update', 'entry', (int) $id, 'Ubicación en el mapa: ' . $entry['title']);
        json_response(['ok' => true, 'lat' => round($lat, 6), 'lng' => round($lng, 6)]);
    }

    /** Municipios de un departamento (selects dependientes y selector de coordenadas). */
    public function apiMunicipalities(): void
    {
        $dep = Request::int('departamento');
        json_response(['items' => $dep ? Geo::municipalities($dep) : []]);
    }
}
