<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;
use App\Core\Markdown;
use App\Sections;

/**
 * Publicaciones de todas las secciones (destinos, platos, fincas, emprendimientos,
 * expresiones culturales y noticias). Comparten estructura y se diferencian por "section" y "meta".
 */
final class Entry
{
    public const STATUSES = ['draft', 'published', 'archived'];

    private const SELECT = 'SELECT e.*, d.name AS department_name, d.slug AS department_slug, m.name AS municipality_name, m.slug AS municipality_slug,
            r.name AS region_name, r.slug AS region_slug, r.color AS region_color,
            c.id AS c_id, c.path AS c_path, c.variants AS c_variants, c.alt_text AS c_alt_text, c.title AS c_title, c.width AS c_width, c.height AS c_height, c.credit AS c_credit, c.kind AS c_kind
        FROM entries e
        LEFT JOIN departments d ON d.id = e.department_id
        LEFT JOIN regions r ON r.id = d.region_id
        LEFT JOIN municipalities m ON m.id = e.municipality_id
        LEFT JOIN media c ON c.id = e.cover_media_id';

    /** Condición de visibilidad pública: publicado y con fecha de publicación cumplida. */
    public static function visibleSql(string $alias = 'e'): string
    {
        return "$alias.status = 'published' AND ($alias.published_at IS NULL OR $alias.published_at <= ?)";
    }

    /**
     * Consulta pública con filtros.
     * Filtros: section, sections[], category (slug), category_id, department_id, municipality_id, region (slug),
     * q, featured, hidden_gem, geo (con coordenadas), upcoming, ids[], exclude, order, limit, offset.
     */
    public static function query(array $f = [], bool $publicOnly = true): array
    {
        [$where, $params] = self::where($f, $publicOnly);
        $order = match ($f['order'] ?? 'recent') {
            'featured' => 'e.is_featured DESC, e.featured_order ASC, e.published_at DESC',
            'title' => 'e.title ASC',
            'event' => 'e.event_start ASC',
            'updated' => 'e.updated_at DESC',
            'random' => DB::driver() === 'mysql' ? 'RAND()' : 'RANDOM()',
            default => 'COALESCE(e.published_at, e.created_at) DESC, e.id DESC',
        };
        $limit = max(1, min(500, (int) ($f['limit'] ?? 24)));
        $offset = max(0, (int) ($f['offset'] ?? 0));
        $rows = DB::all(self::SELECT . " WHERE $where ORDER BY $order LIMIT $limit OFFSET $offset", $params);
        return self::hydrate($rows);
    }

    public static function count(array $f = [], bool $publicOnly = true): int
    {
        [$where, $params] = self::where($f, $publicOnly);
        return (int) DB::value("SELECT COUNT(*) FROM entries e LEFT JOIN departments d ON d.id = e.department_id LEFT JOIN regions r ON r.id = d.region_id WHERE $where", $params);
    }

