<?php
declare(strict_types=1);
/**
 * Genera database/voy_por_colombia.sqlite (base lista para usar sin MySQL) copiando
 * los datos de una base MySQL donde se importó database/voy_por_colombia.sql.
 * Así ambos archivos apuntan a las mismas imágenes de public/uploads/demo y marca.
 *
 *   SRC_DB_USER=root SRC_DB_PASS= php tools/db/build_sqlite.php
 */
$target = dirname(__DIR__, 2) . '/database/voy_por_colombia.sqlite';
@unlink($target);
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=' . $target);
require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Database as DB;

App\Core\Migrator::run(static fn () => null);
$src = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('SRC_DB_HOST') ?: '127.0.0.1', getenv('SRC_DB_PORT') ?: '3306', getenv('SRC_DB_NAME') ?: 'voy_por_colombia'),
    getenv('SRC_DB_USER') ?: 'root',
    getenv('SRC_DB_PASS') ?: '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$pdo = DB::pdo();
$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->beginTransaction();
foreach ($src->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (in_array($table, ['migrations', 'users', 'login_attempts', 'audit_log'], true)) {
        continue; // sin cuentas ni registros
    }
    $pdo->exec("DELETE FROM $table");
    $n = 0;
    foreach ($src->query("SELECT * FROM `$table`") as $row) {
        DB::insert($table, $row);
        $n++;
    }
    echo str_pad($table, 18) . " $n\n";
}
$pdo->commit();
$pdo->exec('PRAGMA journal_mode = DELETE');
$pdo->exec('VACUUM');
echo 'Listo: ' . $target . ' (' . round(filesize($target) / 1024) . " KB)\n";
