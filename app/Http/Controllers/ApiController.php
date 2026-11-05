<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Municipality;
use App\Models\TravelRoute;
use App\Support\Sections;
use App\Support\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** API pública de solo lectura usada por el mapa, el buscador, la galería, favoritos y rutas. */
class ApiController extends Controller
{
    public const MAP_SECTIONS = ['tourism', 'culture', 'gastronomy', 'coffee', 'entrepreneurship'];

    public function markers(Request $request)
    {
        $sections = array_values(array_filter(explode(',', (string) $request->query('secciones', implode(',', self::MAP_SECTIONS))), [Sections::class, 'exists']));
        return response()->json(['markers' => self::markerCards($sections ?: ['tourism']), 'counts' => self::departmentCounts()])
            ->header('Cache-Control', 'public, max-age=60');
    }

    public static function markerCards(array $sections): array
    {
        return Entry::cards(Entry::visible()->onMap()->withCard()->whereIn('section', $sections)->featuredFirst()->limit(500)->get());
    }

    /** Publicaciones visibles por departamento (slug => total). */
    public static function departmentCounts(): array
    {
        return Entry::visible()->join('departments', 'departments.id', '=', 'entries.department_id')
            ->groupBy('departments.slug')->selectRaw('departments.slug, COUNT(entries.id) AS total')
            ->pluck('total', 'slug')->map(fn ($n) => (int) $n)->all();
    }

