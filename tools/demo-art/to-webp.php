<?php
// Convierte los PNG renderizados a WebP para database/seeds/demo-media.
// Uso: php tools/demo-art/to-webp.php <carpeta_png> <carpeta_destino>
[$_, $src, $dst] = $argv + [null, null, null];
if (!$src || !$dst) {
    fwrite(STDERR, "Uso: php to-webp.php <carpeta_png> <carpeta_destino>\n");
    exit(1);
}
@mkdir($dst, 0755, true);
foreach (glob(rtrim($src, '/') . '/*.png') as $png) {
    $im = imagecreatefrompng($png);
    $w = imagesx($im);
    // Se limita a 1600 px de ancho para mantener liviano el repositorio.
    if ($w > 1600) {
        $im = imagescale($im, 1600);
    }
    imagewebp($im, $dst . '/' . basename($png, '.png') . '.webp', 82);
    echo basename($png), PHP_EOL;
}
