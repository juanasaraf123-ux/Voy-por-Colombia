<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collection;
use App\Models\Department;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Municipality;
use App\Services\MediaProcessor;
use App\Support\Sections;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Centro multimedia: carga, metadatos, reutilización y eliminación de archivos. */
class MediaController extends Controller
{
    private function query(Request $request)
    {
        $q = Media::query()->search($request->query('q'))->orderByDesc('created_at')->orderByDesc('id');
        $kinds = match ($request->query('tipo')) {
            'image' => ['image'], 'video' => ['video', 'embed'], 'audio' => ['audio'], 'document' => ['document'], default => null,
        };
        if ($kinds) {
            $q->whereIn('kind', $kinds);
        }
        if ($request->query('galeria') === '1') {
            $q->where('in_gallery', true);
        }
        if ($d = $request->integer('departamento')) {
            $q->where('department_id', $d);
        }
        return $q;
    }

    public function index(Request $request)
    {
        $page = $this->query($request)->with(['department', 'municipality', 'collection'])->paginate(36, ['*'], 'pagina')->withQueryString();
        return Inertia::render('Admin/Media/Index', [
            'title' => 'Centro multimedia',
            'items' => $page->getCollection()->map->toPublic(),
            'pagination' => ['page' => $page->currentPage(), 'pages' => $page->lastPage(), 'total' => $page->total()],
            'filters' => ['q' => $request->query('q', ''), 'tipo' => $request->query('tipo', ''), 'galeria' => $request->query('galeria', ''), 'departamento' => $request->integer('departamento') ?: ''],
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'limits' => array_map(fn ($b) => MediaProcessor::humanBytes($b), MediaProcessor::limits()),
            'serverLimit' => ini_get('upload_max_filesize'),
        ]);
    }

