<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;

final class Category
{
    public static function forSection(string $section): array
    {
        return DB::all('SELECT * FROM categories WHERE section = ? ORDER BY sort_order, name', [$section]);
    }

    /** Categorías con al menos una publicación visible. */
    public static function usedInSection(string $section): array
    {
        return DB::all(
            'SELECT c.*, COUNT(e.id) AS total FROM categories c JOIN entry_categories ec ON ec.category_id = c.id JOIN entries e ON e.id = ec.entry_id
             WHERE c.section = ? AND ' . Entry::visibleSql() . ' GROUP BY c.id, c.section, c.name, c.slug, c.icon, c.color, c.sort_order ORDER BY c.sort_order, c.name',
            [$section, now()]
        );
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    /** Filtra los ids recibidos dejando solo los que pertenecen a la sección. */
    public static function validIds(string $section, array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $params = array_merge([$section], $ids);
        return array_map('intval', array_column(DB::all('SELECT id FROM categories WHERE section = ? AND id IN (' . DB::placeholders($ids) . ')', $params), 'id'));
    }
}
