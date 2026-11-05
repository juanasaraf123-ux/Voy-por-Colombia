<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = ['admin' => 'Administrador', 'editor' => 'Editor'];

    /** Permisos por rol. El administrador tiene acceso total. Se comprueban en el servidor (Gate) en cada acción. */
    public const PERMISSIONS = [
        'admin' => ['*'],
        'editor' => [
            'content.manage', 'content.publish', 'content.delete',
            'media.manage', 'map.manage', 'home.manage', 'routes.manage', 'categories.manage',
        ],
    ];

    protected $fillable = ['name', 'email', 'username', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $perms = self::PERMISSIONS[$this->role] ?? [];
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Reglas mínimas de contraseña (se usan al crear y cambiar contraseñas). */
    public static function passwordRules(): array
    {
        return ['required', 'string', 'min:10', 'max:200', 'regex:/[A-Za-z]/', 'regex:/\d/'];
    }
}
