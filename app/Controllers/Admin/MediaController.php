<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\MediaProcessor;
use App\Core\Request;
use App\Models\Geo;
use App\Models\Media;
use App\Sections;

/** Centro multimedia: carga, vista previa, metadatos, reutilización y eliminación de archivos. */
final class MediaController extends AdminController
{
    private const PER_PAGE = 36;

    protected function permission(): ?string
    {
        return 'media.manage';
    }

    private function filters(): array
    {
        $tipo = Request::str('tipo');
        return [
            'kind' => match ($tipo) {
                'image' => ['image'], 'video' => ['video', 'embed'], 'audio' => ['audio'], 'document' => ['document'], default => null,
            },
            'q' => Request::str('q', '', 100),
            'gallery' => Request::str('galeria') === '1' ? 1 : null,
            'department_id' => Request::int('departamento'),
            'collection_id' => Request::int('coleccion'),
        ];
    }

    public function index(): void
    {
        $f = $this->filters();
        $page = max(1, Request::int('pagina', 1) ?? 1);
        $total = Media::count($f);
        $this->render('media/index', [
            'pageTitle' => 'Centro multimedia',
            'items' => Media::list($f + ['limit' => self::PER_PAGE, 'offset' => ($page - 1) * self::PER_PAGE]),
            'total' => $total,
            'page' => $page,
            'pages' => (int) max(1, ceil($total / self::PER_PAGE)),
            'tipo' => Request::str('tipo'),
            'filters' => $f,
            'departments' => Geo::departments(),
            'collections' => DB::all('SELECT * FROM collections ORDER BY sort_order, name'),
            'limits' => MediaProcessor::limits(),
        ]);
    }

    /** Carga uno o varios archivos desde el formulario. */
    public function upload(): void
    {
        [$ok, $errors] = $this->processUploads();
        foreach ($errors as $err) {
            flash('error', $err);
        }
        if ($ok) {
            flash('success', count($ok) === 1 ? 'Archivo cargado. Completa su descripción y créditos.' : count($ok) . ' archivos cargados.');
        }
        if (count($ok) === 1) {
            redirect(url('/admin/multimedia/' . $ok[0]));
        }
        redirect(url('/admin/multimedia'));
    }

