<?php
declare(strict_types=1);

namespace App\Core;

/** Plantillas PHP nativas con layout. Toda salida dinámica debe pasar por e(). */
final class View
{
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $template, array $data = [], ?string $layout = 'layouts/public'): void
    {
        self::securityHeaders();
        $content = self::capture($template, $data);
        echo $layout ? self::capture($layout, $data + ['content' => $content]) : $content;
    }

    public static function capture(string $template, array $data = []): string
    {
        $file = APP_PATH . '/Views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Plantilla no encontrada: $template");
        }
        extract(self::$shared + $data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    public static function partial(string $name, array $data = []): string
    {
        return self::capture('partials/' . $name, $data);
    }

    public static function securityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(self)');
        $tiles = parse_url((string) env('MAP_TILE_URL', ''), PHP_URL_HOST);
        $tileSrc = $tiles ? ' https://' . str_replace('{s}', '*', $tiles) : '';
        $tileSrc = str_replace('{s}.', '*.', $tileSrc);
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob: https://i.ytimg.com https://*.vimeocdn.com$tileSrc; media-src 'self' blob:; "
            . "frame-src https://www.youtube-nocookie.com https://player.vimeo.com; script-src 'self'; style-src 'self' 'unsafe-inline'; font-src 'self'; "
            . "connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
    }

    public static function errorPage(int $status, string $title, string $message, ?string $debug = null): void
    {
        if (!headers_sent()) {
            http_response_code($status);
        }
        try {
            $layout = str_starts_with(Request::path(), '/admin') && Auth::check() ? 'layouts/admin' : 'layouts/public';
            self::render('public/error', compact('status', 'title', 'message', 'debug') + ['pageTitle' => $title], $layout);
        } catch (\Throwable $e) {
            echo '<!doctype html><meta charset="utf-8"><title>' . e($title) . '</title><h1>' . e($title) . '</h1><p>' . e($message) . '</p>';
            if ($debug) {
                echo '<pre>' . e($debug) . "\n\n" . e((string) $e) . '</pre>';
            }
        }
    }
}
