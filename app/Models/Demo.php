<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;

/** Utilidades para el contenido de demostración. */
final class Demo
{
    public static function counts(): array
    {
        return [
            'entries' => (int) DB::value('SELECT COUNT(*) FROM entries WHERE is_demo = 1'),
            'routes' => (int) DB::value('SELECT COUNT(*) FROM routes WHERE is_demo = 1'),
            'media' => (int) DB::value('SELECT COUNT(*) FROM media WHERE is_demo = 1'),
        ];
    }

    /** Elimina publicaciones, rutas y archivos de demostración. El contenido real no se toca. */
    public static function remove(): array
    {
        $counts = self::counts();
        DB::transaction(static function () {
            DB::run('DELETE FROM routes WHERE is_demo = 1');
            DB::run('DELETE FROM entries WHERE is_demo = 1');
        });
        $heroIds = Setting::json('hero_media_ids');
        foreach (DB::all('SELECT id FROM media WHERE is_demo = 1') as $m) {
            Media::delete((int) $m['id']);
        }
        Setting::set('hero_media_ids', json_encode(array_values(array_filter($heroIds, static fn ($id) => Media::find((int) $id) !== null))));
        return $counts;
    }
}
