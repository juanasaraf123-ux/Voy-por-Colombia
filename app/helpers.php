<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

function env(string $key, mixed $default = null): mixed
{
    return App\Core\Env::get($key, $default);
}

/** Escapa texto para HTML. Úsalo en TODA salida de datos. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string
{
    return rtrim((string) env('APP_URL', ''), '/');
}

/** Ruta relativa a la raíz del sitio (soporta instalación en subcarpeta mediante APP_BASE_PATH). */
function url(string $path = '/', array $query = []): string
{
    $prefix = rtrim((string) env('APP_BASE_PATH', ''), '/');
    $path = '/' . ltrim($path, '/');
    $qs = $query ? '?' . http_build_query(array_filter($query, static fn ($v) => $v !== null && $v !== '')) : '';
    return $prefix . $path . ($qs === '?' ? '' : $qs);
}

function asset(string $path): string
{
    $file = PUBLIC_PATH . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? substr((string) filemtime($file), -6) : '1';
    return url('/assets/' . ltrim($path, '/')) . '?v=' . $v;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function slugify(string $text): string
{
    $text = transliterate($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', strtolower($text)) ?? '';
    return trim($text, '-') ?: 'contenido';
}

function transliterate(string $text): string
{
    if (class_exists(Transliterator::class)) {
        $t = Transliterator::create('Any-Latin; Latin-ASCII');
        if ($t) {
            return (string) $t->transliterate($text);
        }
    }
    return strtr($text, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
}

/** Texto normalizado (minúsculas, sin tildes) para búsquedas que no distingan acentos. */
function search_normalize(string $text): string
{
    $text = strtolower(transliterate($text));
    return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]+/', ' ', $text) ?? '') ?? '');
}

/** Escapa los comodines de LIKE en texto del usuario (se usa con ESCAPE '!'). */
function like_escape(string $text): string
{
    return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $text);
}

function str_limit(?string $text, int $limit = 140): string
{
    $text = trim(strip_tags((string) $text));
    return mb_strlen($text) > $limit ? rtrim(mb_substr($text, 0, $limit - 1)) . '…' : $text;
}

function redirect(string $to, int $status = 302): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

function back(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = parse_url($ref, PHP_URL_HOST);
    redirect($ref !== '' && $host === ($_SERVER['HTTP_HOST'] ?? null) ? $ref : url($fallback));
}

function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function abort(int $status = 404, string $message = ''): never
{
    throw new App\Core\HttpException($status, $message);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash']['messages'][] = ['type' => $type, 'text' => $message];
}

function old(string $key, mixed $default = ''): mixed
{
    $old = Session::peekFlash('old') ?? [];
    return $old[$key] ?? $default;
}

function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="' . e(url('/assets/img/icons.svg')) . '#' . e($name) . '"></use></svg>';
}

const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

function fecha(?string $datetime, bool $withYear = true): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '';
    }
    return date('j', $ts) . ' de ' . MESES[(int) date('n', $ts) - 1] . ($withYear ? ' de ' . date('Y', $ts) : '');
}

function fecha_hora(?string $datetime): string
{
    return $datetime ? fecha($datetime) . ', ' . date('g:i a', (int) strtotime($datetime)) : '';
}

function reading_time(?string $text): int
{
    return max(1, (int) ceil(str_word_count(transliterate((string) $text)) / 200));
}

function human_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $n = (float) $bytes;
    while ($n >= 1024 && $i < count($units) - 1) {
        $n /= 1024;
        $i++;
    }
    return round($n, $i ? 1 : 0) . ' ' . $units[$i];
}

/** URL pública de un archivo multimedia en el tamaño solicitado (thumb, medium, large, original). */
function media_url(?array $media, string $size = 'large'): string
{
    if (!$media || empty($media['path'])) {
        return asset('img/placeholder.svg');
    }
    if (str_starts_with((string) $media['path'], 'http')) {
        return (string) $media['path'];
    }
    $variants = is_array($media['variants'] ?? null) ? $media['variants'] : json_decode((string) ($media['variants'] ?? ''), true);
    $path = $variants[$size] ?? $media['path'];
    return url('/uploads/' . ltrim((string) $path, '/'));
}

function media_srcset(?array $media): string
{
    if (!$media) {
        return '';
    }
    $variants = is_array($media['variants'] ?? null) ? $media['variants'] : json_decode((string) ($media['variants'] ?? ''), true);
    $widths = ['thumb' => 480, 'medium' => 1024, 'large' => 1920];
    $parts = [];
    foreach ($widths as $key => $w) {
        if (!empty($variants[$key])) {
            $parts[] = url('/uploads/' . $variants[$key]) . ' ' . $w . 'w';
        }
    }
    return implode(', ', $parts);
}

/** Etiqueta <img> con srcset, carga diferida y texto alternativo. */
function img(?array $media, string $size = 'medium', string $class = '', string $sizes = '100vw', bool $lazy = true, string $altFallback = ''): string
{
    $alt = $media['alt_text'] ?? '' ?: ($media['title'] ?? '' ?: $altFallback);
    $srcset = media_srcset($media);
    $attrs = [
        'src' => media_url($media, $size),
        'alt' => $alt,
        'class' => $class,
        'loading' => $lazy ? 'lazy' : 'eager',
        'decoding' => 'async',
    ];
    if ($srcset !== '') {
        $attrs['srcset'] = $srcset;
        $attrs['sizes'] = $sizes;
    }
    if (!empty($media['width']) && !empty($media['height'])) {
        $attrs['width'] = (string) $media['width'];
        $attrs['height'] = (string) $media['height'];
    }
    if (!$lazy) {
        $attrs['fetchpriority'] = 'high';
    }
    $html = '<img';
    foreach ($attrs as $k => $v) {
        if ($v !== '') {
            $html .= ' ' . $k . '="' . e($v) . '"';
        }
    }
    return $html . '>';
}

function status_label(string $status): string
{
    return ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado', 'scheduled' => 'Programado'][$status] ?? $status;
}

function setting(string $key, mixed $default = null): mixed
{
    return App\Models\Setting::get($key, $default);
}

function md(?string $text): string
{
    return App\Core\Markdown::render((string) $text);
}
