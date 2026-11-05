<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga los datos de database/data/snapshot.json:
 * - Siempre: regiones, departamentos, los 1.122 municipios, categorías, secciones, colecciones e imagen de portada.
 * - Con demostración (DemoSeeder): publicaciones, ilustraciones, video, audio y rutas marcadas como demo.
 * No crea cuentas ni contraseñas: la primera cuenta se crea desde /admin (localhost) o con «php artisan vpc:admin».
 */
class DatabaseSeeder extends Seeder
{
    public static bool $withDemo = false;

    public function run(): void
    {
        $data = json_decode((string) file_get_contents(database_path('data/snapshot.json')), true);
        $demo = static::$withDemo;
        $demoMedia = array_column(array_filter($data['media'], fn ($m) => $m['is_demo']), 'id');

        DB::transaction(function () use ($data, $demo, $demoMedia) {
            foreach (['regions', 'collections', 'departments', 'municipalities', 'categories'] as $table) {
                $this->insert($table, $data[$table], fn ($r) => $table === 'departments' && !$demo ? ['cover_media_id' => null] + $r : $r);
            }
            $this->insert('media', array_filter($data['media'], fn ($m) => $demo || !$m['is_demo']));
            $this->insert('sections', $data['sections'], fn ($r) => !$demo && in_array($r['cover_media_id'], $demoMedia) ? ['cover_media_id' => null] + $r : $r);
            if ($demo) {
                foreach (['entries', 'entry_categories', 'entry_media', 'routes', 'route_stops'] as $table) {
                    $this->insert($table, $data[$table]);
                }
            }
            foreach ($data['settings'] as $s) {
                if (!$demo && in_array($s['setting_key'], ['hero_spotlight_id', 'hero_video_id'], true)) {
                    continue;
                }
                DB::table('settings')->updateOrInsert(['setting_key' => $s['setting_key']], ['setting_value' => $s['setting_value'], 'updated_at' => now()]);
            }
        });
        $this->resetSequences();
        Setting::forget();
    }

    private function insert(string $table, array $rows, ?callable $map = null): void
    {
        if (DB::table($table)->exists()) {
            return; // ya cargado: no se duplica
        }
        $rows = array_values(array_map($map ?? fn ($r) => $r, $rows));
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /** PostgreSQL: después de insertar ids explícitos, las secuencias deben continuar desde el máximo. */
    private function resetSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        foreach (['regions', 'collections', 'departments', 'municipalities', 'categories', 'media', 'sections', 'entries', 'routes'] as $table) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$table', 'id'), COALESCE((SELECT MAX(id) FROM $table), 1))");
        }
    }
}