    private static function where(array $f, bool $publicOnly): array
    {
        $w = ['1 = 1'];
        $p = [];
        if ($publicOnly) {
            $w[] = self::visibleSql();
            $p[] = now();
        } elseif (!empty($f['status'])) {
            if ($f['status'] === 'scheduled') {
                $w[] = "e.status = 'published' AND e.published_at > ?";
                $p[] = now();
            } else {
                $w[] = 'e.status = ?';
                $p[] = $f['status'];
            }
        }
        if (!empty($f['section'])) {
            $w[] = 'e.section = ?';
            $p[] = $f['section'];
        }
        if (!empty($f['sections'])) {
            $w[] = 'e.section IN (' . DB::placeholders($f['sections']) . ')';
            array_push($p, ...$f['sections']);
        }
        if (!empty($f['category'])) {
            $w[] = 'EXISTS (SELECT 1 FROM entry_categories ec JOIN categories cat ON cat.id = ec.category_id WHERE ec.entry_id = e.id AND cat.slug = ?)';
            $p[] = $f['category'];
        }
        if (!empty($f['category_id'])) {
            $w[] = 'EXISTS (SELECT 1 FROM entry_categories ec WHERE ec.entry_id = e.id AND ec.category_id = ?)';
            $p[] = (int) $f['category_id'];
        }
        if (!empty($f['department_id'])) {
            $w[] = 'e.department_id = ?';
            $p[] = (int) $f['department_id'];
        }
        if (!empty($f['municipality_id'])) {
            $w[] = 'e.municipality_id = ?';
            $p[] = (int) $f['municipality_id'];
        }
        if (!empty($f['region'])) {
            $w[] = 'r.slug = ?';
            $p[] = $f['region'];
        }
        if (!empty($f['featured'])) {
            $w[] = 'e.is_featured = 1';
        }
        if (!empty($f['hidden_gem'])) {
            $w[] = 'e.is_hidden_gem = 1';
        }
        if (!empty($f['geo'])) {
            $w[] = 'e.lat IS NOT NULL AND e.lng IS NOT NULL AND e.show_on_map = 1';
        }
        if (!empty($f['upcoming'])) {
            $w[] = 'e.event_start IS NOT NULL AND COALESCE(e.event_end, e.event_start) >= ?';
            $p[] = date('Y-m-d 00:00:00');
        }
        if (!empty($f['event_from'])) {
            $w[] = 'e.event_start IS NOT NULL AND COALESCE(e.event_end, e.event_start) >= ? AND e.event_start <= ?';
            $p[] = $f['event_from'];
            $p[] = $f['event_to'];
        }
        if (!empty($f['ids'])) {
            $w[] = 'e.id IN (' . DB::placeholders($f['ids']) . ')';
            array_push($p, ...array_map('intval', $f['ids']));
        }
        if (!empty($f['exclude'])) {
            $w[] = 'e.id <> ?';
            $p[] = (int) $f['exclude'];
        }
        if (!empty($f['is_demo'])) {
            $w[] = 'e.is_demo = 1';
        }
        if (isset($f['q']) && trim((string) $f['q']) !== '') {
            $normalized = search_normalize((string) $f['q']);
            if ($normalized === '') {
                // Solo símbolos: no hay nada que buscar.
                $w[] = '1 = 0';
            }
            foreach (array_slice(explode(' ', $normalized), 0, 6) as $term) {
                if ($term === '') {
                    continue;
                }
                $w[] = "e.search_text LIKE ? ESCAPE '!'";
                $p[] = '%' . like_escape($term) . '%';
            }
        }
        return [implode(' AND ', $w), $p];
    }

    /** Agrega portada, categorías y meta decodificada a cada fila. */
    public static function hydrate(array $rows): array
    {
        if (!$rows) {
            return [];
        }
        $ids = array_map(static fn ($r) => (int) $r['id'], $rows);
        $cats = DB::all(
            'SELECT ec.entry_id, c.id, c.name, c.slug, c.icon, c.color FROM entry_categories ec JOIN categories c ON c.id = ec.category_id
             WHERE ec.entry_id IN (' . DB::placeholders($ids) . ') ORDER BY c.sort_order, c.name',
            $ids
        );
        $byEntry = [];
        foreach ($cats as $c) {
            $byEntry[(int) $c['entry_id']][] = $c;
        }
        foreach ($rows as &$r) {
            $r['cover'] = $r['c_id'] ? [
                'id' => $r['c_id'], 'path' => $r['c_path'], 'variants' => $r['c_variants'], 'alt_text' => $r['c_alt_text'] ?: $r['title'],
                'title' => $r['c_title'], 'width' => $r['c_width'], 'height' => $r['c_height'], 'credit' => $r['c_credit'], 'kind' => $r['c_kind'],
            ] : null;
            foreach (['c_id', 'c_path', 'c_variants', 'c_alt_text', 'c_title', 'c_width', 'c_height', 'c_credit', 'c_kind'] as $k) {
                unset($r[$k]);
            }
            $r['categories'] = $byEntry[(int) $r['id']] ?? [];
            $r['meta'] = json_decode((string) ($r['meta'] ?? ''), true) ?: [];
            $r['url'] = Sections::exists($r['section']) ? Sections::url($r['section'], $r['slug']) : '#';
            $r['effective_status'] = ($r['status'] === 'published' && $r['published_at'] && $r['published_at'] > now()) ? 'scheduled' : $r['status'];
        }
        return $rows;
    }

