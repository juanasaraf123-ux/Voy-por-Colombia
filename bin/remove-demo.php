<?php
declare(strict_types=1);
/** Elimina todo el contenido marcado como demostración (publicaciones, rutas y archivos). */
require __DIR__ . '/../app/bootstrap.php';
$counts = App\Models\Demo::remove();
echo "Eliminado: {$counts['entries']} publicaciones, {$counts['routes']} rutas y {$counts['media']} archivos de demostración.\n";
