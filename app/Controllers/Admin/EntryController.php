<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\PublicController;
use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\Request;
use App\Models\Category;
use App\Models\Entry;
use App\Models\Geo;
use App\Models\Media;
use App\Sections;

/** Gestión de publicaciones de todas las secciones (Turismo, Gastronomía, Café, Emprendimiento, Cultura y Noticias). */
final class EntryController extends AdminController
{
    private const PER_PAGE = 20;

    protected function permission(): ?string
    {
        return 'content.manage';
    }

    private function sectionKey(string $path): string
    {
        return Sections::byPath($path) ?? throw new HttpException(404);
    }

    private function findOr404(int $id): array
    {
        return Entry::find($id) ?? throw new HttpException(404, 'La publicación no existe o fue eliminada.');
    }

    public function index(string $section): void
    {
        $key = $this->sectionKey($section);
        $filters = [
            'section' => $key,
            'status' => in_array(Request::str('estado'), ['draft', 'published', 'archived', 'scheduled'], true) ? Request::str('estado') : null,
            'q' => Request::str('q', '', 100),
            'category_id' => Request::int('categoria'),
            'department_id' => Request::int('departamento'),
        ];
        $page = max(1, Request::int('pagina', 1) ?? 1);
        $total = Entry::count($filters, false);
        $counts = [];
        foreach (DB::all('SELECT status, COUNT(*) AS n FROM entries WHERE section = ? GROUP BY status', [$key]) as $r) {
            $counts[$r['status']] = (int) $r['n'];
        }
        $this->render('entries/index', [
            'pageTitle' => Sections::get($key)['label'],
            'key' => $key,
            'def' => Sections::get($key),
            'items' => Entry::query($filters + ['limit' => self::PER_PAGE, 'offset' => ($page - 1) * self::PER_PAGE, 'order' => 'updated'], false),
            'filters' => $filters,
            'total' => $total,
            'page' => $page,
            'pages' => (int) max(1, ceil($total / self::PER_PAGE)),
            'counts' => $counts,
            'categories' => Category::forSection($key),
            'departments' => Geo::departments(),
        ]);
    }

    public function create(string $section): void
    {
        $key = $this->sectionKey($section);
        $this->form($key, null);
    }

    public function edit(string $id): void
    {
        $entry = $this->findOr404((int) $id);
        $this->form($entry['section'], $entry);
    }

    private function form(string $key, ?array $entry): void
    {
        $gallery = $entry ? Entry::media((int) $entry['id']) : [];
        $this->render('entries/form', [
            'pageTitle' => $entry ? 'Editar: ' . $entry['title'] : 'Nuevo ' . Sections::get($key)['singular'],
            'key' => $key,
            'def' => Sections::get($key),
            'entry' => $entry,
            'fields' => Sections::fields($key),
            'categories' => Category::forSection($key),
            'selectedCategories' => $entry ? array_map('intval', array_column($entry['categories'], 'id')) : [],
            'departments' => Geo::departments(),
            'municipalities' => $entry && $entry['department_id'] ? Geo::municipalities((int) $entry['department_id']) : [],
            'cover' => $entry && $entry['cover_media_id'] ? Media::find((int) $entry['cover_media_id']) : null,
            'gallery' => $gallery,
        ]);
    }

    public function store(string $section): void
    {
        $key = $this->sectionKey($section);
        [$data, $cats, $media] = $this->validated($key, null, url('/admin/contenido/' . $section . '/nuevo'));
        $id = Entry::save(null, $data, $cats, $media, (int) $this->user['id']);
        Audit::log($data['status'] === 'published' ? 'publish' : 'create', 'entry', $id, Sections::get($key)['label'] . ': ' . $data['title']);
        flash('success', $data['status'] === 'published' ? 'Publicación creada y publicada.' : 'Publicación guardada como ' . mb_strtolower(status_label($data['status'])) . '.');
        redirect(url('/admin/publicaciones/' . $id . '/editar'));
    }

    public function update(string $id): void
    {
        $entry = $this->findOr404((int) $id);
        [$data, $cats, $media] = $this->validated($entry['section'], $entry, url('/admin/publicaciones/' . $id . '/editar'));
        Entry::save((int) $id, $data, $cats, $media, (int) $this->user['id']);
        $action = $data['status'] !== $entry['status'] ? ($data['status'] === 'published' ? 'publish' : ($data['status'] === 'archived' ? 'archive' : 'unpublish')) : 'update';
        Audit::log($action, 'entry', (int) $id, Sections::get($entry['section'])['label'] . ': ' . $data['title']);
        flash('success', 'Cambios guardados.');
        redirect(url('/admin/publicaciones/' . $id . (Request::bool('_preview') ? '/vista-previa' : '/editar')));
    }