    /** @return array{0: int[], 1: string[]} */
    private function processUploads(): array
    {
        $files = $_FILES['files'] ?? $_FILES['file'] ?? null;
        if (!$files || empty($files['name'])) {
            // Un POST mayor que post_max_size llega vacío.
            return [[], ['No se recibió ningún archivo. Si el archivo es muy grande, revisa los límites upload_max_filesize y post_max_size del servidor.']];
        }
        $list = [];
        if (is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                $list[] = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
            }
        } else {
            $list[] = $files;
        }
        if (count($list) > 20) {
            return [[], ['Puedes subir máximo 20 archivos a la vez.']];
        }
        $ok = [];
        $errors = [];
        $inGallery = Request::bool('in_gallery');
        foreach ($list as $file) {
            try {
                $data = MediaProcessor::handleUpload($file);
                $title = mb_substr(trim((string) preg_replace('/[_-]+/', ' ', pathinfo((string) $file['name'], PATHINFO_FILENAME))), 0, 190);
                $id = Media::create($data + [
                    'title' => $title ?: null,
                    'in_gallery' => $inGallery && $data['kind'] === 'image' ? 1 : 0,
                    'credit' => Request::str('credit', '', 190) ?: null,
                ], (int) $this->user['id']);
                Audit::log('upload', 'media', $id, 'Archivo: ' . $file['name']);
                $ok[] = $id;
            } catch (\InvalidArgumentException $e) {
                $errors[] = '«' . mb_substr((string) $file['name'], 0, 80) . '»: ' . $e->getMessage();
            }
        }
        return [$ok, $errors];
    }

    /** Agrega un video de YouTube o Vimeo por enlace (sin descargarlo). */
    public function embed(): void
    {
        $url = Request::str('url', '', 500);
        $parsed = MediaProcessor::parseEmbed($url);
        if (!$parsed) {
            flash('error', 'El enlace no es de YouTube ni de Vimeo. Copia la dirección completa del video.');
            redirect(url('/admin/multimedia'));
        }
        $id = Media::create([
            'kind' => 'embed',
            'embed_provider' => $parsed[0],
            'embed_id' => $parsed[1],
            'source_url' => $url,
            'title' => Request::str('title', '', 190) ?: 'Video de ' . ucfirst($parsed[0]),
            'in_gallery' => Request::bool('in_gallery') ? 1 : 0,
        ], (int) $this->user['id']);
        Audit::log('create', 'media', $id, 'Video en línea: ' . $url);
        if ($this->isAjax()) {
            json_response(['item' => Media::toJson(Media::find($id))]);
        }
        flash('success', 'Video agregado a la biblioteca.');
        redirect(url('/admin/multimedia/' . $id));
    }

    public function edit(string $id): void
    {
        $m = Media::find((int) $id) ?? throw new HttpException(404);
        $this->render('media/edit', [
            'pageTitle' => 'Editar archivo',
            'm' => $m,
            'usage' => Media::usage((int) $id),
            'usedIn' => DB::all('SELECT DISTINCT e.id, e.title, e.section FROM entries e LEFT JOIN entry_media em ON em.entry_id = e.id WHERE e.cover_media_id = ? OR em.media_id = ? LIMIT 20', [(int) $id, (int) $id]),
            'departments' => Geo::departments(),
            'municipalities' => $m['department_id'] ? Geo::municipalities((int) $m['department_id']) : [],
            'collections' => DB::all('SELECT * FROM collections ORDER BY sort_order, name'),
        ]);
    }

    public function update(string $id): void
    {
        $m = Media::find((int) $id) ?? throw new HttpException(404);
        $errors = [];
        $depId = Request::int('department_id');
        $munId = Request::int('municipality_id');
        if ($munId && (!$depId || !Geo::municipalityBelongs($munId, $depId))) {
            $errors['municipality_id'] = 'El municipio no pertenece al departamento.';
        }
        $source = Request::str('source_url', '', 500);
        if ($source !== '' && (!filter_var($source, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $source))) {
            $errors['source_url'] = 'Escribe una dirección completa que empiece por https://';
        }
        $collection = Request::int('collection_id');
        if ($collection && !DB::value('SELECT id FROM collections WHERE id = ?', [$collection])) {
            $errors['collection_id'] = 'Colección no válida.';
        }
        $gallerySection = Request::str('gallery_section');
        if ($gallerySection !== '' && !Sections::exists($gallerySection)) {
            $errors['gallery_section'] = 'Categoría no válida.';
        }
        if ($m['kind'] === 'image' && Request::str('alt_text') === '' && Request::bool('in_gallery')) {
            $errors['alt_text'] = 'Las imágenes de la galería necesitan un texto alternativo (accesibilidad).';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/multimedia/' . $id));
        }
        DB::update('media', [
            'title' => Request::str('title', '', 190) ?: null,
            'alt_text' => Request::str('alt_text', '', 255) ?: null,
            'caption' => Request::str('caption', '', 500) ?: null,
            'credit' => Request::str('credit', '', 190) ?: null,
            'license' => Request::str('license', '', 120) ?: null,
            'source_url' => $source ?: null,
            'department_id' => $depId ?: null,
            'municipality_id' => $munId ?: null,
            'collection_id' => $collection ?: null,
            'gallery_section' => $gallerySection ?: null,
            'in_gallery' => Request::bool('in_gallery') ? 1 : 0,
            'gallery_featured' => Request::bool('gallery_featured') ? 1 : 0,
            'is_demo' => Request::bool('keep_demo') && (int) $m['is_demo'] === 1 ? 1 : 0,
            'updated_at' => now(),
        ], 'id = :mid', ['mid' => (int) $id]);
        Audit::log('update', 'media', (int) $id, 'Archivo: ' . ($m['original_name'] ?? $m['title']));
        flash('success', 'Datos del archivo guardados.');
        redirect(url('/admin/multimedia/' . $id));
    }

    public function destroy(string $id): void
    {
        $m = Media::find((int) $id) ?? throw new HttpException(404);
        Media::delete((int) $id);
        Audit::log('delete', 'media', (int) $id, 'Archivo: ' . ($m['original_name'] ?? $m['title']));
        flash('success', 'Archivo eliminado de la biblioteca y del almacenamiento.');
        redirect(url('/admin/multimedia'));
    }

    /** Lista JSON para el selector multimedia de los formularios. */
    public function apiList(): void
    {
        $f = $this->filters();
        $page = max(1, Request::int('pagina', 1) ?? 1);
        $items = Media::list($f + ['limit' => 30, 'offset' => ($page - 1) * 30]);
        json_response(['items' => array_map([Media::class, 'toJson'], $items), 'total' => Media::count($f), 'page' => $page]);
    }

    /** Carga desde el selector multimedia (AJAX). */
    public function apiUpload(): void
    {
        [$ok, $errors] = $this->processUploads();
        json_response(['items' => array_map(static fn ($id) => Media::toJson(Media::find($id)), $ok), 'errors' => $errors], $ok ? 200 : 422);
    }
}
