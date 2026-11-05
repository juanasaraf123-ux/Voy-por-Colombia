<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Permisos verificados en el servidor en cada acción administrativa (no basta con ocultar botones).
        $all = array_unique(array_merge(...array_values(User::PERMISSIONS)));
        foreach (array_merge($all, ['users.manage', 'settings.manage', 'audit.view']) as $permission) {
            if ($permission === '*') {
                continue;
            }
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        // Inicio de sesión: 5 intentos por cuenta y 20 por dirección IP cada 15 minutos.
        RateLimiter::for('login', function (Request $request) {
            $login = mb_strtolower((string) $request->input('login'));
            return [
                Limit::perMinutes(15, 5)->by('login:' . $login . '|' . $request->ip()),
                Limit::perMinutes(15, 20)->by('login-ip:' . $request->ip()),
            ];
        });
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(240)->by($request->ip()));
    }
}
