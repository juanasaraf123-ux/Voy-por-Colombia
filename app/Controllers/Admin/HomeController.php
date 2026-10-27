<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\Request;
use App\Models\Entry;
use App\Models\Media;
use App\Models\SectionInfo;
use App\Models\Setting;

/** Gestión de la página de inicio: portada, mensajes, bloques de categorías y destacados. */
final class HomeController extends AdminController
{
    private const TEXTS = [
        'hero_kicker' => [80, 'Etiqueta superior'],
        'hero_title' => [120, 'Título principal'],
        'hero_subtitle' => [300, 'Texto de la portada'],
        'hero_cta' => [40, 'Botón principal'],
        'hero_note' => [80, 'Frase manuscrita junto a la tarjeta'],
        'home_featured_title' => [120, 'Título de destinos destacados'],
        'home_gems_title' => [120, 'Título de joyas poco conocidas'],
        'closing_title' => [120, 'Título del cierre'],
        'closing_text' => [250, 'Texto del cierre'],
    ];

    protected function permission(): ?string
    {
        return 'home.manage';
    }

    public function edit(): void
    {
        $heroIds = array_map('intval', Setting::json('hero_media_ids'));
        $this->render('home', [
            'pageTitle' => 'Página de inicio',
            'texts' => self::TEXTS,
            'heroMedia' => Media::findMany($heroIds),
            'heroVideo' => (int) Setting::get('hero_video_id') ? Media::find((int) Setting::get('hero_video_id')) : null,
            'spotlight' => (int) Setting::get('hero_spotlight_id') ? \App\Models\Entry::find((int) Setting::get('hero_spotlight_id')) : null,
            'sections' => DB::all('SELECT s.*, m.path, m.variants FROM sections s LEFT JOIN media m ON m.id = s.cover_media_id ORDER BY s.sort_order, s.id'),
            'featured' => Entry::query(['featured' => 1, 'order' => 'featured', 'limit' => 60], false),
        ]);
    }

    public function update(): void
    {
        $errors = [];
        $values = [];
        foreach (self::TEXTS as $key => [$max, $label]) {
            $v = Request::str($key, '', 2000);
            if (mb_strlen($v) > $max) {
                $errors[$key] = "Máximo $max caracteres.";
            }
            if ($v === '' && in_array($key, ['hero_title', 'hero_cta'], true)) {
                $errors[$key] = 'Este campo es obligatorio.';
            }
            $values[$key] = $v;
        }
        $heroIds = Request::ints('hero_media_ids');
        $heroMedia = Media::findMany($heroIds);
        if (count($heroMedia) !== count($heroIds) || array_filter($heroMedia, static fn ($m) => $m['kind'] !== 'image')) {
            $errors['hero_media_ids'] = 'La portada solo admite imágenes de la biblioteca.';
        }
        if (count($heroIds) > 8) {
            $errors['hero_media_ids'] = 'Usa máximo 8 imágenes en la portada.';
        }
        $video = Request::int('hero_video_id');
        if ($video && !in_array(Media::find($video)['kind'] ?? null, ['video', 'embed'], true)) {
            $errors['hero_video_id'] = 'Elige un video de la biblioteca (archivo MP4/WebM o enlace de YouTube/Vimeo).';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/inicio'));
        }
        DB::transaction(function () use ($values, $heroIds, $video) {
            foreach ($values as $k => $v) {
                Setting::set($k, $v);
            }
            Setting::set('hero_media_ids', json_encode(array_values($heroIds)));
            Setting::set('hero_video_id', $video ? (string) $video : '');
            Setting::set('hero_video_background', Request::bool('hero_video_background') ? '1' : '0');
            $spot = Request::int('hero_spotlight_id');
            if ($spot !== null) {
                Setting::set('hero_spotlight_id', $spot ? (string) $spot : '');
            }

            // Bloques de categorías
            foreach ((array) ($_POST['sections'] ?? []) as $id => $s) {
                if (!is_array($s) || !filter_var($id, FILTER_VALIDATE_INT)) {
                    continue;
                }
                $cover = filter_var($s['cover_media_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
                if ($cover && (Media::find($cover)['kind'] ?? null) !== 'image') {
                    $cover = null;
                }
                DB::update('sections', [
                    'title' => mb_substr(trim((string) ($s['title'] ?? '')), 0, 80) ?: 'Sección',
                    'tagline' => mb_substr(trim((string) ($s['tagline'] ?? '')), 0, 160) ?: null,
                    'description' => mb_substr(trim((string) ($s['description'] ?? '')), 0, 500) ?: null,
                    'cover_media_id' => $cover,
                    'sort_order' => max(0, min(99, (int) ($s['sort_order'] ?? 0))),
                    'show_on_home' => !empty($s['show_on_home']) ? 1 : 0,
                ], 'id = :sid', ['sid' => (int) $id]);
            }

            // Destacados: orden y retiro
            foreach ((array) ($_POST['featured'] ?? []) as $id => $f) {
                if (!is_array($f) || !filter_var($id, FILTER_VALIDATE_INT)) {
                    continue;
                }
                DB::update('entries', [
                    'featured_order' => max(0, min(999, (int) ($f['order'] ?? 0))),
                    'is_featured' => empty($f['remove']) ? 1 : 0,
                ], 'id = :eid', ['eid' => (int) $id]);
            }
            $add = Request::int('add_featured');
            if ($add) {
                DB::update('entries', ['is_featured' => 1], 'id = :eid', ['eid' => $add]);
            }
        });
        SectionInfo::forget();
        Audit::log('settings', 'home', null, 'Página de inicio actualizada');
        flash('success', 'Página de inicio actualizada. Los cambios ya están visibles en el sitio.');
        redirect(url('/admin/inicio'));
    }
}
