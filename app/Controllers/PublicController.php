<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Models\Category;
use App\Models\Entry;
use App\Models\Geo;
use App\Models\Media;
use App\Models\Route;
use App\Models\SectionInfo;
use App\Models\Setting;
use App\Sections;

final class PublicController
{
    private const PER_PAGE = 12;

    public function home(): void
    {
        $heroMedia = Media::findMany(array_map('intval', Setting::json('hero_media_ids')));
        $heroVideo = (int) Setting::get('hero_video_id') ? Media::find((int) Setting::get('hero_video_id')) : null;
        $strip = Entry::query(['section' => 'tourism', 'featured' => 1, 'order' => 'featured', 'limit' => 12]);
        $spotId = (int) Setting::get('hero_spotlight_id');
        $spotRow = ($spotId ? Entry::query(['ids' => [$spotId], 'limit' => 1]) : []) ?: array_slice($strip, 0, 1);
        $spotlight = $spotRow ? Entry::toCard($spotRow[0], Entry::mediaKinds([(int) $spotRow[0]['id']])[(int) $spotRow[0]['id']] ?? []) : null;
        $preferred = ['naturaleza', 'aventura', 'patrimonio', 'gastronomia', 'playas', 'turismo-rural', 'experiencias-comunitarias'];
        $cats = array_column(Category::forSection('tourism'), null, 'slug');
        $heroChips = array_values(array_filter(array_map(static fn ($slug) => $cats[$slug] ?? null, $preferred))) ?: array_slice(array_values($cats), 0, 5);
        View::render('public/home', [
            'pageTitle' => null,
            'strip' => array_map(static fn ($e) => Entry::toCard($e) + ['_cover' => $e['cover']], $strip),
            'spotlight' => $spotlight,
            'heroChips' => $heroChips,
            'heroMedia' => $heroMedia,
            'heroVideo' => $heroVideo && in_array($heroVideo['kind'], ['video', 'embed'], true) ? $heroVideo : null,
            'sections' => SectionInfo::forHome(),
            'gems' => Entry::query(['hidden_gem' => 1, 'order' => 'random', 'limit' => 5]),
            'news' => Entry::query(['section' => 'news', 'limit' => 4]),
            'photos' => Media::list(['gallery' => 1, 'kind' => 'image', 'featured' => 1, 'limit' => 9]),
            'events' => Entry::query(['upcoming' => 1, 'order' => 'event', 'limit' => 4]),
            'experiences' => Entry::query(['sections' => ['gastronomy', 'coffee', 'culture', 'entrepreneurship'], 'featured' => 1, 'order' => 'recent', 'limit' => 6]),
            'routes' => Route::list(true, 3, true),
            'regions' => Geo::regions(),
        ]);
    }

    public function tourism(): void
    {
        View::render('public/tourism', [
            'pageTitle' => 'Turismo',
            'info' => SectionInfo::get('tourism'),
            'categories' => Category::forSection('tourism'),
            'departments' => Geo::departments(),
            'regions' => Geo::regions(),
            'routes' => Route::list(true, 6),
            'featured' => Entry::query(['section' => 'tourism', 'featured' => 1, 'order' => 'featured', 'limit' => 8]),
            'gems' => Entry::query(['section' => 'tourism', 'hidden_gem' => 1, 'limit' => 8]),
            'initial' => [
                'categoria' => Request::str('categoria', '', 90),
                'departamento' => Request::str('departamento', '', 100),
                'q' => Request::str('q', '', 100),
                'lugar' => Request::int('lugar'),
            ],
            'bodyClass' => 'page-tourism',
        ]);
    }

    public function gastronomy(): void
    {
        $this->listing('gastronomy', 'public/gastronomy');
    }

