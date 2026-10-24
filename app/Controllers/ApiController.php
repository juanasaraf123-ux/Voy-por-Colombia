<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database as DB;
use App\Core\Request;
use App\Models\Entry;
use App\Models\Geo;
use App\Models\Media;
use App\Sections;

/** API pública de solo lectura usada por el mapa, el buscador, la galería, favoritos y rutas. */
final class ApiController
{
    public function markers(): void
    {
        $sections = array_values(array_filter(explode(',', Request::str('secciones', 'tourism', 200)), [Sections::class, 'exists']));
        $filters = ['sections' => $sections ?: ['tourism']];
        if ($cat = Request::str('categoria', '', 90)) {
            $filters['category'] = $cat;
        }
        if ($q = Request::str('q', '', 100)) {
            $filters['q'] = $q;
        }
        if ($dep = Request::str('departamento', '', 100)) {
            $d = Geo::departmentBySlug($dep);
            $filters['department_id'] = $d['id'] ?? -1;
        }
        header('Cache-Control: public, max-age=60');
        json_response([
            'markers' => Entry::markers($filters),
            'counts' => Geo::entryCountsByDepartment(),
        ]);
    }

    public function search(): void
    {
        $q = Request::str('q', '', 100);
        if (mb_strlen($q) < 2) {
            json_response(['results' => []]);
        }
        $results = [];
        foreach (Entry::query(['q' => $q, 'limit' => 8, 'order' => 'featured']) as $e) {
            $results[] = ['type' => 'entry', 'title' => $e['title'], 'subtitle' => trim(Sections::ALL[$e['section']]['label'] . ' · ' . ($e['municipality_name'] ?? $e['department_name'] ?? ''), ' ·'), 'url' => $e['url'], 'thumb' => $e['cover'] ? media_url($e['cover'], 'thumb') : null, 'icon' => Sections::ALL[$e['section']]['icon']];
        }
        foreach (Geo::search($q, 5) as $p) {
            $results[] = $p['type'] === 'department'
                ? ['type' => 'department', 'title' => $p['name'], 'subtitle' => 'Departamento', 'url' => url('/departamentos/' . $p['slug']), 'icon' => 'map']
                : ['type' => 'municipality', 'title' => $p['name'], 'subtitle' => 'Municipio · ' . $p['department_name'], 'url' => url('/turismo', ['departamento' => $p['department_slug'], 'municipio' => $p['slug']]), 'icon' => 'pin'];
        }
        json_response(['results' => $results]);
    }

    public function gallery(): void
    {
        $tipo = Request::str('tipo');
        $f = [
            'gallery' => 1,
            'kind' => $tipo === 'video' ? ['video', 'embed'] : ($tipo === 'foto' ? ['image'] : ['image', 'video', 'embed']),
            'collection_id' => Request::int('coleccion'),
            'department_id' => Request::int('departamento'),
            'municipality_id' => Request::int('municipio'),
            'gallery_section' => Sections::exists(Request::str('categoria')) ? Request::str('categoria') : null,
            'limit' => 24,
            'offset' => max(0, Request::int('desde', 0) ?? 0),
        ];
        $items = array_map([Media::class, 'toJson'], Media::list($f));
        json_response(['items' => $items, 'total' => Media::count($f)]);
    }

    /** Publicaciones por ids (favoritos y rutas personales), en el mismo orden solicitado. */
    public function entries(): void
    {
        $ids = array_slice(Request::ints('ids'), 0, 60);
        if (!$ids) {
            json_response(['items' => []]);
        }
        $byId = array_column(array_map([Entry::class, 'toCard'], Entry::query(['ids' => $ids, 'limit' => 60])), null, 'id');
        json_response(['items' => array_values(array_filter(array_map(static fn ($id) => $byId[$id] ?? null, $ids)))]);
    }

    public function department(string $slug): void
    {
        $dep = Geo::departmentBySlug($slug) ?? abort(404);
        $cover = $dep['cover_media_id'] ? Media::find((int) $dep['cover_media_id']) : null;
        $counts = DB::all('SELECT m.slug, COUNT(e.id) AS total FROM municipalities m JOIN entries e ON e.municipality_id = m.id WHERE m.department_id = ? AND ' . Entry::visibleSql() . ' GROUP BY m.slug', [$dep['id'], now()]);
        json_response([
            'id' => (int) $dep['id'],
            'name' => $dep['name'],
            'slug' => $dep['slug'],
            'capital' => $dep['capital'],
            'summary' => $dep['summary'],
            'region' => $dep['region_name'],
            'regionColor' => $dep['region_color'],
            'url' => url('/departamentos/' . $dep['slug']),
            'image' => $cover ? media_url($cover, 'medium') : null,
            'municipalityCounts' => array_column($counts, 'total', 'slug'),
            'total' => Entry::count(['department_id' => $dep['id']]),
        ]);
    }

    public function municipality(string $dep, string $slug): void
    {
        $d = Geo::departmentBySlug($dep) ?? abort(404);
        $found = Geo::municipalityBySlug((int) $d['id'], $slug) ?? abort(404);
        $m = Geo::municipality((int) $found['id']);
        json_response([
            'id' => (int) $m['id'],
            'name' => $m['name'],
            'department' => $m['department_name'],
            'items' => array_map([Entry::class, 'toCard'], Entry::query(['municipality_id' => $m['id'], 'limit' => 30, 'order' => 'featured'])),
        ]);
    }
}
