<?php

namespace App\Models;

use App\Support\Sections;
use App\Support\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Publicaciones de todas las secciones (destinos, platos, fincas, emprendimientos, expresiones culturales y noticias).
 * Comparten estructura y se diferencian por «section» y «meta».
 */
class Entry extends Model
{
    protected $guarded = [];

    public const STATUSES = ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'];

    protected $casts = [
        'meta' => 'array',
        'published_at' => 'datetime',
        'event_start' => 'datetime',
        'event_end' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
        'show_on_map' => 'boolean',
        'is_featured' => 'boolean',
        'is_hidden_gem' => 'boolean',
        'is_demo' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function cover()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'entry_categories')->orderBy('sort_order')->orderBy('name');
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'entry_media')->withPivot('sort_order')->orderBy('entry_media.sort_order')->orderBy('media.id');
    }

    /** Visibles al público: publicadas y con fecha de publicación cumplida. */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('entries.status', 'published')
            ->where(fn ($w) => $w->whereNull('entries.published_at')->orWhere('entries.published_at', '<=', now()));
    }

    public function scopeWithCard(Builder $q): Builder
    {
        return $q->with(['cover', 'department.region', 'municipality', 'categories']);
    }

    /** Búsqueda sin tildes sobre el texto indexado (título, resumen, cuerpo, lugar, categorías y campos). */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $q;
        }
        $normalized = Text::normalize($term);
        if ($normalized === '') {
            return $q->whereRaw('1 = 0');
        }
        foreach (array_slice(explode(' ', $normalized), 0, 6) as $word) {
            $q->whereRaw("entries.search_text LIKE ? ESCAPE '!'", ['%' . Text::likeEscape($word) . '%']);
        }
        return $q;
    }

    public function scopeInCategory(Builder $q, ?string $slug): Builder
    {
        return $slug ? $q->whereHas('categories', fn ($c) => $c->where('slug', $slug)) : $q;
    }

    public function scopeOnMap(Builder $q): Builder
    {
        return $q->whereNotNull('entries.lat')->whereNotNull('entries.lng')->where('entries.show_on_map', true);
    }

    public function scopeFeaturedFirst(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')->orderBy('featured_order')->orderByDesc('published_at');
    }

    public function scopeRecent(Builder $q): Builder
    {
        return $q->orderByRaw('COALESCE(entries.published_at, entries.created_at) DESC')->orderByDesc('entries.id');
    }

    public function effectiveStatus(): string
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->isFuture() ? 'scheduled' : $this->status;
    }

    public function path(): string
    {
        return Sections::exists($this->section) ? Sections::path($this->section, $this->slug) : '#';
    }

    public function metaValue(string $key, mixed $default = null): mixed
    {
        return ($this->meta ?? [])[$key] ?? $default;
    }

    /** Cantidad de imágenes, videos y audios de cada publicación: [id => [tipo => n]]. */
    public static function mediaKinds(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $out = [];
        $rows = DB::table('entry_media')->join('media', 'media.id', '=', 'entry_media.media_id')
            ->whereIn('entry_media.entry_id', $ids)
            ->groupBy('entry_media.entry_id', 'media.kind')
            ->selectRaw('entry_media.entry_id, media.kind, COUNT(*) AS n')->get();
        foreach ($rows as $r) {
            $kind = $r->kind === 'embed' ? 'video' : $r->kind;
            $out[$r->entry_id][$kind] = ($out[$r->entry_id][$kind] ?? 0) + (int) $r->n;
        }
        return $out;
    }

    /** Representación compacta para mapa, tarjetas, favoritos y rutas. */
    public function toCard(array $kinds = []): array
    {
        $cat = $this->categories->first();
        $def = Sections::ALL[$this->section] ?? ['label' => '', 'color' => '#0f9d77', 'icon' => 'compass'];
        $point = (string) $this->metaValue('point_type', '');
        return [
            'id' => $this->id,
            'section' => $this->section,
            'sectionLabel' => $def['label'],
            'color' => $def['color'],
            'title' => $this->title,
            'shortName' => trim((string) $this->metaValue('short_name', '')) ?: $this->title,
            'tagline' => trim((string) $this->metaValue('tagline', '')),
            'summary' => Text::limit($this->summary, 170),
            'url' => $this->path(),
            'lat' => $this->lat,
            'lng' => $this->lng,
            'pointType' => $point,
            'pointIcon' => Sections::POINT_TYPES[$point] ?? null,
            'icon' => $cat->icon ?? $def['icon'],
            'catName' => $cat->name ?? $def['label'],
            'catColor' => $cat->color ?? $def['color'],
            'categories' => $this->categories->map->toPublic()->values()->all(),
            'department' => $this->department?->name,
            'departmentSlug' => $this->department?->slug,
            'regionColor' => $this->department?->region?->color,
            'municipality' => $this->municipality?->name,
            'municipalitySlug' => $this->municipality?->slug,
            'featured' => (bool) $this->is_featured,
            'hiddenGem' => (bool) $this->is_hidden_gem,
            'thumb' => $this->cover?->url('thumb'),
            'image' => $this->cover?->url('medium'),
            'large' => $this->cover?->url('large'),
            'alt' => $this->cover?->alt_text ?: $this->title,
            'media' => ['image' => $kinds['image'] ?? 0, 'video' => $kinds['video'] ?? 0, 'audio' => $kinds['audio'] ?? 0],
            'activities' => array_slice((array) $this->metaValue('activities', []), 0, 5),
            'eventStart' => $this->event_start?->toIso8601String(),
            'eventEnd' => $this->event_end?->toIso8601String(),
            'publishedAt' => $this->published_at?->toIso8601String(),
            'isDemo' => (bool) $this->is_demo,
        ];
    }

    /** Tarjetas con conteo de multimedia para una colección de publicaciones. */
    public static function cards($entries): array
    {
        $kinds = self::mediaKinds($entries->pluck('id')->all());
        return $entries->map(fn (Entry $e) => $e->toCard($kinds[$e->id] ?? []))->values()->all();
    }

    public static function uniqueSlug(string $section, string $title, ?int $ignoreId = null): string
    {
        $base = mb_substr(\Illuminate\Support\Str::slug($title) ?: 'contenido', 0, 180);
        $slug = $base;
        $i = 2;
        while (static::where('section', $section)->where('slug', $slug)->where('id', '<>', $ignoreId ?? 0)->exists()) {
            $slug = "$base-$i";
            $i++;
        }
        return $slug;
    }

    /** Recalcula el texto del buscador a partir del contenido, el lugar, las categorías y los campos. */
    public function refreshSearchText(): void
    {
        $this->loadMissing(['department', 'municipality', 'categories']);
        $parts = [$this->title, $this->summary, Text::plain($this->body), $this->department?->name, $this->municipality?->name,
            $this->categories->pluck('name')->implode(' ')];
        foreach ((array) ($this->meta ?? []) as $v) {
            $parts[] = is_array($v) ? implode(' ', $v) : (string) $v;
        }
        $this->search_text = mb_substr(Text::normalize(implode(' ', array_filter($parts))), 0, 20000);
        $this->saveQuietly();
    }

    /** Lugares cercanos (km) entre los publicados con coordenadas. */
    public function nearby(int $limit = 6, float $radiusKm = 120)
    {
        if ($this->lat === null || $this->lng === null) {
            return $this->department_id
                ? static::visible()->withCard()->where('department_id', $this->department_id)->where('id', '<>', $this->id)->featuredFirst()->limit($limit)->get()
                : collect();
        }
        $dLat = $radiusKm / 111;
        $dLng = $radiusKm / (111 * max(0.2, cos(deg2rad($this->lat))));
        return static::visible()->withCard()->where('id', '<>', $this->id)
            ->whereBetween('lat', [$this->lat - $dLat, $this->lat + $dLat])
            ->whereBetween('lng', [$this->lng - $dLng, $this->lng + $dLng])
            ->limit(200)->get()
            ->each(fn ($e) => $e->distance_km = self::haversine($this->lat, $this->lng, $e->lat, $e->lng))
            ->sortBy('distance_km')->take($limit)->values();
    }

    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round(6371 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
