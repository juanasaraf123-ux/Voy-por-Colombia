<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Rutas turísticas curadas desde el panel (varias paradas en orden). */
class TravelRoute extends Model
{
    protected $table = 'routes';
    protected $guarded = [];
    protected $casts = ['is_featured' => 'boolean', 'is_demo' => 'boolean'];

    public function cover()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function stops()
    {
        return $this->belongsToMany(Entry::class, 'route_stops', 'route_id', 'entry_id')
            ->withPivot(['sort_order', 'note'])->orderBy('route_stops.sort_order');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = mb_substr(\Illuminate\Support\Str::slug($title) ?: 'ruta', 0, 180);
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->where('id', '<>', $ignoreId ?? 0)->exists()) {
            $slug = "$base-$i";
            $i++;
        }
        return $slug;
    }

    /** Ruta con sus paradas visibles, lista para el mapa. */
    public function toPublic(bool $withStops = true): array
    {
        $data = [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'duration' => $this->duration,
            'url' => '/rutas/' . $this->slug,
            'image' => $this->cover?->url('medium'),
            'featured' => (bool) $this->is_featured,
            'stopsCount' => $this->stops_count ?? null,
        ];
        if ($withStops) {
            $stops = $this->stops()->visible()->withCard()->get();
            $cards = Entry::cards($stops);
            foreach ($cards as $i => &$c) {
                $c['note'] = $stops[$i]->pivot->note;
            }
            $data['stops'] = $cards;
            $data['stopsCount'] = count($cards);
        }
        return $data;
    }
}
