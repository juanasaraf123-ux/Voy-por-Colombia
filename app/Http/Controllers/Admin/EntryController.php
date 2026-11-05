<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Municipality;
use App\Support\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/** Publicaciones de todas las secciones (Turismo, Gastronomía, Café, Emprendimiento, Cultura y Noticias). */
class EntryController extends Controller
{
    public function index(Request $request, string $section)
    {
        $key = Sections::byPath($section) ?? abort(404);
        $q = Entry::with(['cover', 'department', 'municipality', 'categories'])->where('section', $key)->search($request->query('q'));
        $estado = $request->query('estado');
        if ($estado === 'scheduled') {
            $q->where('status', 'published')->where('published_at', '>', now());
        } elseif (in_array($estado, array_keys(Entry::STATUSES), true)) {
            $q->where('status', $estado);
        }
        if ($c = $request->integer('categoria')) {
            $q->whereHas('categories', fn ($w) => $w->where('categories.id', $c));
        }
        if ($d = $request->integer('departamento')) {
            $q->where('department_id', $d);
        }
        $page = $q->orderByDesc('updated_at')->paginate(20, ['*'], 'pagina')->withQueryString();
        return Inertia::render('Admin/Entries/Index', [
            'title' => Sections::get($key)['label'],
            'section' => ['key' => $key] + Sections::get($key),
            'items' => $page->getCollection()->map(fn (Entry $e) => [
                'id' => $e->id, 'title' => $e->title, 'status' => $e->effectiveStatus(), 'thumb' => $e->cover?->url('thumb'),
                'place' => trim(($e->municipality?->name ? $e->municipality->name . ', ' : '') . ($e->department?->name ?? ''), ', '),
                'categories' => $e->categories->pluck('name'), 'updatedAt' => $e->updated_at?->toIso8601String(), 'url' => $e->path(),
                'featured' => $e->is_featured, 'isDemo' => $e->is_demo, 'hasCoords' => $e->lat !== null,
            ]),
            'pagination' => ['page' => $page->currentPage(), 'pages' => $page->lastPage(), 'total' => $page->total()],
            'counts' => Entry::where('section', $key)->groupBy('status')->selectRaw('status, COUNT(*) AS n')->pluck('n', 'status'),
            'filters' => ['q' => $request->query('q', ''), 'estado' => $estado ?? '', 'categoria' => $request->integer('categoria') ?: '', 'departamento' => $request->integer('departamento') ?: ''],
            'categories' => Category::forSection($key)->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(string $section)
    {
        $key = Sections::byPath($section) ?? abort(404);
        return $this->form($key, null);
    }

    public function edit(Entry $entry)
    {
        return $this->form($entry->section, $entry);
    }

    private function form(string $key, ?Entry $entry)
    {
        $entry?->load(['cover', 'media', 'categories']);
        $def = Sections::get($key);
        return Inertia::render('Admin/Entries/Form', [
            'title' => $entry ? 'Editar: ' . $entry->title : 'Nuevo ' . $def['singular'],
            'section' => ['key' => $key, 'hasEvents' => Sections::hasEvents($key)] + $def,
            'fields' => Sections::fields($key),
            'entry' => $entry ? [
                'id' => $entry->id, 'title' => $entry->title, 'slug' => $entry->slug, 'summary' => $entry->summary ?? '', 'body' => $entry->body ?? '',
                'status' => $entry->status, 'effectiveStatus' => $entry->effectiveStatus(),
                'published_at' => $entry->published_at?->format('Y-m-d\TH:i') ?? '',
                'department_id' => $entry->department_id ?? '', 'municipality_id' => $entry->municipality_id ?? '',
                'lat' => $entry->lat ?? '', 'lng' => $entry->lng ?? '', 'show_on_map' => $entry->show_on_map,
                'cover_media_id' => $entry->cover_media_id ?? '', 'is_featured' => $entry->is_featured, 'featured_order' => $entry->featured_order,
                'is_hidden_gem' => $entry->is_hidden_gem, 'event_start' => $entry->event_start?->format('Y-m-d\TH:i') ?? '',
                'event_end' => $entry->event_end?->format('Y-m-d\TH:i') ?? '', 'author_name' => $entry->author_name ?? '',
                'meta' => (object) ($entry->meta ?? []), 'categories' => $entry->categories->pluck('id'), 'media_ids' => $entry->media->pluck('id'),
                'is_demo' => $entry->is_demo, 'url' => $entry->path(),
            ] : null,
            'cover' => $entry?->cover?->toPublic(),
            'gallery' => $entry ? $entry->media->map->toPublic() : [],
            'categories' => Category::forSection($key)->get(['id', 'name', 'color']),
            'departments' => Department::orderBy('name')->get(['id', 'name', 'slug']),
            'municipalities' => $entry?->department_id ? Municipality::where('department_id', $entry->department_id)->orderBy('name')->get(['id', 'name', 'lat', 'lng']) : [],
            'canPublish' => Gate::allows('content.publish'),
            'canDelete' => Gate::allows('content.delete'),
        ]);
    }

    public function store(Request $request, string $section)
    {
        $key = Sections::byPath($section) ?? abort(404);
        [$data, $cats, $media] = $this->validated($request, $key, null);
        $entry = DB::transaction(function () use ($data, $cats, $media, $request) {
            $entry = Entry::create($data + ['created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
            $this->sync($entry, $cats, $media);
            return $entry;
        });
        AuditLog::record($data['status'] === 'published' ? 'publish' : 'create', 'entry', $entry->id, Sections::get($key)['label'] . ': ' . $entry->title);
        return redirect('/admin/publicaciones/' . $entry->id . '/editar')
            ->with('success', $data['status'] === 'published' ? 'Publicación creada y publicada.' : 'Publicación guardada como ' . mb_strtolower(Entry::STATUSES[$data['status']]) . '.');
    }

    public function update(Request $request, Entry $entry)
    {
        [$data, $cats, $media] = $this->validated($request, $entry->section, $entry);
        $old = $entry->status;
        DB::transaction(function () use ($entry, $data, $cats, $media, $request) {
            $entry->update($data + ['updated_by' => $request->user()->id]);
            $this->sync($entry, $cats, $media);
        });
        $action = $data['status'] !== $old ? ['published' => 'publish', 'archived' => 'archive', 'draft' => 'unpublish'][$data['status']] : 'update';
        AuditLog::record($action, 'entry', $entry->id, Sections::get($entry->section)['label'] . ': ' . $entry->title);
        return redirect('/admin/publicaciones/' . $entry->id . ($request->boolean('_preview') ? '/vista-previa' : '/editar'))->with('success', 'Cambios guardados.');
    }

    private function sync(Entry $entry, array $cats, array $media): void
    {
        $entry->categories()->sync($cats);
        $entry->media()->sync(collect($media)->values()->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->all());
        $entry->load(['categories', 'department', 'municipality']);
        $entry->refreshSearchText();
    }

    /** Publicar, despublicar (borrador) o archivar desde el listado. */
    public function status(Request $request, Entry $entry)
    {
        Gate::authorize('content.publish');
        $status = $request->validate(['status' => 'required|in:draft,published,archived'])['status'];
        if ($status === 'published' && trim((string) $entry->summary) === '') {
            return back()->with('error', 'Antes de publicar agrega un resumen a «' . $entry->title . '».');
        }
        $entry->status = $status;
        if ($status === 'published' && !$entry->published_at) {
            $entry->published_at = now();
        }
        $entry->updated_by = $request->user()->id;
        $entry->save();
        AuditLog::record(['published' => 'publish', 'draft' => 'unpublish', 'archived' => 'archive'][$status], 'entry', $entry->id, Sections::get($entry->section)['label'] . ': ' . $entry->title);
        return back()->with('success', '«' . $entry->title . '» ahora está ' . mb_strtolower(Entry::STATUSES[$status]) . '.');
    }

    public function destroy(Request $request, Entry $entry)
    {
        Gate::authorize('content.delete');
        if ($request->input('confirm') !== 'ELIMINAR') {
            return back()->with('error', 'Para eliminar escribe ELIMINAR en la confirmación.');
        }
        $entry->delete();
        AuditLog::record('delete', 'entry', $entry->id, Sections::get($entry->section)['label'] . ': ' . $entry->title);
        return redirect('/admin/contenido/' . Sections::get($entry->section)['path'])->with('success', 'Publicación eliminada. Los archivos multimedia se conservan en la biblioteca.');
    }

    public function preview(Entry $entry)
    {
        $entry->load(['cover', 'department.region', 'municipality', 'categories']);
        return app(PublicController::class)->renderEntry($entry, true);
    }

    /** Valida y normaliza el formulario: [datos, categorías, multimedia]. */
    private function validated(Request $request, string $key, ?Entry $entry): array
    {
        $rules = [
            'title' => 'required|string|min:3|max:190',
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'summary' => 'nullable|string|max:400',
            'body' => 'nullable|string|max:100000',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'department_id' => 'nullable|integer|exists:departments,id',
            'municipality_id' => 'nullable|integer|exists:municipalities,id',
            'lat' => 'nullable|numeric|between:-5,14|required_with:lng',
            'lng' => 'nullable|numeric|between:-83,-66|required_with:lat',
            'cover_media_id' => 'nullable|integer|exists:media,id',
            'media_ids' => 'array|max:60',
            'media_ids.*' => 'integer|exists:media,id',
            'categories' => 'array',
            'categories.*' => 'integer',
            'featured_order' => 'nullable|integer|min:0|max:999',
            'event_start' => 'nullable|date',
            'event_end' => 'nullable|date|after_or_equal:event_start',
            'author_name' => 'nullable|string|max:120',
            'meta' => 'array',
        ];
        foreach (Sections::fields($key) as $f) {
            $rules['meta.' . $f['key']] = match ($f['type']) {
                'lines' => 'nullable|string|max:20000',
                'url' => ['nullable', 'url:http,https', 'max:500'],
                'email' => 'nullable|email|max:190',
                'tel' => ['nullable', 'regex:/^[\d\s()+\-.]{7,25}$/'],
                'select' => 'nullable|in:' . implode(',', array_map(fn ($o) => str_replace(',', '\,', $o), $f['options'])),
                'textarea' => 'nullable|string|max:10000',
                default => 'nullable|string|max:255',
            };
        }
        $v = $request->validate($rules, [
            'slug.regex' => 'Usa solo minúsculas, números y guiones (por ejemplo: valle-de-cocora).',
            'lat.between' => 'Las coordenadas están fuera de Colombia (latitud entre -5 y 14).',
            'lng.between' => 'Las coordenadas están fuera de Colombia (longitud entre -83 y -66).',
            'event_end.after_or_equal' => 'La fecha de fin no puede ser anterior al inicio.',
        ]);

        $status = $v['status'];
        if ($status === 'published' && !Gate::allows('content.publish')) {
            $status = 'draft';
        }
        $errors = [];
        if ($status === 'published' && trim((string) ($v['summary'] ?? '')) === '') {
            $errors['summary'] = 'Para publicar escribe un resumen breve.';
        }
        $slug = $v['slug'] ?? '';
        if ($slug !== '' && Entry::where('section', $key)->where('slug', $slug)->where('id', '<>', $entry->id ?? 0)->exists()) {
            $errors['slug'] = 'Ya existe otra publicación con esta dirección.';
        }
        if (!empty($v['municipality_id']) && !Municipality::whereKey($v['municipality_id'])->where('department_id', $v['department_id'] ?? 0)->exists()) {
            $errors['municipality_id'] = 'El municipio no pertenece al departamento seleccionado.';
        }
        if (!empty($v['cover_media_id']) && Media::find($v['cover_media_id'])?->kind !== 'image') {
            $errors['cover_media_id'] = 'La imagen principal debe ser una imagen de la biblioteca.';
        }
        if (!empty($v['event_end']) && empty($v['event_start'])) {
            $errors['event_start'] = 'Indica también la fecha de inicio.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $meta = [];
        foreach (Sections::fields($key) as $f) {
            $raw = trim((string) ($v['meta'][$f['key']] ?? ''));
            if ($raw === '') {
                continue;
            }
            $meta[$f['key']] = $f['type'] === 'lines'
                ? array_slice(array_values(array_filter(array_map(fn ($l) => mb_substr(trim($l), 0, 200), preg_split('/\R/', $raw) ?: []))), 0, 40)
                : $raw;
        }
        $publishedAt = $v['published_at'] ?? null;
        if ($status === 'published' && !$publishedAt) {
            $publishedAt = $entry?->published_at ?? now();
        }
        $data = [
            'section' => $key,
            'title' => $v['title'],
            'slug' => $slug !== '' ? $slug : Entry::uniqueSlug($key, $v['title'], $entry?->id),
            'summary' => $v['summary'] ?? null,
            'body' => $v['body'] ?? null,
            'status' => $status,
            'published_at' => $publishedAt,
            'department_id' => $v['department_id'] ?? null,
            'municipality_id' => $v['municipality_id'] ?? null,
            'lat' => isset($v['lat']) ? round((float) $v['lat'], 6) : null,
            'lng' => isset($v['lng']) ? round((float) $v['lng'], 6) : null,
            'show_on_map' => $request->boolean('show_on_map'),
            'cover_media_id' => $v['cover_media_id'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'featured_order' => (int) ($v['featured_order'] ?? 0),
            'is_hidden_gem' => $request->boolean('is_hidden_gem'),
            'event_start' => $v['event_start'] ?? null,
            'event_end' => $v['event_end'] ?? null,
            'author_name' => $v['author_name'] ?? null,
            'meta' => $meta,
        ];
        if ($entry && $entry->is_demo) {
            // Una ficha de demostración sigue marcada al editarla, salvo que se indique lo contrario.
            $data['is_demo'] = $request->boolean('keep_demo');
        }
        $cats = Category::where('section', $key)->whereIn('id', $v['categories'] ?? [])->pluck('id')->all();
        return [$data, $cats, array_values(array_unique($v['media_ids'] ?? []))];
    }
}