    public function coffee(): void
    {
        $process = Entry::query(['section' => 'coffee', 'category' => 'proceso', 'limit' => 12, 'order' => 'title']);
        usort($process, static fn ($a, $b) => ((int) ($a['meta']['step'] ?? 99)) <=> ((int) ($b['meta']['step'] ?? 99)));
        $this->listing('coffee', 'public/coffee', [
            'process' => $process,
            'farms' => Entry::markers(['section' => 'coffee']),
        ]);
    }

    public function entrepreneurship(): void
    {
        $this->listing('entrepreneurship', 'public/entrepreneurship');
    }

    public function culture(): void
    {
        $month = Request::str('mes', date('Y-m'), 7);
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = date('Y-m');
        }
        $start = $month . '-01 00:00:00';
        $end = date('Y-m-t 23:59:59', (int) strtotime($start));
        $this->listing('culture', 'public/culture', [
            'month' => $month,
            'monthEvents' => Entry::query(['event_from' => $start, 'event_to' => $end, 'order' => 'event', 'limit' => 100]),
            'upcoming' => Entry::query(['upcoming' => 1, 'order' => 'event', 'limit' => 6]),
        ]);
    }

    public function news(): void
    {
        $this->listing('news', 'public/news', [
            'featuredNews' => Request::str('q') === '' && !Request::str('categoria') && (Request::int('pagina', 1) ?? 1) === 1
                ? Entry::query(['section' => 'news', 'featured' => 1, 'limit' => 1]) : [],
        ]);
    }

    /** Listado con filtros comunes: categoría, región, departamento, municipio, búsqueda y paginación. */
    private function listing(string $section, string $template, array $extra = []): void
    {
        $filters = [
            'section' => $section,
            'category' => Request::str('categoria', '', 90),
            'region' => Request::str('region', '', 80),
            'department_id' => Request::int('departamento'),
            'municipality_id' => Request::int('municipio'),
            'q' => Request::str('q', '', 100),
        ];
        $page = max(1, Request::int('pagina', 1) ?? 1);
        $total = Entry::count($filters);
        $items = Entry::query($filters + ['limit' => self::PER_PAGE, 'offset' => ($page - 1) * self::PER_PAGE, 'order' => $section === 'news' ? 'recent' : 'featured']);
        $municipalities = $filters['department_id'] ? Geo::municipalities($filters['department_id']) : [];
        View::render($template, $extra + [
            'pageTitle' => Sections::get($section)['label'],
            'section' => $section,
            'info' => SectionInfo::get($section),
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => (int) max(1, ceil($total / self::PER_PAGE)),
            'filters' => $filters,
            'categories' => Category::usedInSection($section),
            'departments' => Geo::departments(),
            'municipalities' => $municipalities,
            'regions' => Geo::regions(),
            'bodyClass' => 'page-' . Sections::get($section)['path'],
        ]);
    }

    public function entry(string $section, string $slug): void
    {
        $key = Sections::byPath($section);
        if (!$key) {
            abort(404);
        }
        $entry = Entry::findPublic($key, $slug);
        if (!$entry) {
            abort(404);
        }
        $this->renderEntry($entry, false);
    }

    /** También se usa para la vista previa del panel. */
    public function renderEntry(array $entry, bool $preview): void
    {
        $media = Entry::media((int) $entry['id']);
        View::render('public/entry', [
            'pageTitle' => $entry['title'],
            'metaDescription' => $entry['summary'],
            'ogImage' => $entry['cover'] ? media_url($entry['cover'], 'large') : null,
            'entry' => $entry,
            'def' => Sections::get($entry['section']),
            'images' => array_values(array_filter($media, static fn ($m) => $m['kind'] === 'image')),
            'videos' => array_values(array_filter($media, static fn ($m) => in_array($m['kind'], ['video', 'embed'], true))),
            'audios' => array_values(array_filter($media, static fn ($m) => $m['kind'] === 'audio')),
            'documents' => array_values(array_filter($media, static fn ($m) => $m['kind'] === 'document')),
            'nearby' => $preview ? [] : Entry::nearby($entry),
            'related' => $preview ? [] : Entry::query(['section' => $entry['section'], 'exclude' => $entry['id'], 'limit' => 3, 'order' => 'random']),
            'preview' => $preview,
            'bodyClass' => 'page-entry section-' . $entry['section'],
        ]);
    }

    public function gallery(): void
    {
        $filters = [
            'gallery' => 1,
            'kind' => Request::str('tipo') === 'video' ? ['video', 'embed'] : (Request::str('tipo') === 'foto' ? ['image'] : ['image', 'video', 'embed']),
            'collection_id' => Request::int('coleccion'),
            'department_id' => Request::int('departamento'),
            'municipality_id' => Request::int('municipio'),
            'gallery_section' => Sections::exists(Request::str('categoria')) ? Request::str('categoria') : null,
        ];
        View::render('public/gallery', [
            'pageTitle' => 'Galería de fotos',
            'info' => SectionInfo::get('gallery'),
            'items' => Media::list($filters + ['limit' => 24]),
            'total' => Media::count($filters),
            'collections' => \App\Core\Database::all('SELECT * FROM collections ORDER BY sort_order, name'),
            'departments' => Geo::departments(),
            'municipalities' => $filters['department_id'] ? Geo::municipalities($filters['department_id']) : [],
            'filters' => $filters + ['tipo' => Request::str('tipo'), 'categoria' => Request::str('categoria')],
            'bodyClass' => 'page-gallery',
        ]);
    }

    public function search(): void
    {
        $q = Request::str('q', '', 100);
        $groups = [];
        $places = [];
        if (mb_strlen($q) >= 2) {
            foreach (array_keys(Sections::ALL) as $key) {
                $rows = Entry::query(['section' => $key, 'q' => $q, 'limit' => 12, 'order' => 'featured']);
                if ($rows) {
                    $groups[$key] = $rows;
                }
            }
            $places = Geo::search($q, 12);
        }
        View::render('public/search', [
            'pageTitle' => $q !== '' ? "Resultados para «{$q}»" : 'Buscar',
            'q' => $q,
            'groups' => $groups,
            'places' => $places,
            'total' => array_sum(array_map('count', $groups)) + count($places),
        ]);
    }

    public function favorites(): void
    {
        View::render('public/favorites', ['pageTitle' => 'Mis favoritos']);
    }

    public function myRoute(): void
    {
        View::render('public/my-route', ['pageTitle' => 'Mi ruta', 'bodyClass' => 'page-my-route']);
    }

    public function routes(): void
    {
        View::render('public/routes', ['pageTitle' => 'Rutas turísticas', 'routes' => Route::list(true, 60)]);
    }

    public function route(string $slug): void
    {
        $route = Route::findPublic($slug) ?? abort(404);
        View::render('public/route', [
            'pageTitle' => $route['title'],
            'metaDescription' => $route['summary'],
            'route' => $route,
            'stops' => Route::stops((int) $route['id']),
            'bodyClass' => 'page-route',
        ]);
    }

    public function department(string $slug): void
    {
        $dep = Geo::departmentBySlug($slug) ?? abort(404);
        $entries = Entry::query(['department_id' => $dep['id'], 'limit' => 60, 'order' => 'featured']);
        $bySection = [];
        foreach ($entries as $e) {
            $bySection[$e['section']][] = $e;
        }
        View::render('public/department', [
            'pageTitle' => $dep['name'],
            'metaDescription' => $dep['summary'],
            'dep' => $dep,
            'cover' => $dep['cover_media_id'] ? Media::find((int) $dep['cover_media_id']) : null,
            'bySection' => $bySection,
            'total' => count($entries),
            'photos' => Media::list(['gallery' => 1, 'kind' => 'image', 'department_id' => $dep['id'], 'limit' => 8]),
            'bodyClass' => 'page-department',
        ]);
    }
}
