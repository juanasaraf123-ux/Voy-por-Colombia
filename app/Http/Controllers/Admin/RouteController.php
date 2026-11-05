<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Entry;
use App\Models\Media;
use App\Models\TravelRoute;
use App\Support\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/** Rutas turísticas curadas (varias paradas en orden). */
class RouteController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Routes/Index', [
            'title' => 'Rutas turísticas',
            'routes' => TravelRoute::with('cover')->withCount('stops')->orderByDesc('updated_at')->get()->map(fn ($r) => [
                'id' => $r->id, 'title' => $r->title, 'slug' => $r->slug, 'status' => $r->status, 'duration' => $r->duration,
                'stops' => $r->stops_count, 'thumb' => $r->cover?->url('thumb'), 'featured' => $r->is_featured, 'isDemo' => $r->is_demo,
            ]),
        ]);
    }

    public function create()
    {
        return $this->form(null);
    }

    public function edit(TravelRoute $route)
    {
        return $this->form($route);
    }

    private function form(?TravelRoute $route)
    {
        $route?->load(['cover', 'stops.cover', 'stops.department', 'stops.municipality']);
        return Inertia::render('Admin/Routes/Form', [
            'title' => $route ? 'Editar ruta' : 'Nueva ruta',
            'route' => $route ? $route->only(['id', 'title', 'slug', 'summary', 'body', 'duration', 'cover_media_id', 'status', 'is_featured', 'is_demo']) : null,
            'cover' => $route?->cover?->toPublic(),
            'stops' => $route ? $route->stops->map(fn ($s) => self::pick($s) + ['note' => $s->pivot->note ?? '']) : [],
        ]);
    }

    public static function pick(Entry $e): array
    {
        return [
            'id' => $e->id, 'title' => $e->title, 'section' => Sections::ALL[$e->section]['label'] ?? '', 'thumb' => $e->cover?->url('thumb'),
            'place' => trim(($e->municipality?->name ? $e->municipality->name . ', ' : '') . ($e->department?->name ?? ''), ', '),
            'status' => Entry::STATUSES[$e->status] ?? $e->status, 'hasCoords' => $e->lat !== null,
        ];
    }

    public function store(Request $request)
    {
        [$data, $stops] = $this->validated($request, null);
        $route = DB::transaction(function () use ($data, $stops, $request) {
            $route = TravelRoute::create($data + ['created_by' => $request->user()->id]);
            $route->stops()->sync($stops);
            return $route;
        });
        AuditLog::record('create', 'route', $route->id, 'Ruta: ' . $route->title);
        return redirect('/admin/rutas/' . $route->id)->with('success', 'Ruta creada.');
    }

    public function update(Request $request, TravelRoute $route)
    {
        [$data, $stops] = $this->validated($request, $route);
        DB::transaction(function () use ($route, $data, $stops) {
            $route->update($data);
            $route->stops()->sync($stops);
        });
        AuditLog::record('update', 'route', $route->id, 'Ruta: ' . $route->title);
        return back()->with('success', 'Ruta actualizada.');
    }

    public function destroy(TravelRoute $route)
    {
        $route->delete();
        AuditLog::record('delete', 'route', $route->id, 'Ruta: ' . $route->title);
        return redirect('/admin/rutas')->with('success', 'Ruta eliminada.');
    }

    private function validated(Request $request, ?TravelRoute $route): array
    {
        $v = $request->validate([
            'title' => 'required|string|min:3|max:190', 'summary' => 'nullable|string|max:400', 'body' => 'nullable|string|max:20000',
            'duration' => 'nullable|string|max:60', 'cover_media_id' => 'nullable|integer|exists:media,id', 'status' => 'required|in:draft,published,archived',
            'stops' => 'array|max:30', 'stops.*.id' => 'required|integer|exists:entries,id', 'stops.*.note' => 'nullable|string|max:255',
        ]);
        if (!empty($v['cover_media_id']) && Media::find($v['cover_media_id'])->kind !== 'image') {
            return back()->withErrors(['cover_media_id' => 'Selecciona una imagen.'])->throwResponse();
        }
        $stops = [];
        foreach (array_values($v['stops'] ?? []) as $i => $s) {
            $stops[$s['id']] = ['sort_order' => $i, 'note' => $s['note'] ?? null];
        }
        if ($v['status'] === 'published' && count($stops) < 2) {
            return back()->withErrors(['stops' => 'Una ruta publicada necesita al menos dos paradas.'])->throwResponse();
        }
        return [[
            'title' => $v['title'], 'slug' => TravelRoute::uniqueSlug($v['title'], $route?->id), 'summary' => $v['summary'] ?? null,
            'body' => $v['body'] ?? null, 'duration' => $v['duration'] ?? null, 'cover_media_id' => $v['cover_media_id'] ?? null,
            'status' => $v['status'], 'is_featured' => $request->boolean('is_featured'), 'is_demo' => $route && $route->is_demo && $request->boolean('keep_demo'),
        ], $stops];
    }

    /** Búsqueda de publicaciones para agregar paradas o destacados. */
    public function apiEntries(Request $request)
    {
        return ['items' => Entry::with(['cover', 'department', 'municipality'])->search($request->query('q'))->orderBy('title')->limit(15)->get()->map(fn ($e) => self::pick($e))];
    }
}
