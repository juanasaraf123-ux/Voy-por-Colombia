<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;

/** Regiones naturales, departamentos y municipios. */
final class Geo
{
    private static ?array $departments = null;

    public static function regions(): array
    {
        return DB::all('SELECT * FROM regions ORDER BY sort_order, name');
    }

    public static function departments(): array
    {
        return self::$departments ??= DB::all(
            'SELECT d.*, r.name AS region_name, r.slug AS region_slug, r.color AS region_color FROM departments d LEFT JOIN regions r ON r.id = d.region_id ORDER BY d.name'
        );
    }

    public static function department(int $id): ?array
    {
        return DB::one('SELECT d.*, r.name AS region_name, r.slug AS region_slug, r.color AS region_color FROM departments d LEFT JOIN regions r ON r.id = d.region_id WHERE d.id = ?', [$id]);
    }

    public static function departmentBySlug(string $slug): ?array
    {
        return DB::one('SELECT d.*, r.name AS region_name, r.slug AS region_slug, r.color AS region_color FROM departments d LEFT JOIN regions r ON r.id = d.region_id WHERE d.slug = ?', [$slug]);
    }

    public static function municipalities(int $departmentId): array
    {
        return DB::all('SELECT id, name, slug, lat, lng FROM municipalities WHERE department_id = ? ORDER BY name', [$departmentId]);
    }

    public static function municipality(int $id): ?array
    {
        return DB::one('SELECT m.*, d.name AS department_name, d.slug AS department_slug FROM municipalities m JOIN departments d ON d.id = m.department_id WHERE m.id = ?', [$id]);
    }

    public static function municipalityBySlug(int $departmentId, string $slug): ?array
    {
        return DB::one('SELECT * FROM municipalities WHERE department_id = ? AND slug = ?', [$departmentId, $slug]);
    }

    /** Comprueba que el municipio pertenezca al departamento indicado. */
    public static function municipalityBelongs(int $municipalityId, int $departmentId): bool
    {
        return (bool) DB::value('SELECT 1 FROM municipalities WHERE id = ? AND department_id = ?', [$municipalityId, $departmentId]);
    }

    /** Conteo de publicaciones visibles por departamento (para el mapa). */
    public static function entryCountsByDepartment(): array
    {
        $rows = DB::all('SELECT d.slug, COUNT(e.id) AS total FROM departments d JOIN entries e ON e.department_id = d.id WHERE ' . Entry::visibleSql() . ' GROUP BY d.slug', [now()]);
        return array_column($rows, 'total', 'slug');
    }

    public static function search(string $q, int $limit = 8): array
    {
        if (search_normalize($q) === '') {
            return [];
        }
        $term = '%' . like_escape(search_normalize($q)) . '%';
        $deps = DB::all("SELECT 'department' AS type, id, name, slug, NULL AS department_slug, NULL AS department_name FROM departments WHERE search_name LIKE ? ESCAPE '!' ORDER BY name LIMIT $limit", [$term]);
        $muns = DB::all("SELECT 'municipality' AS type, m.id, m.name, m.slug, d.slug AS department_slug, d.name AS department_name FROM municipalities m JOIN departments d ON d.id = m.department_id
            WHERE m.search_name LIKE ? ESCAPE '!' ORDER BY m.name LIMIT $limit", [$term]);
        return array_merge($deps, $muns);
    }

    /** Datos para colorear el mapa: color de región por departamento y lista de regiones con sus departamentos. */
    public static function mapMeta(): array
    {
        $colors = [];
        $regions = [];
        foreach (self::regions() as $r) {
            $regions[$r['slug']] = ['slug' => $r['slug'], 'name' => $r['name'], 'color' => $r['color'], 'departments' => []];
        }
        foreach (self::departments() as $d) {
            $colors[$d['slug']] = $d['region_color'] ?? '#2bb673';
            if ($d['region_slug']) {
                $regions[$d['region_slug']]['departments'][] = $d['slug'];
            }
        }
        return ['colors' => $colors, 'regions' => array_values($regions)];
    }
}
