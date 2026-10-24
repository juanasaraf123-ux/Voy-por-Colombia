<?php
declare(strict_types=1);
/** Aplica únicamente las migraciones pendientes. */
require __DIR__ . '/../app/bootstrap.php';
$applied = App\Core\Migrator::run(static fn ($m) => print("  · $m\n"));
echo $applied ? "Listo.\n" : "No hay migraciones pendientes.\n";
