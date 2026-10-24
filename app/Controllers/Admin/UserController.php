<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

/**
 * Cuentas administrativas. Solo los administradores gestionan usuarios;
 * cualquier cuenta puede editar su propio perfil y contraseña.
 */
final class UserController extends AdminController
{
    private function adminOnly(): void
    {
        $this->require('users.manage');
    }

    private function input(): array
    {
        return [
            'name' => Request::str('name', '', 120),
            'email' => mb_strtolower(Request::str('email', '', 190)),
            'username' => mb_strtolower(Request::str('username', '', 60)),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];
    }

    public function index(): void
    {
        $this->adminOnly();
        $this->render('users/index', ['pageTitle' => 'Usuarios', 'users' => User::all()]);
    }

    public function create(): void
    {
        $this->adminOnly();
        $this->render('users/form', ['pageTitle' => 'Nueva cuenta', 'u' => null, 'roles' => User::roles()]);
    }

    public function store(): void
    {
        $this->adminOnly();
        $d = $this->input();
        $role = Request::str('role');
        $errors = User::validate($d, null, true);
        if (!User::roleId($role)) {
            $errors['role'] = 'Selecciona un rol.';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/usuarios/nuevo'));
        }
        $id = User::create($d['name'], $d['email'], $d['username'], $d['password'], $role);
        Audit::log('create', 'user', $id, 'Cuenta: ' . $d['username'] . ' (' . $role . ')');
        flash('success', 'Cuenta creada. Comparte la contraseña por un canal seguro y pide cambiarla al ingresar.');
        redirect(url('/admin/usuarios'));
    }

    public function edit(string $id): void
    {
        $this->adminOnly();
        $u = User::find((int) $id) ?? throw new HttpException(404);
        $this->render('users/form', ['pageTitle' => 'Editar cuenta', 'u' => $u, 'roles' => User::roles()]);
    }

    public function update(string $id): void
    {
        $this->adminOnly();
        $u = User::find((int) $id) ?? throw new HttpException(404);
        $d = $this->input();
        $role = Request::str('role');
        $errors = User::validate($d, (int) $id, false);
        $roleId = User::roleId($role);
        if (!$roleId) {
            $errors['role'] = 'Selecciona un rol.';
        } elseif ($u['role'] === 'admin' && $role !== 'admin' && User::activeAdmins() <= 1) {
            $errors['role'] = 'Debe existir al menos un administrador activo.';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/usuarios/' . $id));
        }
        $data = ['name' => $d['name'], 'email' => $d['email'], 'username' => $d['username'], 'role_id' => $roleId, 'updated_at' => now()];
        if ($d['password'] !== '' || $role !== $u['role']) {
            // Cambiar contraseña o rol cierra las sesiones abiertas de esa cuenta.
            $data['session_version'] = bin2hex(random_bytes(8));
        }
        if ($d['password'] !== '') {
            $data['password_hash'] = Auth::hash($d['password']);
        }
        DB::update('users', $data, 'id = :uid', ['uid' => (int) $id]);
        if ((int) $id === (int) $this->user['id'] && isset($data['session_version'])) {
            Session::put('session_version', $data['session_version']);
        }
        Audit::log('update', 'user', (int) $id, 'Cuenta: ' . $d['username'] . ($d['password'] !== '' ? ' (contraseña restablecida)' : ''));
        flash('success', 'Cuenta actualizada.');
        redirect(url('/admin/usuarios'));
    }

    /** Activa o desactiva una cuenta. */
    public function toggle(string $id): void
    {
        $this->adminOnly();
        $u = User::find((int) $id) ?? throw new HttpException(404);
        if ((int) $id === (int) $this->user['id']) {
            flash('error', 'No puedes desactivar tu propia cuenta.');
            redirect(url('/admin/usuarios'));
        }
        $active = (int) $u['is_active'] === 1;
        if ($active && $u['role'] === 'admin' && User::activeAdmins() <= 1) {
            flash('error', 'Debe existir al menos un administrador activo.');
            redirect(url('/admin/usuarios'));
        }
        DB::update('users', ['is_active' => $active ? 0 : 1, 'session_version' => bin2hex(random_bytes(8)), 'updated_at' => now()], 'id = :uid', ['uid' => (int) $id]);
        Audit::log($active ? 'deactivate' : 'activate', 'user', (int) $id, 'Cuenta: ' . $u['username']);
        flash('success', $active ? 'Cuenta desactivada: ya no puede ingresar.' : 'Cuenta activada.');
        redirect(url('/admin/usuarios'));
    }

    public function profile(): void
    {
        $this->render('profile', ['pageTitle' => 'Mi perfil']);
    }

    public function updateProfile(): void
    {
        $d = $this->input();
        $d['username'] = $this->user['username'];
        $errors = User::validate($d, (int) $this->user['id'], false);
        if ($d['password'] !== '' && !password_verify((string) ($_POST['current_password'] ?? ''), $this->user['password_hash'])) {
            $errors['current_password'] = 'La contraseña actual no es correcta.';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/perfil'));
        }
        $data = ['name' => $d['name'], 'email' => $d['email'], 'updated_at' => now()];
        if ($d['password'] !== '') {
            $data['password_hash'] = Auth::hash($d['password']);
            $data['session_version'] = bin2hex(random_bytes(8));
        }
        DB::update('users', $data, 'id = :uid', ['uid' => (int) $this->user['id']]);
        if (isset($data['session_version'])) {
            Session::regenerate();
            Session::put('session_version', $data['session_version']);
        }
        Audit::log('update', 'user', (int) $this->user['id'], 'Actualizó su perfil' . ($d['password'] !== '' ? ' y contraseña' : ''));
        flash('success', 'Perfil actualizado.');
        redirect(url('/admin/perfil'));
    }
}
