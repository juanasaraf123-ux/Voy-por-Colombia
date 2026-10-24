<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database as DB;

final class User
{
    public static function all(): array
    {
        return DB::all('SELECT u.id, u.name, u.email, u.username, u.is_active, u.last_login_at, u.created_at, r.slug AS role, r.name AS role_name
            FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.name');
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT u.*, r.slug AS role, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?', [$id]);
    }

    public static function roles(): array
    {
        return DB::all('SELECT * FROM roles ORDER BY id');
    }

    public static function roleId(string $slug): ?int
    {
        $id = DB::value('SELECT id FROM roles WHERE slug = ?', [$slug]);
        return $id ? (int) $id : null;
    }

    public static function activeAdmins(): int
    {
        return (int) DB::value("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'admin' AND u.is_active = 1");
    }

    /** Valida los datos de una cuenta. Devuelve errores por campo. */
    public static function validate(array $d, ?int $ignoreId, bool $passwordRequired): array
    {
        $errors = [];
        if (mb_strlen($d['name']) < 2) {
            $errors['name'] = 'Escribe el nombre completo.';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Escribe un correo válido.';
        } elseif (DB::value('SELECT id FROM users WHERE email = ? AND id <> ?', [$d['email'], $ignoreId ?? 0])) {
            $errors['email'] = 'Ya existe una cuenta con este correo.';
        }
        if (!preg_match('/^[a-z0-9._-]{3,40}$/', $d['username'])) {
            $errors['username'] = 'Usa entre 3 y 40 caracteres: letras minúsculas, números, punto, guion o guion bajo.';
        } elseif (DB::value('SELECT id FROM users WHERE username = ? AND id <> ?', [$d['username'], $ignoreId ?? 0])) {
            $errors['username'] = 'Este nombre de usuario ya está en uso.';
        }
        if ($passwordRequired || $d['password'] !== '') {
            if ($problem = Auth::passwordProblem($d['password'])) {
                $errors['password'] = $problem;
            } elseif ($d['password'] !== ($d['password_confirmation'] ?? null)) {
                $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
            }
        }
        return $errors;
    }

    public static function create(string $name, string $email, string $username, string $password, string $role): int
    {
        $roleId = self::roleId($role) ?? throw new \InvalidArgumentException('Rol inválido');
        return DB::insert('users', [
            'role_id' => $roleId,
            'name' => $name,
            'email' => mb_strtolower($email),
            'username' => mb_strtolower($username),
            'password_hash' => Auth::hash($password),
            'is_active' => 1,
            'session_version' => bin2hex(random_bytes(8)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