    public function search(Request $request)
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        if (mb_strlen($q) < 2) {
            return ['results' => []];
        }
        $results = [];
        foreach (Entry::visible()->withCard()->search($q)->featuredFirst()->limit(8)->get() as $e) {
            $def = Sections::ALL[$e->section];
            $results[] = [
                'type' => 'entry', 'title' => $e->title, 'url' => $e->path(), 'thumb' => $e->cover?->url('thumb'), 'icon' => $def['icon'],
                'subtitle' => trim($def['label'] . ' · ' . ($e->municipality?->name ?? $e->department?->name ?? ''), ' ·'),
            ];
        }
        foreach (self::geoSearch($q, 5) as $g) {
            $results[] = $g['type'] === 'department'
                ? ['type' => 'department', 'title' => $g['name'], 'subtitle' => 'Departamento · ver en el mapa', 'url' => '/?departamento=' . $g['slug'], 'icon' => 'map']
                : ['type' => 'municipality', 'title' => $g['name'], 'subtitle' => 'Municipio de ' . $g['departmentName'] . ' · ver en el mapa',
                    'url' => '/?departamento=' . $g['departmentSlug'] . '&municipio=' . $g['slug'], 'icon' => 'pin'];
        }
        return ['results' => $results];
    }

    /** Departamentos y municipios por nombre (sin tildes). */
    public static function geoSearch(string $q, int $limit = 8): array
    {
        $n = Text::normalize($q);
        if ($n === '') {
            return [];
        }
        $like = '%' . Text::likeEscape($n) . '%';
        $deps = Department::whereRaw("search_name LIKE ? ESCAPE '!'", [$like])->orderBy('name')->limit($limit)->get()
            ->map(fn ($d) => ['type' => 'department', 'id' => $d->id, 'name' => $d->name, 'slug' => $d->slug]);
        $muns = Municipality::with('department')->whereRaw("search_name LIKE ? ESCAPE '!'", [$like])->orderBy('name')->limit($limit)->get()
            ->map(fn ($m) => ['type' => 'municipality', 'id' => $m->id, 'name' => $m->name, 'slug' => $m->slug,
                'departmentSlug' => $m->department->slug, 'departmentName' => $m->department->name, 'lat' => $m->lat, 'lng' => $m->lng]);
        return $deps->concat($muns)->values()->all();
    }

    public function gallery(Request $request)
    {
        $q = self::galleryQuery($request);
        $total = (clone $q)->count();
        $items = $q->with(['department', 'municipality', 'collection'])->offset(max(0, (int) $request->query('desde', 0)))->limit(24)->get();
        return ['items' => $items->map->toPublic(), 'total' => $total];
    }

    public static function galleryQuery(Request $request)
    {
        $tipo = $request->query('tipo');
        $q = Media::where('in_gallery', true)
            ->whereIn('kind', $tipo === 'video' ? ['video', 'embed'] : ($tipo === 'foto' ? ['image'] : ['image', 'video', 'embed']))
            ->orderByDesc('gallery_featured')->orderByDesc('created_at')->orderByDesc('id');
        if ($c = (int) $request->query('coleccion')) {
            $q->where('collection_id', $c);
        }
        if ($d = (int) $request->query('departamento')) {
            $q->where('department_id', $d);
        }
        if ($m = (int) $request->query('municipio')) {
            $q->where('municipality_id', $m);
        }
        if (Sections::exists((string) $request->query('categoria'))) {
            $q->where('gallery_section', $request->query('categoria'));
        }
        return $q;
    }

    /** Publicaciones por ids (favoritos y ruta personal), en el mismo orden solicitado. */
    public function entries(Request $request)
    {
        $ids = array_slice(array_values(array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))))), 0, 60);
        if (!$ids) {
            return ['items' => []];
        }
        $byId = collect(Entry::cards(Entry::visible()->withCard()->whereIn('id', $ids)->get()))->keyBy('id');
        return ['items' => collect($ids)->map(fn ($id) => $byId[$id] ?? null)->filter()->values()];
    }

    /** Multimedia de una publicación visible, para el visor (sin salir del mapa). */
    public function entryMedia(int $id)
    {
        $entry = Entry::visible()->with(['media', 'cover'])->findOrFail($id);
        $items = $entry->media->map->toPublic();
        $images = $items->where('kind', 'image')->values();
        if ($images->isEmpty() && $entry->cover) {
            $images = collect([$entry->cover->toPublic()]);
        }
        return [
            'title' => $entry->title,
            'images' => $images,
            'videos' => $items->whereIn('kind', ['video', 'embed'])->values(),
            'audios' => $items->where('kind', 'audio')->values(),
        ];
    }

    /** Rutas publicadas con sus paradas (líneas animadas del mapa). */
    public function routes()
    {
        return response()->json(['routes' => self::routeList()])->header('Cache-Control', 'public, max-age=60');
    }

    public static function routeList(): array
    {
        return TravelRoute::published()->with('cover')->orderByDesc('is_featured')->orderByDesc('updated_at')->limit(50)->get()
            ->map(fn ($r) => $r->toPublic())
            ->filter(fn ($r) => count(array_filter($r['stops'], fn ($s) => $s['lat'] !== null)) >= 2)
            ->values()->all();
    }

    public function department(string $slug)
    {
        return self::departmentInfo(Department::with(['region', 'cover'])->where('slug', $slug)->firstOrFail());
    }

    public static function departmentInfo(Department $dep): array
    {
        $counts = Entry::visible()->join('municipalities', 'municipalities.id', '=', 'entries.municipality_id')
            ->where('municipalities.department_id', $dep->id)->groupBy('municipalities.slug', 'municipalities.name')
            ->selectRaw('municipalities.slug, municipalities.name, COUNT(entries.id) AS total')->orderByDesc('total')->get();
        return [
            'id' => $dep->id,
            'name' => $dep->name,
            'slug' => $dep->slug,
            'capital' => $dep->capital,
            'summary' => $dep->summary,
            'region' => $dep->region?->name,
            'regionColor' => $dep->region?->color,
            'url' => '/departamentos/' . $dep->slug,
            'image' => $dep->cover?->url('medium'),
            'municipalityCounts' => $counts->pluck('total', 'slug')->map(fn ($n) => (int) $n),
            'municipalities' => $counts->map(fn ($c) => ['slug' => $c->slug, 'name' => $c->name, 'total' => (int) $c->total])->values(),
            'photos' => Media::where('kind', 'image')->where('department_id', $dep->id)->orderByDesc('gallery_featured')->limit(8)->get()->map->toPublic(),
            'total' => Entry::visible()->where('department_id', $dep->id)->count(),
        ];
    }

    public function municipality(string $dep, string $slug)
    {
        $d = Department::where('slug', $dep)->firstOrFail();
        $m = Municipality::where('department_id', $d->id)->where('slug', $slug)->firstOrFail();
        return [
            'id' => $m->id, 'name' => $m->name, 'department' => $d->name, 'lat' => $m->lat, 'lng' => $m->lng,
            'items' => Entry::cards(Entry::visible()->withCard()->where('municipality_id', $m->id)->featuredFirst()->limit(30)->get()),
        ];
    }
}
