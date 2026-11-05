<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Department;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Municipality;
use App\Models\Region;
use App\Models\SectionInfo;
use App\Models\Setting;
use App\Models\TravelRoute;
use App\Support\Sections;
use App\Support\Text;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    private const PER_PAGE = 12;

    /** Portada: una sola pantalla con el mapa interactivo como protagonista. */
    public function home(Request $request)
    {
        $markers = ApiController::markerCards(ApiController::MAP_SECTIONS);
        $spotId = (int) Setting::value('hero_spotlight_id');
        $heroMedia = Media::whereIn('id', array_map('intval', Setting::json('hero_media_ids')))->get();
        $video = (int) Setting::value('hero_video_id') ? Media::find((int) Setting::value('hero_video_id')) : null;
        $preferred = ['naturaleza', 'aventura', 'patrimonio', 'gastronomia', 'playas', 'turismo-rural', 'experiencias-comunitarias'];
        $cats = Category::forSection('tourism')->get()->keyBy('slug');
        $chips = collect($preferred)->map(fn ($s) => $cats[$s] ?? null)->filter()->values();
        if ($chips->isEmpty()) {
            $chips = $cats->values()->take(6);
        }
        $chips = $chips->map(fn ($c) => $c->toPublic() + ['count' => collect($markers)->filter(fn ($m) => collect($m['categories'])->contains('slug', $c->slug))->count()]);
        $s = Setting::allValues();
        return Inertia::render('Home', [
            'texts' => [
                'kicker' => $s['hero_kicker'], 'title' => $s['hero_title'], 'subtitle' => $s['hero_subtitle'],
                'note' => $s['hero_note'], 'hint' => $s['map_hint'],
            ],
            'heroImage' => $heroMedia->first()?->url('large'),
            'heroVideo' => $video && in_array($video->kind, ['video', 'embed'], true) ? $video->toPublic() : null,
            'markers' => $markers,
            'counts' => ApiController::departmentCounts(),
            'regionColors' => Department::with('region')->get()->mapWithKeys(fn ($d) => [$d->slug => $d->region?->color ?? '#2bb673']),
            'regions' => Region::with('departments:id,region_id,slug')->orderBy('sort_order')->get()
                ->map(fn ($r) => ['slug' => $r->slug, 'name' => $r->name, 'color' => $r->color, 'departments' => $r->departments->pluck('slug')]),
            'chips' => $chips,
            'spotlightId' => $spotId ?: (collect($markers)->firstWhere('featured', true)['id'] ?? null),
            'routes' => ApiController::routeList(),
            // Panel derecho por defecto: lo más nuevo y las noticias destacadas.
            'newest' => ($n = Entry::visible()->withCard()->where('section', '<>', 'news')->recent()->first()) ? $n->toCard() : null,
            'news' => Entry::cards(Entry::visible()->withCard()->where('section', 'news')->orderByDesc('is_featured')->recent()->limit(3)->get()),
            'initial' => [
                'departamento' => $request->query('departamento'),
                'municipio' => $request->query('municipio'),
                'lugar' => $request->integer('lugar') ?: null,
                'ruta' => $request->query('ruta'),
                'region' => $request->query('region'),
                'categoria' => $request->query('categoria'),
            ],
        ]);
    }

    /** Listado de una sección con filtros (categoría, región, departamento, municipio, búsqueda) y paginación. */
    public function section(Request $request, string $key)
    {
        $q = Entry::visible()->withCard()->where('section', $key)
            ->inCategory($request->query('categoria'))
            ->search($request->query('q'));
        if ($r = $request->query('region')) {
            $q->whereHas('department.region', fn ($w) => $w->where('slug', $r));
        }
        if ($d = $request->integer('departamento')) {
            $q->where('department_id', $d);
        }
        if ($m = $request->integer('municipio')) {
            $q->where('municipality_id', $m);
        }
        $key === 'news' ? $q->recent() : $q->featuredFirst();
        $page = $q->paginate(self::PER_PAGE, ['entries.*'], 'pagina')->withQueryString();
        $extra = [];
        if ($key === 'coffee') {
            $extra['process'] = Entry::cards(Entry::visible()->withCard()->where('section', 'coffee')->inCategory('proceso')->get()
                ->sortBy(fn ($e) => (int) $e->metaValue('step', 99))->values());
        }
        if (in_array($key, ['culture', 'news', 'gastronomy', 'coffee'], true)) {
            $extra['upcoming'] = Entry::cards(Entry::visible()->withCard()->whereNotNull('event_start')
                ->whereRaw('COALESCE(event_end, event_start) >= ?', [now()->startOfDay()])->orderBy('event_start')->limit(6)->get());
        }
        return Inertia::render('Section', $extra + [
            'section' => ['key' => $key] + Sections::get($key),
            'info' => SectionInfo::present($key),
            'items' => Entry::cards($page->getCollection()),
            'pagination' => ['page' => $page->currentPage(), 'pages' => $page->lastPage(), 'total' => $page->total()],
            'filters' => [
                'categoria' => $request->query('categoria', ''), 'region' => $request->query('region', ''), 'q' => $request->query('q', ''),
                'departamento' => $request->integer('departamento') ?: '', 'municipio' => $request->integer('municipio') ?: '',
            ],
            'categories' => Category::forSection($key)->whereHas('entries', fn ($e) => $e->visible())->get()->map->toPublic(),
            'departments' => Department::orderBy('name')->get(['id', 'name', 'slug']),
            'municipalities' => $request->integer('departamento') ? Municipality::where('department_id', $request->integer('departamento'))->orderBy('name')->get(['id', 'name']) : [],
            'regions' => Region::orderBy('sort_order')->get(['slug', 'name', 'color']),
        ]);
    }

    public function entry(string $path, string $slug)
    {
        $key = Sections::byPath($path) ?? abort(404);
        $entry = Entry::visible()->withCard()->where('section', $key)->where('slug', $slug)->firstOrFail();
        return $this->renderEntry($entry, false);
    }

    /** Ficha de una publicación (también se usa para la vista previa del panel). */
    public function renderEntry(Entry $entry, bool $preview)
    {
        $entry->loadMissing(['media.department', 'media.municipality', 'media.collection']);
        $media = $entry->media->map->toPublic();
        $kinds = Entry::mediaKinds([$entry->id]);
        $def = Sections::get($entry->section);
        $fields = collect(Sections::fields($entry->section))->map(fn ($f) => $f + ['value' => $entry->metaValue($f['key'])])
            ->filter(fn ($f) => !in_array($f['key'], ['short_name', 'tagline', 'point_type', 'step'], true) && filled($f['value']))
            ->map(fn ($f) => $f['type'] === 'textarea' ? $f + ['html' => Text::markdown($f['value'])] : $f)->values();
        return Inertia::render('Entry', [
            'entry' => $entry->toCard($kinds[$entry->id] ?? []) + [
                'body' => Text::markdown($entry->body),
                'summaryFull' => $entry->summary,
                'authorName' => $entry->author_name,
                'effectiveStatus' => $entry->effectiveStatus(),
                'coverMedia' => $entry->cover?->toPublic(),
                'regionName' => $entry->department?->region?->name,
            ],
            'section' => ['key' => $entry->section] + $def,
            'fields' => $fields,
            'images' => $media->where('kind', 'image')->values(),
            'videos' => $media->whereIn('kind', ['video', 'embed'])->values(),
            'audios' => $media->where('kind', 'audio')->values(),
            'documents' => $media->where('kind', 'document')->values(),
            'nearby' => $preview ? [] : Entry::cards($entry->nearby()),
            'related' => $preview ? [] : Entry::cards(Entry::visible()->withCard()->where('section', $entry->section)->where('id', '<>', $entry->id)->inRandomOrder()->limit(3)->get()),
            'preview' => $preview,
        ]);
    }

    public function gallery(Request $request)
    {
        $q = ApiController::galleryQuery($request);
        return Inertia::render('Gallery', [
            'info' => SectionInfo::present('gallery'),
            'items' => (clone $q)->with(['department', 'municipality', 'collection'])->limit(24)->get()->map->toPublic(),
            'total' => $q->count(),
            'collections' => Collection::orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'municipalities' => $request->integer('departamento') ? Municipality::where('department_id', $request->integer('departamento'))->orderBy('name')->get(['id', 'name']) : [],
            'filters' => [
                'tipo' => $request->query('tipo', ''), 'categoria' => $request->query('categoria', ''), 'coleccion' => $request->integer('coleccion') ?: '',
                'departamento' => $request->integer('departamento') ?: '', 'municipio' => $request->integer('municipio') ?: '',
            ],
        ]);
    }

    public function search(Request $request)
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $groups = [];
        $places = [];
        if (mb_strlen($q) >= 2) {
            foreach (Sections::ALL as $key => $def) {
                $rows = Entry::cards(Entry::visible()->withCard()->where('section', $key)->search($q)->featuredFirst()->limit(12)->get());
                if ($rows) {
                    $groups[] = ['key' => $key, 'label' => $def['label'], 'color' => $def['color'], 'icon' => $def['icon'], 'items' => $rows];
                }
            }
            $places = ApiController::geoSearch($q, 12);
        }
        return Inertia::render('Search', ['q' => $q, 'groups' => $groups, 'places' => $places]);
    }

    public function favorites()
    {
        return Inertia::render('Favorites');
    }

    public function myRoute()
    {
        return Inertia::render('MyRoute');
    }

    public function routes()
    {
        return Inertia::render('Routes', ['routes' => ApiController::routeList()]);
    }

    public function route(string $slug)
    {
        $route = TravelRoute::published()->with('cover')->where('slug', $slug)->firstOrFail();
        return Inertia::render('RouteShow', ['route' => $route->toPublic() + ['body' => Text::markdown($route->body)]]);
    }

    public function department(string $slug)
    {
        $dep = Department::with(['region', 'cover'])->where('slug', $slug)->firstOrFail();
        $entries = Entry::visible()->withCard()->where('department_id', $dep->id)->featuredFirst()->limit(60)->get();
        $bySection = collect(Entry::cards($entries))->groupBy('section')->map(fn ($items, $key) => [
            'key' => $key, 'label' => Sections::ALL[$key]['label'], 'color' => Sections::ALL[$key]['color'], 'items' => $items->values(),
        ])->values();
        return Inertia::render('Department', ['dep' => ApiController::departmentInfo($dep) + ['cover' => $dep->cover?->toPublic()], 'groups' => $bySection]);
    }
}
