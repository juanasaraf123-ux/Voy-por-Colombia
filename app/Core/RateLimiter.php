<?php
declare(strict_types=1);

namespace App\Core;

/** Limita los intentos de inicio de sesión por identificador y por IP. */
final class RateLimiter
{
    public static function tooMany(string $login, string $ip): bool
    {
        $since = date('Y-m-d H:i:s', time() - Auth::LOCK_MINUTES * 60);
        $byLogin = (int) Database::value(
            'SELECT COUNT(*) FROM login_attempts WHERE identifier = ? AND success = 0 AND created_at >= ?',
            [$login, $since]
        );
        $byIp = (int) Database::value(
            'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND created_at >= ?',
            [$ip, $since]
        );
        return $byLogin >= Auth::MAX_ATTEMPTS || $byIp >= Auth::MAX_ATTEMPTS * 4;
    }

    public static function hit(string $login, string $ip, bool $success): void
    {
        Database::insert('login_attempts', [
            'identifier' => mb_substr($login, 0, 190),
            'ip' => mb_substr($ip, 0, 45),
            'success' => $success ? 1 : 0,
            'created_at' => now(),
        ]);
        if ($success) {
            Database::run('DELETE FROM login_attempts WHERE identifier = ? AND success = 0', [$login]);
        }
        if (random_int(1, 50) === 1) {
            Database::run('DELETE FROM login_attempts WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 86400 * 7)]);
        }
    }
}
