<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

/** Configuración editable desde el panel (textos de portada, redes sociales, etc.). */
class Setting extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'setting_key';
    protected $keyType = 'string';
    protected $guarded = [];

    public const DEFAULTS = [
        'site_name' => 'Voy por Colombia',
        'site_tagline' => 'Colombia, Tierra de Infinitos',
        'logo_pre' => 'Voy por',
        'logo_title' => 'Colombia',
        'logo_subtitle' => 'Tierra de infinitos',
        'hero_kicker' => 'Hay un país que todavía no conoces…',
        'hero_title' => 'Colombia te espera',
        'hero_subtitle' => 'Explora sus paisajes, culturas, sabores y vive experiencias únicas.',
        'hero_spotlight_id' => '',
        'hero_note' => 'Cada región, una historia',
        'hero_media_ids' => '[]',
        'hero_video_id' => '',
        'map_hint' => 'Toca un departamento para acercarte',
        'footer_text' => 'Plataforma para descubrir la diversidad turística, gastronómica, cultural, natural y emprendedora de Colombia.',
        'social_instagram' => '',
        'social_facebook' => '',
        'social_youtube' => '',
        'social_tiktok' => '',
        'social_x' => '',
        'contact_email' => '',
    ];

    private static ?array $cache = null;

    public static function allValues(): array
    {
        if (self::$cache === null) {
            self::$cache = self::DEFAULTS;
            try {
                foreach (static::query()->get() as $row) {
                    self::$cache[$row->setting_key] = $row->setting_value;
                }
            } catch (Throwable) {
                // Base aún no instalada: valores por defecto.
            }
        }
        return self::$cache;
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return self::allValues()[$key] ?? $default;
    }

    public static function json(string $key): array
    {
        $v = json_decode((string) self::value($key, '[]'), true);
        return is_array($v) ? $v : [];
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrInsert(['setting_key' => $key], ['setting_value' => $value, 'updated_at' => now()]);
        self::$cache = null;
    }

    public static function forget(): void
    {
        self::$cache = null;
    }
}
