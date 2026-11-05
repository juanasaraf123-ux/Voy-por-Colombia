<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Text
{
    /** Minúsculas, sin tildes ni símbolos: para búsquedas que no distinguen acentos. */
    public static function normalize(?string $text): string
    {
        $text = strtolower(Str::ascii((string) $text));
        $text = preg_replace('/[^a-z0-9 ]+/', ' ', $text) ?? '';
        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }

    /** Escapa los comodines de LIKE (se usa con ESCAPE '!'). */
    public static function likeEscape(string $text): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $text);
    }

    /** Markdown seguro: el HTML escrito por el usuario se escapa y los enlaces inseguros se descartan. */
    public static function markdown(?string $text): string
    {
        return (string) Str::markdown((string) $text, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
    }

    /** Texto plano de un Markdown (para buscador y resúmenes). */
    public static function plain(?string $markdown): string
    {
        return trim(html_entity_decode(strip_tags(self::markdown($markdown)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public static function limit(?string $text, int $limit = 160): string
    {
        return Str::limit(trim(strip_tags((string) $text)), $limit - 1, '…');
    }
}