    public static function find(int $id): ?array
    {
        $row = DB::one(self::SELECT . ' WHERE e.id = ?', [$id]);
        return $row ? self::hydrate([$row])[0] : null;
    }

    public static function findPublic(string $section, string $slug): ?array
    {
        $row = DB::one(self::SELECT . ' WHERE e.section = ? AND e.slug = ? AND ' . self::visibleSql(), [$section, $slug, now()]);
        return $row ? self::hydrate([$row])[0] : null;
    }

    /** Multimedia asociada (galería, videos y audios), en orden. */
    public static function media(int $entryId): array
    {
        return DB::all('SELECT m.*, em.sort_order FROM entry_media em JOIN media m ON m.id = em.media_id WHERE em.entry_id = ? ORDER BY em.sort_order, m.id', [$entryId]);
    }

    /** Lugares cercanos (distancia en km) entre los publicados con coordenadas. */
    public static function nearby(array $entry, int $limit = 6, float $radiusKm = 120): array
    {
        if ($entry['lat'] === null || $entry['lng'] === null) {
            return $entry['department_id'] ? self::query(['department_id' => $entry['department_id'], 'exclude' => $entry['id'], 'limit' => $limit, 'order' => 'featured']) : [];
        }
        $lat = (float) $entry['lat'];
        $lng = (float) $entry['lng'];
        $dLat = $radiusKm / 111;
        $dLng = $radiusKm / (111 * max(0.2, cos(deg2rad($lat))));
        $rows = DB::all(
            self::SELECT . ' WHERE ' . self::visibleSql() . ' AND e.id <> ? AND e.lat BETWEEN ? AND ? AND e.lng BETWEEN ? AND ? LIMIT 200',
            [now(), (int) $entry['id'], $lat - $dLat, $lat + $dLat, $lng - $dLng, $lng + $dLng]
        );
        foreach ($rows as &$r) {
            $r['distance_km'] = self::haversine($lat, $lng, (float) $r['lat'], (float) $r['lng']);
        }
        unset($r);
        usort($rows, static fn ($a, $b) => $a['distance_km'] <=> $b['distance_km']);
        return self::hydrate(array_slice($rows, 0, $limit));
    }

    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }

    public static function uniqueSlug(string $section, string $title, ?int $ignoreId = null): string
    {
        $base = mb_substr(slugify($title), 0, 180);
        $slug = $base;
        $i = 2;
        while (DB::value('SELECT id FROM entries WHERE section = ? AND slug = ? AND id <> ?', [$section, $slug, $ignoreId ?? 0])) {
            $slug = "$base-$i";
            $i++;
        }
        return $slug;
    }

    /** Texto normalizado para el buscador (título, resumen, cuerpo, lugar y categorías). */
    public static function buildSearchText(array $data, array $categoryIds = []): string
    {
        $parts = [$data['title'] ?? '', $data['summary'] ?? '', Markdown::plain((string) ($data['body'] ?? ''))];
        if (!empty($data['department_id'])) {
            $parts[] = (string) DB::value('SELECT name FROM departments WHERE id = ?', [(int) $data['department_id']]);
        }
        if (!empty($data['municipality_id'])) {
            $parts[] = (string) DB::value('SELECT name FROM municipalities WHERE id = ?', [(int) $data['municipality_id']]);
        }
        if ($categoryIds) {
            $parts[] = implode(' ', array_column(DB::all('SELECT name FROM categories WHERE id IN (' . DB::placeholders($categoryIds) . ')', $categoryIds), 'name'));
        }
        $meta = json_decode((string) ($data['meta'] ?? ''), true) ?: [];
        foreach ($meta as $v) {
            $parts[] = is_array($v) ? implode(' ', $v) : (string) $v;
        }
        return mb_substr(search_normalize(implode(' ', $parts)), 0, 20000);
    }

    /** Guarda (crea o actualiza) con categorías y multimedia asociadas. Devuelve el id. */
    public static function save(?int $id, array $data, array $categoryIds, array $mediaIds, ?int $userId): int
    {
        return DB::transaction(static function () use ($id, $data, $categoryIds, $mediaIds, $userId) {
            $data['search_text'] = self::buildSearchText($data, $categoryIds);
            $data['updated_at'] = now();
            $data['updated_by'] = $userId;
            if ($id) {
                DB::update('entries', $data, 'id = :eid', ['eid' => $id]);
            } else {
                $data['created_at'] = now();
                $data['created_by'] = $userId;
                $id = DB::insert('entries', $data);
            }
            DB::run('DELETE FROM entry_categories WHERE entry_id = ?', [$id]);
            foreach ($categoryIds as $cid) {
                DB::insert('entry_categories', ['entry_id' => $id, 'category_id' => $cid]);
            }
            DB::run('DELETE FROM entry_media WHERE entry_id = ?', [$id]);
            foreach (array_values(array_unique($mediaIds)) as $i => $mid) {
                DB::insert('entry_media', ['entry_id' => $id, 'media_id' => $mid, 'sort_order' => $i]);
            }
            return $id;
        });
    }

    public static function setStatus(int $id, string $status, ?int $userId): void
    {
        $data = ['status' => $status, 'updated_at' => now(), 'updated_by' => $userId];
        if ($status === 'published' && !DB::value('SELECT published_at FROM entries WHERE id = ?', [$id])) {
            $data['published_at'] = now();
        }
        DB::update('entries', $data, 'id = :eid', ['eid' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::run('DELETE FROM entries WHERE id = ?', [$id]);
    }

    /** Datos ligeros para marcadores del mapa. */
    public static function markers(array $f = []): array
    {
        $rows = self::query($f + ['geo' => 1, 'limit' => 500, 'order' => 'featured']);
        return array_map(static fn ($r) => self::toCard($r), $rows);
    }

    /** Representación compacta (JSON) para mapa, favoritos y rutas. */
    public static function toCard(array $r): array
    {
        return [
            'id' => (int) $r['id'],
            'section' => $r['section'],
            'sectionLabel' => Sections::ALL[$r['section']]['label'] ?? '',
            'color' => Sections::ALL[$r['section']]['color'] ?? '#0f9d77',
            'title' => $r['title'],
            'summary' => str_limit($r['summary'] ?? '', 160),
            'url' => $r['url'],
            'lat' => $r['lat'] !== null ? (float) $r['lat'] : null,
            'lng' => $r['lng'] !== null ? (float) $r['lng'] : null,
            'department' => $r['department_name'],
            'departmentSlug' => $r['department_slug'],
            'municipality' => $r['municipality_name'],
            'municipalitySlug' => $r['municipality_slug'],
            'featured' => (bool) $r['is_featured'],
            'hiddenGem' => (bool) $r['is_hidden_gem'],
            'categories' => array_map(static fn ($c) => ['slug' => $c['slug'], 'name' => $c['name'], 'icon' => $c['icon'], 'color' => $c['color']], $r['categories']),
            'thumb' => $r['cover'] ? media_url($r['cover'], 'thumb') : null,
            'image' => $r['cover'] ? media_url($r['cover'], 'medium') : null,
            'activities' => array_slice((array) ($r['meta']['activities'] ?? []), 0, 5),
            'isDemo' => (bool) $r['is_demo'],
        ];
    }
}
