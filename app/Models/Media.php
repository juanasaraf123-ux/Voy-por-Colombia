<?php

namespace App\Models;

use App\Services\MediaProcessor;
use App\Support\Text;
use Illuminate\Database\Eloquent\Model;

/** Biblioteca multimedia reutilizable (imágenes, videos, audios, documentos y videos incrustados). */
class Media extends Model
{
    protected $table = 'media';
    protected $guarded = [];
    protected $casts = ['in_gallery' => 'boolean', 'gallery_featured' => 'boolean', 'is_demo' => 'boolean'];

    public const KINDS = ['image' => 'Imagen', 'video' => 'Video', 'audio' => 'Audio', 'document' => 'Documento', 'embed' => 'Video en línea'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function variantList(): array
    {
        $v = json_decode((string) $this->variants, true);
        return is_array($v) ? $v : [];
    }

    /** URL pública del archivo en el tamaño pedido (thumb, medium, large u original). */
    public function url(string $size = 'large'): string
    {
        if (!$this->path) {
            return asset('assets/img/placeholder.svg');
        }
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }
        $path = $this->variantList()[$size] ?? $this->path;
        return asset('uploads/' . ltrim($path, '/'));
    }

    public function thumbUrl(): string
    {
        return match ($this->kind) {
            'image' => $this->url('thumb'),
            'embed' => $this->embed_provider === 'youtube'
                ? 'https://i.ytimg.com/vi/' . rawurlencode((string) $this->embed_id) . '/hqdefault.jpg'
                : asset('assets/img/video-placeholder.svg'),
            'video' => asset('assets/img/video-placeholder.svg'),
            'audio' => asset('assets/img/audio-placeholder.svg'),
            default => asset('assets/img/document-placeholder.svg'),
        };
    }

    public function embedUrl(): ?string
    {
        return match ($this->embed_provider) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/' . rawurlencode((string) $this->embed_id) . '?rel=0',
            'vimeo' => 'https://player.vimeo.com/video/' . rawurlencode((string) $this->embed_id),
            default => null,
        };
    }

    public function srcset(): string
    {
        $parts = [];
        foreach (['thumb' => 480, 'medium' => 1024, 'large' => 1920] as $key => $w) {
            if (!empty($this->variantList()[$key])) {
                $parts[] = asset('uploads/' . $this->variantList()[$key]) . " {$w}w";
            }
        }
        return implode(', ', array_unique($parts));
    }

    /** Representación para el frontend (galería, visores y selector del panel). */
    public function toPublic(): array
    {
        $image = $this->kind === 'image';
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'title' => $this->title,
            'caption' => $this->caption,
            'credit' => $this->credit,
            'license' => $this->license,
            'alt' => $this->alt_text ?: (string) $this->title,
            'width' => $this->width,
            'height' => $this->height,
            'thumb' => $this->thumbUrl(),
            'medium' => $image ? $this->url('medium') : $this->thumbUrl(),
            'large' => $image ? $this->url('large') : $this->thumbUrl(),
            'srcset' => $image ? $this->srcset() : '',
            'src' => in_array($this->kind, ['video', 'audio', 'document'], true) ? $this->url('original') : null,
            'embed' => $this->kind === 'embed' ? $this->embedUrl() : null,
            'mime' => $this->mime,
            'department' => $this->department?->name,
            'municipality' => $this->municipality?->name,
            'collection' => $this->collection?->name,
            'isDemo' => (bool) $this->is_demo,
        ];
    }

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }
        $like = '%' . Text::likeEscape($term) . '%';
        return $q->where(function ($w) use ($like) {
            foreach (['title', 'caption', 'original_name', 'credit'] as $col) {
                $w->orWhereRaw("$col LIKE ? ESCAPE '!'", [$like]);
            }
        });
    }

    /** Número de publicaciones, rutas, secciones y departamentos que usan el archivo. */
    public function usage(): int
    {
        return \DB::table('entry_media')->where('media_id', $this->id)->count()
            + Entry::where('cover_media_id', $this->id)->count()
            + TravelRoute::where('cover_media_id', $this->id)->count()
            + SectionInfo::where('cover_media_id', $this->id)->count()
            + Department::where('cover_media_id', $this->id)->count();
    }

    protected static function booted(): void
    {
        static::deleting(function (Media $m) {
            Department::where('cover_media_id', $m->id)->update(['cover_media_id' => null]);
        });
        static::deleted(function (Media $m) {
            MediaProcessor::deleteFiles($m->getAttributes());
        });
    }
}
