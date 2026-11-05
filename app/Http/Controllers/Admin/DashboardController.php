<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Municipality;
use App\Models\TravelRoute;
use App\Services\Demo;
use App\Support\Sections;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $count = fn ($sections) => Entry::whereIn('section', (array) $sections)->count();
        return Inertia::render('Admin/Dashboard', [
            'title' => 'Panel',
            'stats' => [
                ['Destinos', $count('tourism'), 'compass', '#0f9d77', '/admin/contenido/turismo'],
                ['Departamentos', Department::count(), 'map', '#2bb673', '/admin/mapa'],
                ['Municipios', Municipality::count(), 'pin', '#12b5b0', '/admin/mapa'],
                ['Fotografías', Media::where('kind', 'image')->count(), 'image', '#f2a900', '/admin/multimedia?tipo=image'],
                ['Videos', Media::whereIn('kind', ['video', 'embed'])->count(), 'video', '#ff3f8e', '/admin/multimedia?tipo=video'],
                ['Noticias', $count('news'), 'news', '#1d6fe0', '/admin/contenido/noticias'],
                ['Experiencias', $count(['gastronomy', 'coffee', 'culture']), 'sparkle', '#ff7a1a', '/admin/contenido/cultura'],
                ['Emprendimientos', $count('entrepreneurship'), 'store', '#7b3fe4', '/admin/contenido/emprendimiento'],
                ['Rutas', TravelRoute::count(), 'route', '#0f5c4a', '/admin/rutas'],
            ],
            'published' => Entry::visible()->count(),
            'drafts' => Entry::where('status', 'draft')->count(),
            'scheduled' => Entry::where('status', 'published')->where('published_at', '>', now())->count(),
            'noCoords' => Entry::where('section', 'tourism')->where('status', 'published')->where(fn ($q) => $q->whereNull('lat')->orWhereNull('lng'))->count(),
            'recent' => Entry::with('cover')->orderByDesc('updated_at')->limit(8)->get()->map(fn ($e) => [
                'id' => $e->id, 'title' => $e->title, 'section' => Sections::ALL[$e->section]['label'], 'color' => Sections::ALL[$e->section]['color'],
                'status' => $e->effectiveStatus(), 'thumb' => $e->cover?->url('thumb'), 'updatedAt' => $e->updated_at?->toIso8601String(),
            ]),
            'activity' => AuditLog::with('user:id,name')->orderByDesc('id')->limit(10)->get()->map(fn ($a) => [
                'id' => $a->id, 'user' => $a->user?->name ?? 'Sistema', 'action' => AuditLog::ACTIONS[$a->action] ?? $a->action, 'summary' => $a->summary, 'at' => $a->created_at?->toIso8601String(),
            ]),
            'demo' => Demo::counts(),
        ]);
    }
}
