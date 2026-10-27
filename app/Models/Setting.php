<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database as DB;

/** Configuración editable desde el panel (textos de portada, redes sociales, etc.). */
final class Setting
{
    private static ?array $cache = null;

    public const DEFAULTS = [
        'site_name' => 'Voy por Colombia',
        'site_tagline' => 'Colombia, Tierra de Infinitos',
        'logo_pre' => 'Voy por',
        'logo_title' => 'Colombia',
        'logo_subtitle' => 'Tierra de infinitos',
        'hero_kicker' => 'Hay un país que todavía no conoces…',
        'hero_title' => 'Colombia te espera',
        'hero_subtitle' => 'Explora sus paisajes, culturas, sabores y vive experiencias únicas.',
        'hero_cta' => 'Explorar mapa',
        'hero_spotlight_id' => '',
        'hero_note' => 'Cada región, una historia',
        'hero_media_ids' => '[]',
        'hero_video_id' => '',
        'hero_video_background' => '0',
        'hero_words' => 'selva,mar,páramo,desierto,café,música,llanura',
        'map_title' => 'Un mapa para perderse (y encontrarse)',
        'map_subtitle' => 'Toca un departamento, acércate a sus municipios y descubre lo que guarda cada rincón.',
        'closing_title' => 'El infinito empieza en el próximo viaje',
        'closing_text' => 'Guarda tus lugares favoritos, arma tu ruta y deja que Colombia te sorprenda.',
        'footer_text' => 'Plataforma para descubrir la diversidad turística, gastronómica, cultural, natural y emprendedora de Colombia.',
        'social_instagram' => '',
        'social_facebook' => '',
        'social_youtube' => '',
        'social_tiktok' => '',
        'social_x' => '',
        'contact_email' => '',
        'home_featured_title' => 'Lugares que te cambian el ritmo',
        'home_gems_title' => 'Joyas poco conocidas',
    ];

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = self::DEFAULTS;
            try {
                foreach (DB::all('SELECT setting_key, setting_value FROM settings') as $row) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\PDOException) {
                // Base de datos aún no instalada: se usan los valores por defecto.
            }
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function json(string $key): array
    {
        $v = json_decode((string) self::get($key, '[]'), true);
        return is_array($v) ? $v : [];
    }

    public static function set(string $key, ?string $value): void
    {
        if (DB::value('SELECT 1 FROM settings WHERE setting_key = ?', [$key])) {
            DB::update('settings', ['setting_value' => $value, 'updated_at' => now()], 'setting_key = :k', ['k' => $key]);
        } else {
            DB::insert('settings', ['setting_key' => $key, 'setting_value' => $value, 'updated_at' => now()]);
        }
        self::$cache = null;
    }
}
