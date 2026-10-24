<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Session;
use App\Core\View;

/** Base de los controladores del panel: autorización, vistas, errores de validación y respuestas. */
abstract class AdminController
{
    protected array $user;

    public function __construct()
    {
        $this->user = Auth::require($this->permission());
        header('Cache-Control: no-store, private');
        header('X-Robots-Tag: noindex, nofollow');
    }

    /** Permiso mínimo para usar el controlador (null = cualquier cuenta activa). */
    protected function permission(): ?string
    {
        return null;
    }

    protected function render(string $template, array $data = []): void
    {
        View::render('admin/' . $template, $data + ['user' => $this->user], 'layouts/admin');
    }

    /** Devuelve al formulario con los errores y los datos enviados. */
    protected function fail(array $errors, string $to): never
    {
        Session::flash('errors', $errors);
        $old = $_POST;
        unset($old['_token'], $old['password'], $old['password_confirmation'], $old['current_password']);
        Session::flash('old', $old);
        flash('error', 'Revisa los campos marcados.');
        redirect($to);
    }

    protected function require(string $permission): void
    {
        if (!Auth::can($permission)) {
            throw new \App\Core\HttpException(403, 'No tienes permiso para realizar esta acción.');
        }
    }

    protected function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }
}
