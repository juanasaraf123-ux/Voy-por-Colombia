<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function loginForm(Request $request)
    {
        if ($this->setupAllowed($request)) {
            return redirect()->route('setup');
        }
        return Inertia::render('Admin/Login', ['noUsers' => !User::exists()]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['login' => 'required|string|max:190', 'password' => 'required|string|max:200']);
        $login = mb_strtolower(trim($data['login']));
        $user = User::where('email', $login)->orWhere('username', $login)->first();
        // Se verifica un hash ficticio si la cuenta no existe, para no revelar cuentas por el tiempo de respuesta.
        $valid = Hash::check($data['password'], $user->password ?? '$2y$12$' . str_repeat('a', 53));
        if (!$user || !$valid) {
            throw ValidationException::withMessages(['login' => 'Correo, usuario o contraseña incorrectos.']);
        }
        if (!$user->is_active) {
            throw ValidationException::withMessages(['login' => 'Esta cuenta está desactivada. Contacta a un administrador.']);
        }
        Auth::login($user, false);
        $request->session()->regenerate();
        RateLimiter::clear('login:' . $login . '|' . $request->ip());
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        AuditLog::record('login', 'user', $user->id, 'Inicio de sesión');
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            AuditLog::record('logout', 'user', $request->user()->id, 'Cierre de sesión');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Sesión cerrada.');
    }

    /**
     * La primera cuenta se puede crear desde el navegador solo mientras no exista ninguna y:
     * - se abre desde el mismo computador (localhost, por ejemplo XAMPP), o
     * - se envía el SETUP_TOKEN configurado en .env (hosting sin consola).
     */
    private function setupAllowed(Request $request): bool
    {
        if (User::exists()) {
            return false;
        }
        $local = in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)
            && !$request->headers->has('X-Forwarded-For') && !$request->headers->has('X-Real-Ip') && !$request->headers->has('Forwarded');
        $token = (string) config('vpc.setup_token');
        $sent = (string) ($request->query('token') ?? $request->input('setup_token') ?? '');
        return $local || (strlen($token) >= 24 && hash_equals($token, $sent));
    }

    public function setupForm(Request $request)
    {
        abort_unless($this->setupAllowed($request), 404);
        return Inertia::render('Admin/Setup', ['token' => (string) $request->query('token', '')]);
    }

    public function setup(Request $request)
    {
        abort_unless($this->setupAllowed($request), 404);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'username' => 'required|alpha_dash|min:3|max:60',
            'password' => array_merge(User::passwordRules(), ['confirmed']),
        ], self::passwordMessages());
        $user = User::create([
            'name' => $data['name'], 'email' => mb_strtolower($data['email']), 'username' => mb_strtolower($data['username']),
            'password' => $data['password'], 'role' => 'admin', 'is_active' => true,
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        AuditLog::record('create', 'user', $user->id, 'Primera cuenta de administración');
        return redirect()->route('admin.dashboard')->with('success', 'Cuenta creada. Ya puedes gestionar el contenido.');
    }

    public static function passwordMessages(): array
    {
        return [
            'password.min' => 'La contraseña debe tener al menos 10 caracteres.',
            'password.regex' => 'La contraseña debe combinar letras y números.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
