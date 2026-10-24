<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validación y almacenamiento de archivos multimedia.
 * - Comprueba error de carga, tamaño, tipo MIME real (finfo) y extensión.
 * - Las imágenes se vuelven a codificar con GD (elimina metadatos y contenido incrustado)
 *   y se generan variantes WebP optimizadas para la web.
 * - Los archivos se guardan en public/uploads con nombres aleatorios, nunca en la base de datos.
 */
final class MediaProcessor
{
    public const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    public const VIDEO_TYPES = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];
    public const AUDIO_TYPES = ['audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/ogg' => 'ogg', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'audio/webm' => 'weba'];
    public const DOC_TYPES = ['application/pdf' => 'pdf'];

    public const VARIANTS = ['thumb' => 480, 'medium' => 1024, 'large' => 1920];
    public const MAX_ORIGINAL = 2560;
    public const MAX_PIXELS = 50_000_000;

    /** Extensiones aceptadas por tipo de MIME detectado (evita archivos con doble extensión o disfrazados). */
    private const EXT_ALIASES = ['jpg' => ['jpg', 'jpeg', 'jfif'], 'm4a' => ['m4a', 'mp4'], 'weba' => ['weba', 'webm'], 'mp3' => ['mp3']];

    public static function limits(): array
    {
        return [
            'image' => (int) env('UPLOAD_MAX_IMAGE_MB', 12) * 1024 * 1024,
            'video' => (int) env('UPLOAD_MAX_VIDEO_MB', 200) * 1024 * 1024,
            'audio' => (int) env('UPLOAD_MAX_AUDIO_MB', 40) * 1024 * 1024,
            'document' => (int) env('UPLOAD_MAX_DOC_MB', 20) * 1024 * 1024,
        ];
    }

    /** Lista de extensiones aceptadas, útil para el atributo accept del formulario. */
    public static function acceptAttribute(): string
    {
        return '.jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mp3,.ogg,.wav,.m4a,.pdf';
    }

    /**
     * Procesa un archivo de $_FILES. Devuelve los datos para la tabla media.
     * @throws \InvalidArgumentException con un mensaje comprensible para el usuario.
     */
    public static function handleUpload(array $file): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException(match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido por el servidor.',
                UPLOAD_ERR_PARTIAL => 'El archivo se cargó de forma incompleta. Inténtalo de nuevo.',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo.',
                default => 'No fue posible recibir el archivo (código ' . (int) $error . ').',
            });
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if (PHP_SAPI !== 'cli' && !is_uploaded_file($tmp)) {
            throw new \InvalidArgumentException('Archivo de carga no válido.');
        }
        return self::process($tmp, (string) ($file['name'] ?? 'archivo'), false);
    }

    /** Importa un archivo local de confianza (por ejemplo, datos de demostración). */
    public static function importLocal(string $path, string $originalName): array
    {
        return self::process($path, $originalName, true);
    }

    private static function process(string $tmp, string $originalName, bool $trusted): array
    {
        $size = (int) @filesize($tmp);
        if ($size <= 0) {
            throw new \InvalidArgumentException('El archivo está vacío.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: 'application/octet-stream';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($trusted && $mime === 'image/svg+xml' || ($trusted && $ext === 'svg')) {
            return self::store($tmp, 'svg', 'image', 'image/svg+xml', $originalName, $size, null, null);
        }

        $kind = match (true) {
            isset(self::IMAGE_TYPES[$mime]) => 'image',
            isset(self::VIDEO_TYPES[$mime]) => 'video',
            isset(self::AUDIO_TYPES[$mime]) => 'audio',
            isset(self::DOC_TYPES[$mime]) => 'document',
            default => null,
        };
        if ($kind === null) {
            throw new \InvalidArgumentException('Formato no permitido (' . $mime . '). Usa JPG, PNG, WebP, GIF, MP4, WebM, MP3, OGG, WAV, M4A o PDF.');
        }
        $expected = self::IMAGE_TYPES[$mime] ?? self::VIDEO_TYPES[$mime] ?? self::AUDIO_TYPES[$mime] ?? self::DOC_TYPES[$mime];
        $okExt = self::EXT_ALIASES[$expected] ?? [$expected];
        if (!in_array($ext, $okExt, true)) {
            throw new \InvalidArgumentException('La extensión del archivo (.' . $ext . ') no coincide con su contenido real (' . $mime . ').');
        }
        $limit = self::limits()[$kind];
        if ($size > $limit) {
            throw new \InvalidArgumentException('El archivo pesa ' . human_bytes($size) . '; el máximo para este tipo es ' . human_bytes($limit) . '.');
        }
        if ($kind === 'document' && !str_starts_with((string) file_get_contents($tmp, false, null, 0, 5), '%PDF-')) {
            throw new \InvalidArgumentException('El PDF no es válido.');
        }
        if ($kind === 'image') {
            return self::processImage($tmp, $mime, $originalName, $size);
        }
        return self::store($tmp, $expected, $kind, $mime, $originalName, $size, null, null);
    }

    private static function processImage(string $tmp, string $mime, string $originalName, int $size): array
    {
        $info = @getimagesize($tmp);
        if (!$info || $info[0] < 1 || $info[1] < 1) {
            throw new \InvalidArgumentException('La imagen está dañada o no es válida.');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new \InvalidArgumentException('La imagen es demasiado grande (máximo 50 megapíxeles).');
        }
        if (!extension_loaded('gd')) {
            // Sin GD se conserva el archivo validado tal cual.
            return self::store($tmp, self::IMAGE_TYPES[$mime], 'image', $mime, $originalName, $size, $info[0], $info[1]);
        }
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmp),
            'image/png' => @imagecreatefrompng($tmp),
            'image/webp' => @imagecreatefromwebp($tmp),
            'image/gif' => @imagecreatefromgif($tmp),
        };
        if (!$src) {
            throw new \InvalidArgumentException('No fue posible leer la imagen.');
        }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmp);
            $rotated = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => null,
            };
            if ($rotated) {
                imagedestroy($src);
                $src = $rotated;
            }
        }
        $w = imagesx($src);
        $h = imagesy($src);
        [$dir, $base] = self::target();
        $quality = (int) env('IMAGE_QUALITY', 80);

        $variants = [];
        $lastWidth = 0;
        foreach (self::VARIANTS as $name => $max) {
            $target = min($max, $w);
            if ($target === $lastWidth) {
                // La imagen es más pequeña que esta variante: se reutiliza la anterior.
                $variants[$name] = end($variants);
                continue;
            }
            $file = "$dir/$base-$name.webp";
            self::resizeTo($src, $target, $w, $h, PUBLIC_PATH . '/uploads/' . $file, $quality);
            $variants[$name] = $file;
            $lastWidth = $target;
        }
        $origFile = "$dir/$base.webp";
        self::resizeTo($src, min(self::MAX_ORIGINAL, $w), $w, $h, PUBLIC_PATH . '/uploads/' . $origFile, $quality + 5);
        imagedestroy($src);

        $scale = min(1, self::MAX_ORIGINAL / $w);
        return [
            'kind' => 'image',
            'path' => $origFile,
            'mime' => 'image/webp',
            'original_name' => mb_substr($originalName, 0, 190),
            'size_bytes' => (int) filesize(PUBLIC_PATH . '/uploads/' . $origFile),
            'width' => (int) round($w * $scale),
            'height' => (int) round($h * $scale),
            'variants' => json_encode($variants),
        ];
    }

    private static function resizeTo(\GdImage $src, int $targetW, int $w, int $h, string $dest, int $quality): void
    {
        $targetH = max(1, (int) round($h * $targetW / $w));
        $dst = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $w, $h);
        imagewebp($dst, $dest, max(40, min(95, $quality)));
        imagedestroy($dst);
    }

    /** Carpeta por año/mes y nombre aleatorio. */
    private static function target(): array
    {
        $dir = date('Y/m');
        $abs = PUBLIC_PATH . '/uploads/' . $dir;
        if (!is_dir($abs) && !mkdir($abs, 0755, true) && !is_dir($abs)) {
            throw new \RuntimeException('No se pudo crear la carpeta de cargas: ' . $abs);
        }
        return [$dir, bin2hex(random_bytes(12))];
    }

    private static function store(string $tmp, string $ext, string $kind, string $mime, string $originalName, int $size, ?int $w, ?int $h): array
    {
        [$dir, $base] = self::target();
        $rel = "$dir/$base.$ext";
        $dest = PUBLIC_PATH . '/uploads/' . $rel;
        $ok = is_uploaded_file($tmp) ? move_uploaded_file($tmp, $dest) : copy($tmp, $dest);
        if (!$ok) {
            throw new \RuntimeException('No se pudo guardar el archivo.');
        }
        @chmod($dest, 0644);
        return [
            'kind' => $kind,
            'path' => $rel,
            'mime' => $mime,
            'original_name' => mb_substr($originalName, 0, 190),
            'size_bytes' => $size,
            'width' => $w,
            'height' => $h,
            'variants' => null,
        ];
    }

    /** Borra del disco el archivo y sus variantes. */
    public static function deleteFiles(array $media): void
    {
        $paths = [$media['path'] ?? null];
        $variants = json_decode((string) ($media['variants'] ?? ''), true);
        if (is_array($variants)) {
            $paths = array_merge($paths, array_values($variants));
        }
        foreach (array_unique(array_filter($paths)) as $p) {
            if (str_starts_with((string) $p, 'http') || str_contains((string) $p, '..')) {
                continue;
            }
            $abs = PUBLIC_PATH . '/uploads/' . $p;
            if (is_file($abs)) {
                @unlink($abs);
            }
        }
    }

    /** Interpreta enlaces de YouTube o Vimeo. Devuelve [proveedor, id] o null. */
    public static function parseEmbed(string $url): ?array
    {
        $url = trim($url);
        if (preg_match('~^(?:https?://)?(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return ['youtube', $m[1]];
        }
        if (preg_match('~^(?:https?://)?(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d{6,12})~', $url, $m)) {
            return ['vimeo', $m[1]];
        }
        return null;
    }
}
