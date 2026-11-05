<?php

namespace App\Models;

use App\Support\Sections;
use Illuminate\Database\Eloquent\Model;

/** Presentación editable de cada sección (título, lema, portada, orden). */
class SectionInfo extends Model
{
    public $timestamps = false;
    protected $table = 'sections';
    protected $guarded = [];
    protected $casts = ['show_on_home' => 'boolean'];

    public function cover()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function definition(): ?array
    {
        return $this->section_key === 'gallery' ? Sections::GALLERY : (Sections::ALL[$this->section_key] ?? null);
    }

    /** Datos para el frontend, con valores por defecto si la sección aún no tiene fila. */
    public static function present(string $key): array
    {
        $def = $key === 'gallery' ? Sections::GALLERY : Sections::get($key);
        $row = static::with('cover')->where('section_key', $key)->first();
        return [
            'key' => $key,
            'title' => $row->title ?? $def['label'],
            'tagline' => $row->tagline ?? '',
            'description' => $row->description ?? '',
            'cover' => $row?->cover?->toPublic(),
            'path' => '/' . $def['path'],
            'icon' => $def['icon'],
            'color' => $def['color'],
        ];
    }
}
