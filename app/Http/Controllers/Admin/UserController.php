<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/** Cuentas administrativas (solo administradores). Cualquier cuenta edita su propio perfil. */
class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'title' => 'Usuarios',
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'username', 'role', 'is_active', 'last_login_at']),
            'roles' => User::ROLES,
            'me' => Auth::id(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Form', ['title' => 'Nueva cuenta', 'user' => null, 'roles' => User::ROLES]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Form', ['title' => 'Editar cuenta', 'user' => $user->only(['id', 'name', 'email', 'username', 'role']), 'roles' => User::ROLES]);
    }

    private function rules(?User $user, bool $passwordRequired): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'username' => ['required', 'alpha_dash', 'min:3', 'max:60', Rule::unique('users', 'username')->ignore($user?->id)],
            'role' => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'password' => $passwordRequired ? array_merge(User::passwordRules(), ['confirmed']) : array_merge(['nullable', 'confirmed'], array_slice(User::passwordRules(), 1)),
        ];
    }

    public function store(Request $request)
    {
        $v = $request->validate($this->rules(null, true), AuthController::passwordMessages());
        $user = User::create(['name' => $v['name'], 'email' => mb_strtolower($v['email']), 'username' => mb_strtolower($v['username']), 'password' => $v['password'], 'role' => $v['role'], 'is_active' => true]);
        AuditLog::record('create', 'user', $user->id, 'Cuenta: ' . $user->username . ' (' . $user->role . ')');
        return redirect('/admin/usuarios')->with('success', 'Cuenta creada. Comparte la contraseña por un canal seguro y pide cambiarla al ingresar.');
    }

    public function update(Request $request, User $user)
    {
        $v = $request->validate($this->rules($user, false), AuthController::passwordMessages());
        if ($user->role === 'admin' && $v['role'] !== 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'Debe existir al menos un administrador activo.']);
        }
        $data = ['name' => $v['name'], 'email' => mb_strtolower($v['email']), 'username' => mb_strtolower($v['username']), 'role' => $v['role']];
        if (!empty($v['password'])) {
            $data['password'] = $v['password'];
        }
        $user->update($data);
        if (!empty($v['password']) || $user->wasChanged('role')) {
            $this->closeSessions($user);
        }
        AuditLog::record('update', 'user', $user->id, 'Cuenta: ' . $user->username . (!empty($v['password']) ? ' (contraseña restablecida)' : ''));
        return redirect('/admin/usuarios')->with('success', 'Cuenta actualizada.');
    }

    /** Activa o desactiva una cuenta. Una cuenta desactivada pierde la sesión de inmediato. */
    public function toggle(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }
        if ($user->is_active && $user->role === 'admin' && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Debe existir al menos un administrador activo.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $this->closeSessions($user);
        AuditLog::record('update', 'user', $user->id, ($user->is_active ? 'Activó' : 'Desactivó') . ' la cuenta: ' . $user->username);
        return back()->with('success', $user->is_active ? 'Cuenta activada.' : 'Cuenta desactivada: ya no puede ingresar.');
    }

    /** Cierra las sesiones guardadas de otra cuenta (cambio de contraseña, de rol o desactivación). */
    private function closeSessions(User $user): void
    {
        $user->forceFill(['remember_token' => null])->saveQuietly();
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }

    public function profile(Request $request)
    {
        return Inertia::render('Admin/Profile', ['title' => 'Mi perfil', 'user' => $request->user()->only(['name', 'email', 'username', 'role'])]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $v = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password|string',
            'password' => array_merge(['nullable', 'confirmed'], array_slice(User::passwordRules(), 1)),
        ], AuthController::passwordMessages() + ['current_password.required_with' => 'Escribe tu contraseña actual para cambiarla.']);
        if (!empty($v['password']) && !Hash::check($v['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }
        $user->update(['name' => $v['name'], 'email' => mb_strtolower($v['email'])] + (!empty($v['password']) ? ['password' => $v['password']] : []));
        if (!empty($v['password'])) {
            Auth::logoutOtherDevices($v['password']);
            $request->session()->regenerate();
        }
        AuditLog::record('update', 'user', $user->id, 'Actualizó su perfil' . (!empty($v['password']) ? ' y contraseña' : ''));
        return back()->with('success', 'Perfil actualizado.');
    }
}
