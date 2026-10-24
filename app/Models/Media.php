<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;
use App\Core\MediaProcessor;

/** Biblioteca multimedia reutilizable (imágenes, videos, audios, documentos y videos incrustados). */
final class Media
{
    public const KINDS = ['image' => 'Imagen', 'video' => 'Video', 'audio' => 'Audio', 'document' => 'Documento', 'embed' => 'Video en línea'];

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM media WHERE id = ?', [$id]);
    }

    public static function findMany(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $rows = DB::all('SELECT * FROM media WHERE id IN (' . DB::placeholders($ids) . ')', array_values($ids));
        $byId = array_column($rows, null, 'id');
        return array_values(array_filter(array_map(static fn ($id) => $byId[$id] ?? null, $ids)));
    }

    public static function list(array $f = []): array
    {
        [$where, $params] = self::where($f);
        $limit = max(1, min(200, (int) ($f['limit'] ?? 48)));
        $offset = max(0, (int) ($f['offset'] ?? 0));
        return DB::all("SELECT m.*, d.name AS department_name, mu.name AS municipality_name, col.name AS collection_name
            FROM media m LEFT JOIN departments d ON d.id = m.department_id LEFT JOIN municipalities mu ON mu.id = m.municipality_id
            LEFT JOIN collections col ON col.id = m.collection_id
            WHERE $where ORDER BY " . (!empty($f['gallery']) ? 'm.gallery_featured DESC, m.created_at DESC, m.id DESC' : 'm.created_at DESC, m.id DESC') . " LIMIT $limit OFFSET $offset", $params);
    }

    public static function count(array $f = []): int
    {
        [$where, $params] = self::where($f);
        return (int) DB::value("SELECT COUNT(*) FROM media m WHERE $where", $params);
    }

    private static function where(array $f): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['kind'])) {
            $kinds = (array) $f['kind'];
            $w[] = 'm.kind IN (' . DB::placeholders($kinds) . ')';
            array_push($p, ...$kinds);
        }
        if (!empty($f['gallery'])) {
            $w[] = 'm.in_gallery = 1';
        }
        if (!empty($f['featured'])) {
            $w[] = 'm.gallery_featured = 1';
        }
        if (!empty($f['department_id'])) {
            $w[] = 'm.department_id = ?';
            $p[] = (int) $f['department_id'];
        }
        if (!empty($f['municipality_id'])) {
            $w[] = 'm.municipality_id = ?';
            $p[] = (int) $f['municipality_id'];
        }
        if (!empty($f['collection_id'])) {
            $w[] = 'm.collection_id = ?';
            $p[] = (int) $f['collection_id'];
        }
        if (!empty($f['gallery_section'])) {
            $w[] = 'm.gallery_section = ?';
            $p[] = $f['gallery_section'];
        }
        if (isset($f['q']) && trim((string) $f['q']) !== '') {
            $w[] = "(m.title LIKE ? ESCAPE '!' OR m.caption LIKE ? ESCAPE '!' OR m.original_name LIKE ? ESCAPE '!' OR m.credit LIKE ? ESCAPE '!')";
            $like = '%' . like_escape(trim((string) $f['q'])) . '%';
            array_push($p, $like, $like, $like, $like);
        }
        return [implode(' AND ', $w), $p];
    }

    /** Número de publicaciones y portadas que usan el archivo. */
    public static function usage(int $id): int
    {
        return (int) DB::value('SELECT COUNT(*) FROM entry_media WHERE media_id = ?', [$id])
            + (int) DB::value('SELECT COUNT(*) FROM entries WHERE cover_media_id = ?', [$id])
            + (int) DB::value('SELECT COUNT(*) FROM routes WHERE cover_media_id = ?', [$id])
            + (int) DB::value('SELECT COUNT(*) FROM sections WHERE cover_media_id = ?', [$id])
            + (int) DB::value('SELECT COUNT(*) FROM departments WHERE cover_media_id = ?', [$id]);
    }

    public static function create(array $data, ?int $userId): int
    {
        $data += ['title' => null, 'alt_text' => null];
        $data['uploaded_by'] = $userId;
        $data['created_at'] = $data['updated_at'] = now();
        return DB::insert('media', $data);
    }

    public static function delete(int $id): void
    {
        $m = self::find($id);
        if (!$m) {
            return;
        }
        DB::run('UPDATE departments SET cover_media_id = NULL WHERE cover_media_id = ?', [$id]);
        DB::run('DELETE FROM media WHERE id = ?', [$id]);
        MediaProcessor::deleteFiles($m);
    }

    /** Representación JSON para el selector multimedia y la galería pública. */
    public static function toJson(array $m): array
    {
        return [
            'id' => (int) $m['id'],
            'kind' => $m['kind'],
            'title' => $m['title'],
            'caption' => $m['caption'],
            'credit' => $m['credit'],
            'license' => $m['license'],
            'alt' => $m['alt_text'] ?: $m['title'],
            'width' => $m['width'] ? (int) $m['width'] : null,
            'height' => $m['height'] ? (int) $m['height'] : null,
            'thumb' => self::thumbUrl($m),
            'medium' => $m['kind'] === 'image' ? media_url($m, 'medium') : self::thumbUrl($m),
            'large' => $m['kind'] === 'image' ? media_url($m, 'large') : self::thumbUrl($m),
            'src' => in_array($m['kind'], ['video', 'audio', 'document'], true) ? media_url($m, 'original') : null,
            'embed' => $m['kind'] === 'embed' ? self::embedUrl($m) : null,
            'mime' => $m['mime'],
            'department' => $m['department_name'] ?? null,
            'municipality' => $m['municipality_name'] ?? null,
            'collection' => $m['collection_name'] ?? null,
            'isDemo' => (bool) $m['is_demo'],
        ];
    }

    public static function thumbUrl(array $m): string
    {
        return match ($m['kind']) {
            'image' => media_url($m, 'thumb'),
            'embed' => $m['embed_provider'] === 'youtube' ? 'https://i.ytimg.com/vi/' . rawurlencode((string) $m['embed_id']) . '/hqdefault.jpg' : asset('img/video-placeholder.svg'),
            'video' => asset('img/video-placeholder.svg'),
            'audio' => asset('img/audio-placeholder.svg'),
            default => asset('img/document-placeholder.svg'),
        };
    }

    public static function embedUrl(array $m): ?string
    {
        return match ($m['embed_provider']) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/' . rawurlencode((string) $m['embed_id']) . '?rel=0',
            'vimeo' => 'https://player.vimeo.com/video/' . rawurlencode((string) $m['embed_id']),
            default => null,
        };
    }
}
