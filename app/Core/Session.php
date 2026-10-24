<?php
declare(strict_types=1);

namespace App\Core;

/** Sesión PHP con cookies seguras y mensajes flash. */
final class Session
{
    private static bool $started = false;
    private static array $flashNow = [];

    public static function start(): void
    {
        if (self::$started || PHP_SAPI === 'cli') {
            self::$started = true;
            return;
        }
        $secure = filter_var(env('SESSION_SECURE', Request::isHttps()), FILTER_VALIDATE_BOOL);
        session_name('vpc_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => url('/'),
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) (60 * (int) env('SESSION_LIFETIME_MINUTES', 120)));
        $dir = STORAGE_PATH . '/sessions';
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }
        session_start();
        self::$started = true;
        self::$flashNow = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
            session_destroy();
        }
        self::$started = false;
    }

    /** Guarda un valor para la siguiente petición. */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /** Lee un valor flash (de la petición anterior o recién agregado). */
    public static function peekFlash(string $key): mixed
    {
        return $_SESSION['_flash'][$key] ?? self::$flashNow[$key] ?? null;
    }

    /** Mensajes que deben mostrarse ahora. */
    public static function takeMessages(): array
    {
        $messages = self::$flashNow['messages'] ?? [];
        self::$flashNow['messages'] = [];
        return $messages;
    }

    public static function errors(): array
    {
        return self::$flashNow['errors'] ?? [];
    }
}
