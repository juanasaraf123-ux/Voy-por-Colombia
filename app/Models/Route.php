<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;

/** Rutas turísticas curadas desde el panel (varias paradas en orden). */
final class Route
{
    public static function list(bool $publicOnly = true, int $limit = 50, bool $featured = false): array
    {
        $where = $publicOnly ? "r.status = 'published'" : '1 = 1';
        if ($featured) {
            $where .= ' AND r.is_featured = 1';
        }
        $rows = DB::all("SELECT r.*, m.path AS c_path, m.variants AS c_variants, m.alt_text AS c_alt,
                (SELECT COUNT(*) FROM route_stops s WHERE s.route_id = r.id) AS stops_count
            FROM routes r LEFT JOIN media m ON m.id = r.cover_media_id WHERE $where ORDER BY r.is_featured DESC, r.updated_at DESC LIMIT " . max(1, $limit));
        return array_map([self::class, 'shape'], $rows);
    }

    private static function shape(array $r): array
    {
        $r['cover'] = $r['c_path'] ? ['path' => $r['c_path'], 'variants' => $r['c_variants'], 'alt_text' => $r['c_alt'] ?: $r['title']] : null;
        $r['url'] = url('/rutas/' . $r['slug']);
        return $r;
    }

    public static function find(int $id): ?array
    {
        $r = DB::one('SELECT r.*, m.path AS c_path, m.variants AS c_variants, m.alt_text AS c_alt FROM routes r LEFT JOIN media m ON m.id = r.cover_media_id WHERE r.id = ?', [$id]);
        return $r ? self::shape($r) : null;
    }

    public static function findPublic(string $slug): ?array
    {
        $r = DB::one("SELECT r.*, m.path AS c_path, m.variants AS c_variants, m.alt_text AS c_alt FROM routes r LEFT JOIN media m ON m.id = r.cover_media_id WHERE r.slug = ? AND r.status = 'published'", [$slug]);
        return $r ? self::shape($r) : null;
    }

    /** Paradas de la ruta (solo publicaciones visibles cuando $publicOnly). */
    public static function stops(int $routeId, bool $publicOnly = true): array
    {
        $rows = DB::all('SELECT entry_id, note FROM route_stops WHERE route_id = ? ORDER BY sort_order', [$routeId]);
        if (!$rows) {
            return [];
        }
        $ids = array_map('intval', array_column($rows, 'entry_id'));
        $entries = array_column(Entry::query(['ids' => $ids, 'limit' => 100], $publicOnly), null, 'id');
        $out = [];
        foreach ($rows as $row) {
            if (isset($entries[$row['entry_id']])) {
                $out[] = $entries[$row['entry_id']] + ['stop_note' => $row['note']];
            }
        }
        return $out;
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = mb_substr(slugify($title), 0, 180);
        $slug = $base;
        $i = 2;
        while (DB::value('SELECT id FROM routes WHERE slug = ? AND id <> ?', [$slug, $ignoreId ?? 0])) {
            $slug = "$base-$i";
            $i++;
        }
        return $slug;
    }

    public static function save(?int $id, array $data, array $stops, ?int $userId): int
    {
        return DB::transaction(static function () use ($id, $data, $stops, $userId) {
            $data['updated_at'] = now();
            if ($id) {
                DB::update('routes', $data, 'id = :rid', ['rid' => $id]);
            } else {
                $data['created_at'] = now();
                $data['created_by'] = $userId;
                $id = DB::insert('routes', $data);
            }
            DB::run('DELETE FROM route_stops WHERE route_id = ?', [$id]);
            foreach (array_values($stops) as $i => $stop) {
                DB::insert('route_stops', ['route_id' => $id, 'entry_id' => $stop['entry_id'], 'sort_order' => $i, 'note' => $stop['note'] ?: null]);
            }
            return $id;
        });
    }
}
