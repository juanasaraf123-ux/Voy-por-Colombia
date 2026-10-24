<?php
declare(strict_types=1);

namespace App\Core;

/** Protección contra falsificación de peticiones (CSRF) mediante token de sesión. */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf', $token);
        }
        return $token;
    }

    public static function verify(): void
    {
        $sent = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($sent) || !hash_equals(self::token(), $sent)) {
            throw new HttpException(419, 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.');
        }
    }
}