    /** Carga uno o varios archivos. Devuelve JSON para el selector y redirige desde el centro multimedia. */
    public function upload(Request $request)
    {
        $request->validate(['files' => 'required|array|max:20', 'files.*' => 'file'], [
            'files.required' => 'No se recibió ningún archivo. Si es muy grande, revisa upload_max_filesize y post_max_size del servidor.',
            'files.max' => 'Puedes subir máximo 20 archivos a la vez.',
        ]);
        $ok = [];
        $errors = [];
        foreach ($request->file('files') as $file) {
            try {
                $data = MediaProcessor::handleUpload($file);
                $title = mb_substr(trim((string) preg_replace('/[_-]+/', ' ', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))), 0, 190);
                $m = Media::create($data + [
                    'title' => $title ?: null,
                    'in_gallery' => $request->boolean('in_gallery') && $data['kind'] === 'image',
                    'credit' => mb_substr((string) $request->input('credit', ''), 0, 190) ?: null,
                    'uploaded_by' => $request->user()->id,
                ]);
                AuditLog::record('upload', 'media', $m->id, 'Archivo: ' . $file->getClientOriginalName());
                $ok[] = $m;
            } catch (\InvalidArgumentException $e) {
                $errors[] = '«' . mb_substr($file->getClientOriginalName(), 0, 80) . '»: ' . $e->getMessage();
            }
        }
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json(['items' => collect($ok)->map->toPublic(), 'errors' => $errors], $ok ? 200 : 422);
        }
        $redirect = count($ok) === 1 ? redirect('/admin/multimedia/' . $ok[0]->id) : redirect('/admin/multimedia');
        if ($errors) {
            $redirect->with('error', implode(' ', $errors));
        }
        return $ok ? $redirect->with('success', count($ok) === 1 ? 'Archivo cargado. Completa su descripción y créditos.' : count($ok) . ' archivos cargados.') : $redirect;
    }

    /** Video de YouTube o Vimeo por enlace (sin descargarlo). */
    public function embed(Request $request)
    {
        $data = $request->validate(['url' => 'required|string|max:500', 'title' => 'nullable|string|max:190']);
        $parsed = MediaProcessor::parseEmbed($data['url']) ?? abort(back()->with('error', 'El enlace no es de YouTube ni de Vimeo. Copia la dirección completa del video.'));
        $m = Media::create([
            'kind' => 'embed', 'embed_provider' => $parsed[0], 'embed_id' => $parsed[1], 'source_url' => $data['url'],
            'title' => $data['title'] ?? 'Video de ' . ucfirst($parsed[0]), 'in_gallery' => $request->boolean('in_gallery'), 'uploaded_by' => $request->user()->id,
        ]);
        AuditLog::record('create', 'media', $m->id, 'Video en línea: ' . $data['url']);
        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return ['item' => $m->toPublic()];
        }
        return redirect('/admin/multimedia/' . $m->id)->with('success', 'Video agregado a la biblioteca.');
    }

    public function edit(Media $media)
    {
        $media->load(['department', 'municipality', 'collection']);
        return Inertia::render('Admin/Media/Edit', [
            'title' => 'Editar archivo',
            'media' => $media->toPublic() + $media->only(['alt_text', 'caption', 'credit', 'license', 'source_url', 'department_id', 'municipality_id', 'collection_id', 'gallery_section', 'in_gallery', 'gallery_featured', 'is_demo', 'original_name', 'size_bytes'])
                + ['size' => MediaProcessor::humanBytes((int) $media->size_bytes)],
            'usage' => $media->usage(),
            'usedIn' => Entry::where('cover_media_id', $media->id)->orWhereHas('media', fn ($q) => $q->where('media.id', $media->id))->limit(20)->get(['id', 'title', 'section']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'municipalities' => $media->department_id ? Municipality::where('department_id', $media->department_id)->orderBy('name')->get(['id', 'name']) : [],
            'collections' => Collection::orderBy('sort_order')->get(['id', 'name']),
            'sections' => collect(Sections::ALL)->except('news')->map(fn ($s, $k) => ['key' => $k, 'label' => $s['label']])->values(),
        ]);
    }

    public function update(Request $request, Media $media)
    {
        $v = $request->validate([
            'title' => 'nullable|string|max:190', 'alt_text' => 'nullable|string|max:255', 'caption' => 'nullable|string|max:500',
            'credit' => 'nullable|string|max:190', 'license' => 'nullable|string|max:120', 'source_url' => ['nullable', 'url:http,https', 'max:500'],
            'department_id' => 'nullable|integer|exists:departments,id', 'municipality_id' => 'nullable|integer|exists:municipalities,id',
            'collection_id' => 'nullable|integer|exists:collections,id', 'gallery_section' => 'nullable|in:' . implode(',', array_keys(Sections::ALL)),
        ]);
        if (!empty($v['municipality_id']) && !Municipality::whereKey($v['municipality_id'])->where('department_id', $v['department_id'] ?? 0)->exists()) {
            return back()->withErrors(['municipality_id' => 'El municipio no pertenece al departamento.']);
        }
        if ($media->kind === 'image' && $request->boolean('in_gallery') && trim((string) ($v['alt_text'] ?? '')) === '') {
            return back()->withErrors(['alt_text' => 'Las imágenes de la galería necesitan un texto alternativo (accesibilidad).']);
        }
        $media->update($v + [
            'in_gallery' => $request->boolean('in_gallery'),
            'gallery_featured' => $request->boolean('gallery_featured'),
            'is_demo' => $media->is_demo && $request->boolean('keep_demo'),
        ]);
        AuditLog::record('update', 'media', $media->id, 'Archivo: ' . ($media->original_name ?? $media->title));
        return back()->with('success', 'Datos del archivo guardados.');
    }

    public function destroy(Media $media)
    {
        $media->delete();
        AuditLog::record('delete', 'media', $media->id, 'Archivo: ' . ($media->original_name ?? $media->title));
        return redirect('/admin/multimedia')->with('success', 'Archivo eliminado de la biblioteca y del almacenamiento.');
    }

    /** Lista JSON para el selector multimedia de los formularios. */
    public function apiList(Request $request)
    {
        $page = $this->query($request)->paginate(30, ['*'], 'pagina');
        return ['items' => $page->getCollection()->map->toPublic(), 'total' => $page->total(), 'page' => $page->currentPage(), 'pages' => $page->lastPage()];
    }
}
