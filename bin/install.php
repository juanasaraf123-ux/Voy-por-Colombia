<?php
declare(strict_types=1);
/**
 * Instala o actualiza la base de datos.
 *   php bin/install.php          -> migraciones + datos base (departamentos, municipios, categorías…)
 *   php bin/install.php --demo   -> además carga el contenido de demostración
 */
require __DIR__ . '/../app/bootstrap.php';
require BASE_PATH . '/database/seeds/BaseSeeder.php';

$log = static fn (string $m) => print("  · $m\n");
echo "Base de datos: " . App\Core\Database::driver() . "\n";
$applied = App\Core\Migrator::run($log);
if (!$applied) {
    $log('No hay migraciones pendientes.');
}
BaseSeeder::run($log);
if (in_array('--demo', $argv, true)) {
    require BASE_PATH . '/database/seeds/DemoSeeder.php';
    DemoSeeder::run($log);
}
echo "Listo.\n";
if (!App\Core\Database::value('SELECT COUNT(*) FROM users')) {
    echo "\nAún no hay cuentas administrativas. Crea la primera con:\n  php bin/create-admin.php\n";
}
