<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\User;

/** Inicio y cierre de sesión, e instalador web opcional para crear la primera cuenta. */
final class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect(url('/admin'));
        }
        header('X-Robots-Tag: noindex, nofollow');
        View::render('admin/login', ['pageTitle' => 'Ingresar al panel', 'noUsers' => !DB::value('SELECT COUNT(*) FROM users')], 'layouts/admin-auth');
    }

    public function login(): void
    {
        $login = Request::str('login', '', 190);
        $password = (string) ($_POST['password'] ?? '');
        if ($login === '' || $password === '') {
            Session::flash('old', ['login' => $login]);
            flash('error', 'Escribe tu correo o usuario y tu contraseña.');
            redirect(url('/admin/login'));
        }
        if ($error = Auth::attempt($login, $password)) {
            Session::flash('old', ['login' => $login]);
            flash('error', $error);
            redirect(url('/admin/login'));
        }
        $intended = (string) Session::get('intended', '/admin');
        Session::forget('intended');
        // Solo se permite volver a rutas internas del panel.
        redirect(url(str_starts_with($intended, '/admin') ? $intended : '/admin'));
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        flash('success', 'Cerraste sesión correctamente.');
        redirect(url('/admin/login'));
    }

    private function setupAllowed(): bool
    {
        $token = (string) env('SETUP_TOKEN', '');
        $sent = (string) ($_GET['token'] ?? $_POST['setup_token'] ?? '');
        return strlen($token) >= 24 && hash_equals($token, $sent) && !DB::value('SELECT COUNT(*) FROM users');
    }

    public function setupForm(): void
    {
        if (!$this->setupAllowed()) {
            throw new HttpException(404);
        }
        View::render('admin/setup', ['pageTitle' => 'Crear la primera cuenta', 'token' => (string) $_GET['token']], 'layouts/admin-auth');
    }

    public function setup(): void
    {
        if (!$this->setupAllowed()) {
            throw new HttpException(404);
        }
        $data = [
            'name' => Request::str('name', '', 120),
            'email' => mb_strtolower(Request::str('email', '', 190)),
            'username' => mb_strtolower(Request::str('username', '', 60)),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];
        $errors = User::validate($data, null, true);
        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', ['name' => $data['name'], 'email' => $data['email'], 'username' => $data['username']]);
            redirect(url('/admin/instalar', ['token' => $_POST['setup_token']]));
        }
        $id = User::create($data['name'], $data['email'], $data['username'], $data['password'], 'admin');
        Audit::log('create', 'user', $id, 'Primera cuenta creada con el instalador web');
        flash('success', 'Cuenta creada. Ya puedes ingresar. Recuerda borrar SETUP_TOKEN del archivo .env.');
        redirect(url('/admin/login'));
    }
}
