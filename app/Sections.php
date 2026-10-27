<?php
declare(strict_types=1);

namespace App;

/**
 * Secciones de contenido de la plataforma y sus campos específicos.
 * Los textos de presentación (título, lema, portada, orden) se editan desde el panel (tabla "sections").
 * Aquí se define la estructura: qué campos tiene cada tipo de publicación.
 */
final class Sections
{
    public const ALL = [
        'tourism' => ['path' => 'turismo', 'label' => 'Turismo', 'singular' => 'destino', 'icon' => 'compass', 'color' => '#0f9d77', 'map' => true],
        'gastronomy' => ['path' => 'gastronomia', 'label' => 'Gastronomía', 'singular' => 'sabor', 'icon' => 'fork', 'color' => '#ff6b2c', 'map' => true],
        'coffee' => ['path' => 'cafe', 'label' => 'Café', 'singular' => 'experiencia cafetera', 'icon' => 'coffee', 'color' => '#8a4b2a', 'map' => true],
        'entrepreneurship' => ['path' => 'emprendimiento', 'label' => 'Emprendimiento', 'singular' => 'emprendimiento', 'icon' => 'store', 'color' => '#7b3fe4', 'map' => true],
        'culture' => ['path' => 'cultura', 'label' => 'Cultura', 'singular' => 'expresión cultural', 'icon' => 'mask', 'color' => '#ff3f8e', 'map' => true],
        'news' => ['path' => 'noticias', 'label' => 'Noticias', 'singular' => 'noticia', 'icon' => 'news', 'color' => '#1d6fe0', 'map' => false],
    ];

    /** La galería no es un tipo de publicación: se alimenta de la biblioteca multimedia. */
    public const GALLERY = ['path' => 'galeria', 'label' => 'Galería de fotos', 'icon' => 'camera', 'color' => '#f2a900'];

    /**
     * Campos adicionales por sección (se guardan en entries.meta como JSON).
     * Tipos: text, textarea (markdown), lines (lista, uno por línea), url, email, tel, select.
     */
    public const FIELDS = [
        'tourism' => [
            ['key' => 'activities', 'label' => 'Actividades disponibles', 'type' => 'lines', 'help' => 'Una actividad por línea.'],
            ['key' => 'best_time', 'label' => 'Mejor época para visitar', 'type' => 'text'],
            ['key' => 'how_to_get', 'label' => 'Cómo llegar', 'type' => 'textarea'],
            ['key' => 'altitude', 'label' => 'Altitud o clima', 'type' => 'text'],
            ['key' => 'recommendations', 'label' => 'Recomendaciones', 'type' => 'lines'],
        ],
        'gastronomy' => [
            ['key' => 'dish_type', 'label' => 'Tipo', 'type' => 'select', 'options' => ['Plato típico', 'Bebida', 'Postre', 'Ingrediente', 'Receta', 'Tradición culinaria', 'Experiencia gastronómica']],
            ['key' => 'ingredients', 'label' => 'Ingredientes', 'type' => 'lines', 'help' => 'Un ingrediente por línea.'],
            ['key' => 'preparation', 'label' => 'Preparación', 'type' => 'lines', 'help' => 'Un paso por línea.'],
            ['key' => 'history', 'label' => 'Historia del plato', 'type' => 'textarea'],
            ['key' => 'origin', 'label' => 'Lugar de origen', 'type' => 'text'],
        ],
        'coffee' => [
            ['key' => 'altitude', 'label' => 'Altitud (m s. n. m.)', 'type' => 'text'],
            ['key' => 'varieties', 'label' => 'Variedades', 'type' => 'text'],
            ['key' => 'process', 'label' => 'Proceso', 'type' => 'text', 'help' => 'Lavado, honey, natural…'],
            ['key' => 'tasting_notes', 'label' => 'Notas de cata', 'type' => 'text'],
            ['key' => 'step', 'label' => 'Paso del proceso (1-9)', 'type' => 'text', 'help' => 'Solo para contenidos de la categoría «Proceso».'],
            ['key' => 'contact_phone', 'label' => 'Teléfono de contacto', 'type' => 'tel'],
            ['key' => 'contact_email', 'label' => 'Correo de contacto', 'type' => 'email'],
            ['key' => 'website', 'label' => 'Sitio web', 'type' => 'url'],
            ['key' => 'address', 'label' => 'Dirección o indicaciones', 'type' => 'text'],
        ],
        'entrepreneurship' => [
            ['key' => 'products', 'label' => 'Productos o servicios', 'type' => 'lines'],
            ['key' => 'contact_name', 'label' => 'Persona de contacto', 'type' => 'text'],
            ['key' => 'contact_phone', 'label' => 'Teléfono', 'type' => 'tel'],
            ['key' => 'contact_email', 'label' => 'Correo', 'type' => 'email'],
            ['key' => 'website', 'label' => 'Sitio web', 'type' => 'url'],
            ['key' => 'instagram', 'label' => 'Instagram (usuario)', 'type' => 'text'],
            ['key' => 'address', 'label' => 'Dirección', 'type' => 'text'],
        ],
        'culture' => [
            ['key' => 'venue', 'label' => 'Lugar o escenario', 'type' => 'text'],
            ['key' => 'community', 'label' => 'Comunidad o portadores de la tradición', 'type' => 'text'],
            ['key' => 'schedule', 'label' => 'Temporada o fechas habituales', 'type' => 'text'],
            ['key' => 'respect_note', 'label' => 'Nota de respeto cultural', 'type' => 'textarea', 'help' => 'Pautas para visitar o fotografiar de forma respetuosa.'],
        ],
        'news' => [
            ['key' => 'source', 'label' => 'Fuente', 'type' => 'text'],
            ['key' => 'source_url', 'label' => 'Enlace a la fuente', 'type' => 'url'],
        ],
    ];

