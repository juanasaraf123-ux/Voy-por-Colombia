<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Autenticación y autorización del lado del servidor.
 * Los permisos se verifican en cada acción administrativa, no solo ocultando botones.
 */
final class Auth
{
    /** Permisos por rol. "admin" tiene acceso total. */
    public const PERMISSIONS = [
        'admin' => ['*'],
        'editor' => [
            'content.manage', 'content.publish', 'content.delete',
            'media.manage', 'map.manage', 'home.manage', 'routes.manage', 'categories.manage',
        ],
    ];

    public const MAX_ATTEMPTS = 5;
    public const LOCK_MINUTES = 15;

    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            if (is_int($id)) {
                $user = Database::one(
                    'SELECT u.*, r.slug AS role, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
                    [$id]
                );
                // Si la cuenta fue desactivada o cambió su contraseña, la sesión deja de ser válida.
                if ($user && (int) $user['is_active'] === 1 && hash_equals((string) $user['session_version'], (string) Session::get('session_version'))) {
                    self::$user = $user;
                    $timeout = 60 * (int) env('SESSION_LIFETIME_MINUTES', 120);
                    $last = (int) Session::get('last_activity', time());
                    if (time() - $last > $timeout) {
                        self::logout();
                        return null;
                    }
                    Session::put('last_activity', time());
                } else {
                    Session::forget('user_id');
                }
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $perms = self::PERMISSIONS[$user['role']] ?? [];
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    /** Exige sesión iniciada y, opcionalmente, un permiso. */
    public static function require(?string $permission = null): array
    {
        $user = self::user();
        if (!$user) {
            if (Request::wantsJson()) {
                throw new HttpException(401, 'Debes iniciar sesión.');
            }
            Session::put('intended', Request::path());
            redirect(url('/admin/login'));
        }
        if ($permission !== null && !self::can($permission)) {
            throw new HttpException(403, 'No tienes permiso para realizar esta acción.');
        }
        return $user;
    }

    /**
     * Intenta iniciar sesión con correo o nombre de usuario.
     * Devuelve null si tuvo éxito o un mensaje de error.
     */
    public static function attempt(string $login, string $password): ?string
    {
        $login = mb_strtolower(trim($login));
        $ip = Request::ip();
        if (RateLimiter::tooMany($login, $ip)) {
            return 'Demasiados intentos fallidos. Espera ' . self::LOCK_MINUTES . ' minutos antes de intentarlo de nuevo.';
        }
        $user = Database::one('SELECT * FROM users WHERE email = ? OR username = ?', [$login, $login]);
        // Se verifica un hash ficticio cuando el usuario no existe para no revelar cuentas por tiempo de respuesta.
        $hash = $user['password_hash'] ?? self::hash(bin2hex(random_bytes(8)));
        $valid = password_verify($password, $hash);
        RateLimiter::hit($login, $ip, $valid && $user && (int) $user['is_active'] === 1);
        if (!$valid || !$user) {
            return 'Correo, usuario o contraseña incorrectos.';
        }
        if ((int) $user['is_active'] !== 1) {
            return 'Esta cuenta está desactivada. Contacta a un administrador.';
        }
        if (password_needs_rehash($hash, self::algo())) {
            Database::update('users', ['password_hash' => self::hash($password)], 'id = :uid', ['uid' => $user['id']]);
        }
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
        Session::put('session_version', (string) $user['session_version']);
        Session::put('last_activity', time());
        Session::forget('_csrf');
        Database::update('users', ['last_login_at' => now()], 'id = :uid', ['uid' => $user['id']]);
        self::$loaded = false;
        Audit::log('login', 'user', (int) $user['id'], 'Inicio de sesión');
        return null;
    }

    public static function logout(): void
    {
        if (self::$user) {
            Audit::log('logout', 'user', (int) self::$user['id'], 'Cierre de sesión');
        }
        self::$user = null;
        self::$loaded = true;
        Session::destroy();
    }

    public static function algo(): string|int
    {
        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, self::algo());
    }

    /** Reglas mínimas de contraseña. Devuelve un mensaje de error o null. */
    public static function passwordProblem(string $password): ?string
    {
        if (mb_strlen($password) < 10) {
            return 'La contraseña debe tener al menos 10 caracteres.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'La contraseña debe combinar letras y números.';
        }
        if (mb_strlen($password) > 200) {
            return 'La contraseña es demasiado larga.';
        }
        return null;
    }
}
