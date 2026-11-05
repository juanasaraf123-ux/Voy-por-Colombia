<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Entry;
use App\Models\Media;
use App\Models\SectionInfo;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/** Página de inicio: textos, imagen de fondo, video, lugar protagonista, secciones y destacados. */
class HomeController extends Controller
{
    private const TEXTS = [
        'hero_kicker' => [80, 'Frase manuscrita superior'],
        'hero_title' => [120, 'Título principal'],
        'hero_subtitle' => [300, 'Texto de la portada'],
        'hero_note' => [80, 'Frase manuscrita del panel'],
        'map_hint' => [80, 'Indicación sobre el mapa'],
    ];

    public function edit()
    {
        $spot = (int) Setting::value('hero_spotlight_id');
        $video = (int) Setting::value('hero_video_id');
        return Inertia::render('Admin/Home', [
            'title' => 'Página de inicio',
            'texts' => collect(self::TEXTS)->map(fn ($t, $k) => ['key' => $k, 'max' => $t[0], 'label' => $t[1], 'value' => Setting::value($k, '')])->values(),
            'heroMedia' => Media::whereIn('id', array_map('intval', Setting::json('hero_media_ids')))->get()->map->toPublic(),
            'heroVideo' => $video ? Media::find($video)?->toPublic() : null,
            'spotlight' => $spot ? (($e = Entry::with(['cover', 'department', 'municipality'])->find($spot)) ? RouteController::pick($e) : null) : null,
            'sections' => SectionInfo::with('cover')->orderBy('sort_order')->get()->map(fn ($s) => $s->only(['id', 'section_key', 'title', 'tagline', 'description', 'sort_order', 'show_on_home', 'cover_media_id']) + ['cover' => $s->cover?->toPublic()]),
            'featured' => Entry::with(['cover', 'department', 'municipality'])->where('is_featured', true)->orderBy('featured_order')->limit(60)->get()
                ->map(fn ($e) => RouteController::pick($e) + ['order' => $e->featured_order]),
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (self::TEXTS as $k => [$max]) {
            $rules[$k] = ($k === 'hero_title' ? 'required' : 'nullable') . '|string|max:' . $max;
        }
        $v = $request->validate($rules + [
            'hero_media_ids' => 'array|max:8', 'hero_media_ids.*' => 'integer|exists:media,id',
            'hero_video_id' => 'nullable|integer|exists:media,id', 'hero_spotlight_id' => 'nullable|integer|exists:entries,id',
            'sections' => 'array', 'sections.*.id' => 'required|integer|exists:sections,id', 'sections.*.title' => 'required|string|max:80',
            'sections.*.tagline' => 'nullable|string|max:160', 'sections.*.description' => 'nullable|string|max:500',
            'sections.*.cover_media_id' => 'nullable|integer|exists:media,id', 'sections.*.sort_order' => 'nullable|integer|min:0|max:99',
            'featured' => 'array', 'featured.*.id' => 'required|integer|exists:entries,id', 'featured.*.order' => 'nullable|integer|min:0|max:999',
        ], ['hero_media_ids.max' => 'Usa máximo 8 imágenes.']);
        if (Media::whereIn('id', $v['hero_media_ids'] ?? [])->where('kind', '<>', 'image')->exists()) {
            return back()->withErrors(['hero_media_ids' => 'La portada solo admite imágenes de la biblioteca.']);
        }
        if (!empty($v['hero_video_id']) && !in_array(Media::find($v['hero_video_id'])->kind, ['video', 'embed'], true)) {
            return back()->withErrors(['hero_video_id' => 'Elige un video de la biblioteca (archivo MP4/WebM o enlace de YouTube/Vimeo).']);
        }
        DB::transaction(function () use ($v, $request) {
            foreach (array_keys(self::TEXTS) as $k) {
                Setting::put($k, (string) ($v[$k] ?? ''));
            }
            Setting::put('hero_media_ids', json_encode(array_values($v['hero_media_ids'] ?? [])));
            Setting::put('hero_video_id', (string) ($v['hero_video_id'] ?? ''));
            Setting::put('hero_spotlight_id', (string) ($v['hero_spotlight_id'] ?? ''));
            foreach ($v['sections'] ?? [] as $i => $s) {
                $cover = !empty($s['cover_media_id']) && Media::find($s['cover_media_id'])?->kind === 'image' ? $s['cover_media_id'] : null;
                SectionInfo::whereKey($s['id'])->update([
                    'title' => $s['title'], 'tagline' => $s['tagline'] ?? null, 'description' => $s['description'] ?? null,
                    'cover_media_id' => $cover, 'sort_order' => (int) ($s['sort_order'] ?? 0),
                    'show_on_home' => (bool) $request->input("sections.$i.show_on_home"),
                ]);
            }
            $keep = collect($v['featured'] ?? [])->pluck('id')->all();
            Entry::where('is_featured', true)->whereNotIn('id', $keep)->update(['is_featured' => false]);
            foreach ($v['featured'] ?? [] as $f) {
                Entry::whereKey($f['id'])->update(['is_featured' => true, 'featured_order' => (int) ($f['order'] ?? 0)]);
            }
        });
        AuditLog::record('settings', 'home', null, 'Página de inicio actualizada');
        return back()->with('success', 'Página de inicio actualizada. Los cambios ya están visibles en el sitio.');
    }
}