    public static function exists(string $key): bool
    {
        return isset(self::ALL[$key]);
    }

    public static function get(string $key): array
    {
        return self::ALL[$key] ?? throw new \InvalidArgumentException("Sección desconocida: $key");
    }

    public static function byPath(string $path): ?string
    {
        foreach (self::ALL as $key => $s) {
            if ($s['path'] === $path) {
                return $key;
            }
        }
        return null;
    }

    public static function url(string $key, ?string $slug = null): string
    {
        $path = '/' . self::get($key)['path'];
        return url($slug ? "$path/$slug" : $path);
    }

    /** Tipos de punto del mapa («puntos vivos») y su ícono. */
    public const POINT_TYPES = [
        'Cascada' => 'waterfall', 'Mirador' => 'binoculars', 'Sendero' => 'footprints', 'Playa' => 'wave',
        'Río o laguna' => 'wave', 'Montaña o páramo' => 'mountain', 'Parque natural' => 'leaf', 'Desierto' => 'sun',
        'Pueblo patrimonial' => 'columns', 'Ciudad' => 'city', 'Museo o sitio arqueológico' => 'columns',
        'Restaurante o sabor típico' => 'fork', 'Finca o cafetal' => 'coffee', 'Mercado o artesanías' => 'store',
        'Festividad' => 'party', 'Música y danza' => 'music', 'Experiencia comunitaria' => 'users',
        'Avistamiento de fauna' => 'bird', 'Hospedaje o camping' => 'tent',
    ];

    /** Campos de presentación comunes (tarjetas del mapa y destacados). Las noticias no los usan. */
    public const COMMON_FIELDS = [
        ['key' => 'short_name', 'label' => 'Nombre corto', 'type' => 'text', 'help' => 'Para tarjetas y el mapa. Ej.: «Caño Cristales».'],
        ['key' => 'tagline', 'label' => 'Frase destacada', 'type' => 'text', 'help' => 'Una frase breve y evocadora. Ej.: «Un río de colores».'],
        ['key' => 'point_type', 'label' => 'Tipo de punto en el mapa', 'type' => 'select', 'options' => self::POINT_TYPE_LABELS, 'help' => 'Define el ícono animado del marcador.'],
    ];
    public const POINT_TYPE_LABELS = ['Cascada', 'Mirador', 'Sendero', 'Playa', 'Río o laguna', 'Montaña o páramo', 'Parque natural', 'Desierto',
        'Pueblo patrimonial', 'Ciudad', 'Museo o sitio arqueológico', 'Restaurante o sabor típico', 'Finca o cafetal', 'Mercado o artesanías',
        'Festividad', 'Música y danza', 'Experiencia comunitaria', 'Avistamiento de fauna', 'Hospedaje o camping'];

    public static function fields(string $key): array
    {
        return $key === 'news' ? (self::FIELDS[$key] ?? []) : array_merge(self::COMMON_FIELDS, self::FIELDS[$key] ?? []);
    }

    /** Secciones con eventos de calendario (fecha de inicio y fin). */
    public static function hasEvents(string $key): bool
    {
        return in_array($key, ['culture', 'news', 'gastronomy', 'coffee'], true);
    }
}