    /** Cambia el estado desde el listado: publicar, despublicar (borrador) o archivar. */
    public function status(string $id): void
    {
        $entry = $this->findOr404((int) $id);
        $status = Request::str('status');
        if (!in_array($status, Entry::STATUSES, true)) {
            throw new HttpException(422, 'Estado no válido.');
        }
        $this->require('content.publish');
        if ($status === 'published' && trim((string) $entry['summary']) === '') {
            flash('error', 'Antes de publicar agrega un resumen a «' . $entry['title'] . '».');
            back('/admin');
        }
        Entry::setStatus((int) $id, $status, (int) $this->user['id']);
        $action = ['published' => 'publish', 'draft' => 'unpublish', 'archived' => 'archive'][$status];
        Audit::log($action, 'entry', (int) $id, Sections::get($entry['section'])['label'] . ': ' . $entry['title']);
        flash('success', '«' . $entry['title'] . '» ahora está ' . mb_strtolower(status_label($status)) . '.');
        back('/admin');
    }

    public function destroy(string $id): void
    {
        $this->require('content.delete');
        $entry = $this->findOr404((int) $id);
        if (Request::str('confirm') !== 'ELIMINAR') {
            flash('error', 'Para eliminar escribe ELIMINAR en la confirmación.');
            back('/admin');
        }
        Entry::delete((int) $id);
        Audit::log('delete', 'entry', (int) $id, Sections::get($entry['section'])['label'] . ': ' . $entry['title']);
        flash('success', 'Publicación eliminada. Los archivos multimedia se conservan en la biblioteca.');
        redirect(url('/admin/contenido/' . Sections::get($entry['section'])['path']));
    }

    public function preview(string $id): void
    {
        $entry = $this->findOr404((int) $id);
        header('X-Robots-Tag: noindex, nofollow');
        (new PublicController())->renderEntry($entry, true);
    }

    /**
     * Valida y normaliza el formulario. Devuelve [datos, categorías, multimedia].
     * Ante errores vuelve al formulario con mensajes por campo.
     */
    private function validated(string $key, ?array $entry, string $back): array
    {
        $errors = [];
        $title = Request::str('title', '', 400);
        $slugIn = Request::str('slug', '', 250);
        $summary = Request::str('summary', '', 1000);
        $body = Request::str('body', '', 100000);
        $status = Request::str('status', 'draft');
        if (!in_array($status, Entry::STATUSES, true)) {
            $status = 'draft';
        }
        if ($status === 'published' && !\App\Core\Auth::can('content.publish')) {
            $status = 'draft';
        }

        if (mb_strlen($title) < 3) {
            $errors['title'] = 'El título debe tener al menos 3 caracteres.';
        } elseif (mb_strlen($title) > 190) {
            $errors['title'] = 'El título no puede superar 190 caracteres.';
        }
        if (mb_strlen($summary) > 400) {
            $errors['summary'] = 'El resumen no puede superar 400 caracteres (lleva ' . mb_strlen($summary) . ').';
        }
        if ($status === 'published' && $summary === '') {
            $errors['summary'] = 'Para publicar escribe un resumen breve.';
        }
        if ($slugIn !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugIn)) {
            $errors['slug'] = 'Usa solo minúsculas, números y guiones (por ejemplo: valle-de-cocora).';
        }
        $slug = $slugIn !== '' ? $slugIn : Entry::uniqueSlug($key, $title ?: 'publicacion', $entry['id'] ?? null);
        if ($slugIn !== '' && DB::value('SELECT id FROM entries WHERE section = ? AND slug = ? AND id <> ?', [$key, $slugIn, $entry['id'] ?? 0])) {
            $errors['slug'] = 'Ya existe otra publicación con esta dirección.';
        }

