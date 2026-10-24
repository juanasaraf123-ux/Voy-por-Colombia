<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $prefix = rtrim((string) env('APP_BASE_PATH', ''), '/');
        if ($prefix !== '' && str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix)) ?: '/';
        }
        $path = rawurldecode($path);
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' && filter_var(env('TRUST_PROXY', false), FILTER_VALIDATE_BOOL));
    }

    public static function ip(): string
    {
        if (filter_var(env('TRUST_PROXY', false), FILTER_VALIDATE_BOOL) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public static function wantsJson(): bool
    {
        $path = self::path();
        return str_starts_with($path, '/api/') || str_starts_with($path, '/admin/api/')
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /** Texto recortado de la petición (GET o POST). */
    public static function str(string $key, string $default = '', int $max = 10000): string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (!is_string($value) && !is_numeric($value)) {
            return $default;
        }
        return mb_substr(trim(str_replace("\0", '', (string) $value)), 0, $max);
    }

    public static function int(string $key, ?int $default = null): ?int
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        if ($value === null || $value === '') {
            return $default;
        }
        $int = filter_var($value, FILTER_VALIDATE_INT);
        return $int === false ? $default : $int;
    }

    public static function float(string $key): ?float
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        $f = filter_var(str_replace(',', '.', (string) $value), FILTER_VALIDATE_FLOAT);
        return $f === false ? null : $f;
    }

    public static function bool(string $key): bool
    {
        return filter_var($_POST[$key] ?? $_GET[$key] ?? false, FILTER_VALIDATE_BOOL);
    }

    /** Lista de enteros (por ejemplo, categorías seleccionadas). */
    public static function ints(string $key): array
    {
        $raw = $_POST[$key] ?? $_GET[$key] ?? [];
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $v) {
            $i = filter_var($v, FILTER_VALIDATE_INT);
            if ($i !== false && $i > 0) {
                $out[] = $i;
            }
        }
        return array_values(array_unique($out));
    }
}
