<?php
declare(strict_types=1);
/**
 * Crea una cuenta administrativa de forma segura (la contraseña nunca queda en el código ni en el historial).
 *   php bin/create-admin.php
 *   php bin/create-admin.php --name="Nombre" --email=correo@dominio.com --username=usuario [--role=editor]
 * La contraseña se pide de forma oculta. Para automatizar, puede pasarse en la variable de entorno VPC_ADMIN_PASSWORD.
 */
require __DIR__ . '/../app/bootstrap.php';

use App\Models\User;

$opts = getopt('', ['name:', 'email:', 'username:', 'role:']);
$ask = static function (string $label, ?string $value): string {
    if ($value !== null && $value !== '') {
        return trim($value);
    }
    echo $label . ': ';
    return trim((string) fgets(STDIN));
};
$askHidden = static function (string $label): string {
    echo $label . ': ';
    $tty = DIRECTORY_SEPARATOR === '/' && function_exists('posix_isatty') && posix_isatty(STDIN);
    if ($tty) {
        shell_exec('stty -echo');
    }
    $value = rtrim((string) fgets(STDIN), "\r\n");
    if ($tty) {
        shell_exec('stty echo');
    }
    echo PHP_EOL;
    return $value;
};

$role = $opts['role'] ?? 'admin';
if (!in_array($role, ['admin', 'editor'], true)) {
    fwrite(STDERR, "Rol inválido. Usa admin o editor.\n");
    exit(1);
}
$data = [
    'name' => $ask('Nombre completo', $opts['name'] ?? null),
    'email' => mb_strtolower($ask('Correo electrónico', $opts['email'] ?? null)),
    'username' => mb_strtolower($ask('Nombre de usuario', $opts['username'] ?? null)),
];
$password = getenv('VPC_ADMIN_PASSWORD') ?: $askHidden('Contraseña (mínimo 10 caracteres, letras y números)');
$confirm = getenv('VPC_ADMIN_PASSWORD') ?: $askHidden('Repite la contraseña');
$data['password'] = $password;
$data['password_confirmation'] = $confirm;

$errors = User::validate($data, null, true);
if ($errors) {
    foreach ($errors as $e) {
        fwrite(STDERR, "✗ $e\n");
    }
    exit(1);
}
$id = User::create($data['name'], $data['email'], $data['username'], $password, $role);
App\Core\Audit::log('create', 'user', $id, 'Cuenta creada desde la consola: ' . $data['username']);
echo "✓ Cuenta '{$data['username']}' creada con rol $role. Ingresa en /admin/login\n";