        $publishedAt = $this->datetime('published_at', $errors, 'Fecha de publicación no válida.');
        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = $entry['published_at'] ?? now();
        }

        $depId = Request::int('department_id');
        $munId = Request::int('municipality_id');
        if ($depId && !Geo::department($depId)) {
            $errors['department_id'] = 'Departamento no válido.';
        }
        if ($munId && (!$depId || !Geo::municipalityBelongs($munId, $depId))) {
            $errors['municipality_id'] = 'El municipio no pertenece al departamento seleccionado.';
        }

        $lat = Request::float('lat');
        $lng = Request::float('lng');
        if (($lat === null) !== ($lng === null)) {
            $errors['lat'] = 'Escribe latitud y longitud, o deja ambas vacías.';
        } elseif ($lat !== null && ($lat < -5 || $lat > 14 || $lng < -83 || $lng > -66)) {
            $errors['lat'] = 'Las coordenadas están fuera de Colombia (latitud entre -5 y 14, longitud entre -83 y -66).';
        }

        $coverId = Request::int('cover_media_id');
        if ($coverId) {
            $cover = Media::find($coverId);
            if (!$cover || $cover['kind'] !== 'image') {
                $errors['cover_media_id'] = 'La imagen principal debe ser una imagen de la biblioteca.';
            }
        }
        $mediaIds = Request::ints('media_ids');
        if ($mediaIds && count(Media::findMany($mediaIds)) !== count($mediaIds)) {
            $errors['media_ids'] = 'Algún archivo de la galería ya no existe.';
        }
        $cats = Category::validIds($key, Request::ints('categories'));

        $eventStart = $this->datetime('event_start', $errors, 'Fecha de inicio no válida.');
        $eventEnd = $this->datetime('event_end', $errors, 'Fecha de fin no válida.');
        if ($eventEnd && !$eventStart) {
            $errors['event_start'] = 'Indica también la fecha de inicio.';
        } elseif ($eventStart && $eventEnd && $eventEnd < $eventStart) {
            $errors['event_end'] = 'La fecha de fin no puede ser anterior al inicio.';
        }

        $meta = [];
        foreach (Sections::fields($key) as $f) {
            $name = 'meta_' . $f['key'];
            $raw = Request::str($name, '', 20000);
            if ($raw === '') {
                continue;
            }
            switch ($f['type']) {
                case 'lines':
                    $items = array_values(array_filter(array_map(static fn ($l) => mb_substr(trim($l), 0, 200), preg_split('/\R/', $raw) ?: [])));
                    if (count($items) > 40) {
                        $errors[$name] = 'Máximo 40 elementos.';
                    }
                    $meta[$f['key']] = array_slice($items, 0, 40);
                    break;
                case 'url':
                    if (!filter_var($raw, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $raw)) {
                        $errors[$name] = 'Escribe una dirección web completa que empiece por https://';
                    }
                    $meta[$f['key']] = mb_substr($raw, 0, 500);
                    break;
                case 'email':
                    if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                        $errors[$name] = 'Correo no válido.';
                    }
                    $meta[$f['key']] = mb_substr($raw, 0, 190);
                    break;
                case 'tel':
                    if (!preg_match('/^[\d\s()+\-.]{7,25}$/', $raw)) {
                        $errors[$name] = 'Teléfono no válido.';
                    }
                    $meta[$f['key']] = $raw;
                    break;
                case 'select':
                    if (!in_array($raw, $f['options'], true)) {
                        $errors[$name] = 'Opción no válida.';
                    }
                    $meta[$f['key']] = $raw;
                    break;
                case 'textarea':
                    $meta[$f['key']] = mb_substr($raw, 0, 10000);
                    break;
                default:
                    $meta[$f['key']] = mb_substr($raw, 0, 255);
            }
        }

        if ($errors) {
            $this->fail($errors, $back);
        }

        $data = [
            'section' => $key,
            'title' => $title,
            'slug' => $slug,
            'summary' => $summary ?: null,
            'body' => $body ?: null,
            'status' => $status,
            'published_at' => $publishedAt,
            'department_id' => $depId ?: null,
            'municipality_id' => $munId ?: null,
            'lat' => $lat,
            'lng' => $lng,
            'show_on_map' => Request::bool('show_on_map') ? 1 : 0,
            'cover_media_id' => $coverId ?: null,
            'is_featured' => Request::bool('is_featured') ? 1 : 0,
            'featured_order' => max(0, min(999, Request::int('featured_order', 0) ?? 0)),
            'is_hidden_gem' => Request::bool('is_hidden_gem') ? 1 : 0,
            'event_start' => $eventStart,
            'event_end' => $eventEnd,
            'author_name' => Request::str('author_name', '', 120) ?: null,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ];
        if ($entry && (int) $entry['is_demo'] === 1) {
            // Al editar una ficha de demostración sigue marcada, salvo que se indique lo contrario.
            $data['is_demo'] = Request::bool('keep_demo') ? 1 : 0;
        }
        return [$data, $cats, $mediaIds];
    }

    /** Convierte un campo datetime-local (YYYY-MM-DDTHH:MM) al formato de la base de datos. */
    private function datetime(string $field, array &$errors, string $message): ?string
    {
        $raw = Request::str($field, '', 30);
        if ($raw === '') {
            return null;
        }
        $dt = \DateTime::createFromFormat('Y-m-d\TH:i', $raw) ?: \DateTime::createFromFormat('Y-m-d H:i:s', $raw) ?: \DateTime::createFromFormat('Y-m-d', $raw);
        if (!$dt) {
            $errors[$field] = $message;
            return null;
        }
        if (\DateTime::createFromFormat('Y-m-d', $raw)) {
            $dt->setTime(0, 0);
        }
        return $dt->format('Y-m-d H:i:s');
    }
}
