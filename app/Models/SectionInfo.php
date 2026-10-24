<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;
use App\Sections;

/** Presentación editable de cada sección (bloques de la portada y encabezados). */
final class SectionInfo
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            $rows = DB::all('SELECT s.*, m.path AS c_path, m.variants AS c_variants, m.alt_text AS c_alt, m.width AS c_width, m.height AS c_height
                FROM sections s LEFT JOIN media m ON m.id = s.cover_media_id ORDER BY s.sort_order, s.id');
            self::$cache = [];
            foreach ($rows as $r) {
                $key = $r['section_key'];
                $def = $key === 'gallery' ? Sections::GALLERY : (Sections::ALL[$key] ?? null);
                if (!$def) {
                    continue;
                }
                $r['def'] = $def;
                $r['url'] = url('/' . $def['path']);
                $r['cover'] = $r['c_path'] ? ['path' => $r['c_path'], 'variants' => $r['c_variants'], 'alt_text' => $r['c_alt'] ?: $r['title'], 'width' => $r['c_width'], 'height' => $r['c_height']] : null;
                self::$cache[$key] = $r;
            }
        }
        return self::$cache;
    }

    public static function get(string $key): array
    {
        $def = $key === 'gallery' ? Sections::GALLERY : Sections::get($key);
        return self::all()[$key] ?? ['section_key' => $key, 'title' => $def['label'], 'tagline' => '', 'description' => '', 'cover' => null, 'def' => $def, 'url' => url('/' . $def['path']), 'show_on_home' => 1];
    }

    public static function forHome(): array
    {
        return array_values(array_filter(self::all(), static fn ($s) => (int) $s['show_on_home'] === 1));
    }

    public static function forget(): void
    {
        self::$cache = null;
    }
}
