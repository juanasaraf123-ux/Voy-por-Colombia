<?php
declare(strict_types=1);

/**
 * Pruebas de extremo a extremo de los flujos principales.
 * Crea una base SQLite temporal, levanta el servidor embebido de PHP y ejercita el sitio vía HTTP.
 *
 *   php tests/run.php
 *
 * No modifica la base de datos del proyecto (usa DB_PATH temporal) y borra los archivos que sube.
 *
 * Para probar con MySQL/MariaDB (la base indicada se BORRA y se vuelve a crear):
 *   TEST_DB_DRIVER=mysql TEST_DB_NAME=vpc_test TEST_DB_USER=vpc TEST_DB_PASS=secreto php tests/run.php
 */

$root = dirname(__DIR__);
$tmp = sys_get_temp_dir() . '/vpc-tests-' . bin2hex(random_bytes(4));
mkdir($tmp);
$db = $tmp . '/test.sqlite';
$port = random_int(18000, 18999);
$baseUrl = "http://127.0.0.1:$port";
$mysql = getenv('TEST_DB_DRIVER') === 'mysql';
$env = ['DB_DRIVER' => 'sqlite', 'DB_PATH' => $db, 'APP_DEBUG' => 'false', 'APP_BASE_PATH' => '', 'SETUP_TOKEN' => ''];
if ($mysql) {
    $env = ['DB_DRIVER' => 'mysql', 'DB_HOST' => getenv('TEST_DB_HOST') ?: '127.0.0.1', 'DB_PORT' => getenv('TEST_DB_PORT') ?: '3306',
        'DB_NAME' => getenv('TEST_DB_NAME') ?: 'vpc_test', 'DB_USER' => getenv('TEST_DB_USER') ?: 'root', 'DB_PASS' => getenv('TEST_DB_PASS') ?: ''] + $env;
    $admin = new PDO("mysql:host={$env['DB_HOST']};port={$env['DB_PORT']}", $env['DB_USER'], $env['DB_PASS']);
    $admin->exec("DROP DATABASE IF EXISTS `{$env['DB_NAME']}`");
    $admin->exec("CREATE DATABASE `{$env['DB_NAME']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    unset($admin);
}
function test_pdo(): PDO
{
    global $mysql, $env, $db;
    return $mysql ? new PDO("mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']}", $env['DB_USER'], $env['DB_PASS']) : new PDO('sqlite:' . $db);
}
echo 'Motor: ' . ($mysql ? 'MySQL/MariaDB' : 'SQLite') . "\n";
$envPrefix = implode(' ', array_map(static fn ($k, $v) => $k . '=' . escapeshellarg($v), array_keys($env), $env));

echo "Preparando base de datos temporal…\n";
passthru("$envPrefix php " . escapeshellarg("$root/bin/install.php") . ' --demo > /dev/null', $code);
if ($code !== 0) {
    exit("No se pudo instalar la base de datos de prueba.\n");
}
passthru("$envPrefix VPC_ADMIN_PASSWORD='Admin12345x' php " . escapeshellarg("$root/bin/create-admin.php") . ' --name="Admin Test" --email=admin@test.co --username=admintest > /dev/null');
passthru("$envPrefix VPC_ADMIN_PASSWORD='Editor12345x' php " . escapeshellarg("$root/bin/create-admin.php") . ' --name="Editor Test" --email=editor@test.co --username=editortest --role=editor > /dev/null');

$server = proc_open("$envPrefix exec php -S 127.0.0.1:$port -t " . escapeshellarg("$root/public") . ' ' . escapeshellarg("$root/public/index.php"), [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);
for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', $port); $i++) {
    usleep(100000);
}

$passed = 0;
$failed = [];
$createdFiles = [];
function check(string $name, bool $ok, string $detail = ''): void
{
    global $passed, $failed;
    if ($ok) {
        $passed++;
        echo "  ✓ $name\n";
    } else {
        $failed[] = $name;
        echo "  ✗ $name" . ($detail ? " — $detail" : '') . "\n";
    }
}

final class Client
{
    private string $jar;

    public function __construct(private string $base)
    {
        $this->jar = tempnam(sys_get_temp_dir(), 'vpc-cookie');
    }

    public function request(string $method, string $path, array $data = [], array $headers = [], bool $multipart = false): array
    {
        $ch = curl_init($this->base . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 30,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart ? $data : http_build_query($data));
        }
        $raw = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hsize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $head = substr($raw, 0, $hsize);
        preg_match('/^Location:\s*(.+)$/mi', $head, $m);
        return ['status' => $status, 'body' => substr($raw, $hsize), 'location' => trim($m[1] ?? ''), 'headers' => $head];
    }

    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    public function token(string $path): string
    {
        preg_match('/name="_token" value="([a-f0-9]{64})"/', $this->get($path)['body'], $m);
        return $m[1] ?? '';
    }

    public function post(string $path, array $data, string $tokenFrom, bool $multipart = false): array
    {
        $data['_token'] = $this->token($tokenFrom);
        return $this->request('POST', $path, $data, [], $multipart);
    }

    public function login(string $user, string $pass): array
    {
        return $this->post('/admin/login', ['login' => $user, 'password' => $pass], '/admin/login');
    }
}

try {
    echo "\nSitio público\n";
    $guest = new Client($baseUrl);
    foreach (['/', '/turismo', '/gastronomia', '/cafe', '/emprendimiento', '/cultura', '/galeria', '/noticias', '/rutas', '/favoritos', '/mi-ruta', '/buscar?q=cafe', '/departamentos/meta'] as $p) {
        $r = $guest->get($p);
        check("GET $p responde 200", $r['status'] === 200, (string) $r['status']);
    }
    check('Página inexistente responde 404', $guest->get('/no-existe/de-verdad')['status'] === 404);
    $home = $guest->get('/')['body'];
    check('Portada muestra el mensaje principal', str_contains($home, 'te espera') && str_contains($home, 'data-hero-map'));
    check('Cabeceras de seguridad (CSP, nosniff)', str_contains($guest->get('/')['headers'], 'Content-Security-Policy') && str_contains($guest->get('/')['headers'], 'nosniff'));
    $api = json_decode($guest->get('/api/mapa')['body'], true);
    check('API del mapa devuelve marcadores con coordenadas', count($api['markers'] ?? []) > 10 && isset($api['markers'][0]['lat']));
    $search = json_decode($guest->get('/api/buscar?q=' . urlencode('bogota'))['body'], true);
    check('Búsqueda sin tildes encuentra «Bogotá»', (bool) array_filter($search['results'] ?? [], static fn ($r) => str_contains($r['title'], 'Bogotá')));
    $inj = $guest->get('/api/buscar?q=' . urlencode("' OR 1=1; DROP TABLE entries; --"));
    check('Intento de inyección SQL en búsqueda no rompe nada', $inj['status'] === 200 && $guest->get('/turismo')['status'] === 200);
    $lk = json_decode($guest->get('/api/buscar?q=' . urlencode('%%'))['body'], true);
    check('Comodines de LIKE se escapan', ($lk['results'] ?? null) === []);
    check('Borradores no son públicos', $guest->get('/noticias/borrador-guia-de-mercados-campesinos')['status'] === 404);
    check('Publicaciones programadas no son públicas', $guest->get('/noticias/nueva-ruta-por-el-eje-cafetero-programada')['status'] === 404);
    check('Archivadas no son públicas', $guest->get('/noticias/archivo-temporada-de-fin-de-ano-anterior')['status'] === 404);
    $media = json_decode($guest->get('/api/publicaciones/3/multimedia')['body'], true);
    check('Multimedia de un lugar disponible para el visor del mapa', isset($media['images'], $media['videos'], $media['audios']));
    $rutas = json_decode($guest->get('/api/rutas')['body'], true);
    check('Rutas con coordenadas para las líneas animadas', !empty($rutas['routes'][0]['stops'][1]['lat']));
    $mk = json_decode($guest->get('/api/mapa?secciones=tourism,gastronomy')['body'], true);
    check('Marcadores con tipo de punto (puntos vivos)', (bool) array_filter($mk['markers'], static fn ($m) => $m['pointType'] === 'Restaurante o sabor típico'));
    check('GeoJSON de departamentos disponible', str_contains($guest->get('/data/geo/departamentos.geojson')['body'], 'FeatureCollection'));

    echo "\nProtección del panel\n";
    $r = $guest->get('/admin');
    check('/admin sin sesión redirige al ingreso', $r['status'] === 302 && str_contains($r['location'], '/admin/login'));
    check('API privada sin sesión responde 401', $guest->request('GET', '/admin/api/multimedia', [], ['Accept: application/json'])['status'] === 401);
    $r = $guest->request('POST', '/admin/login', ['login' => 'admintest', 'password' => 'Admin12345x']);
    check('POST sin token CSRF es rechazado (419)', $r['status'] === 419);
    $r = $guest->login('admintest', 'incorrecta');
    $page = $guest->get('/admin/login')['body'];
    check('Contraseña incorrecta no inicia sesión', $r['location'] !== '' && str_contains($page, 'incorrectos') && $guest->get('/admin')['status'] === 302);
    $attacker = new Client($baseUrl);
    for ($i = 0; $i < 5; $i++) {
        $attacker->login('editortest', 'mala' . $i);
    }
    $attacker->login('editortest', 'Editor12345x');
    check('Bloqueo tras 5 intentos fallidos (incluso con la clave correcta)', $attacker->get('/admin')['status'] === 302);
    check('Instalador web deshabilitado sin SETUP_TOKEN', $guest->get('/admin/instalar?token=x')['status'] === 404);

    echo "\nAdministrador\n";
    $admin = new Client($baseUrl);
    $r = $admin->login('admintest', 'Admin12345x');
    check('Ingreso del administrador', $r['status'] === 302 && str_ends_with($r['location'], '/admin'));
    check('Dashboard accesible', $admin->get('/admin')['status'] === 200);
    foreach (['/admin/usuarios', '/admin/auditoria', '/admin/configuracion', '/admin/multimedia', '/admin/mapa', '/admin/inicio', '/admin/categorias', '/admin/rutas'] as $p) {
        check("Admin accede a $p", $admin->get($p)['status'] === 200);
    }

    // Carga de imagen válida (generada con GD) y archivos peligrosos.
    $png = "$tmp/foto-prueba.png";
    $im = imagecreatetruecolor(1200, 800);
    imagefill($im, 0, 0, imagecolorallocate($im, 18, 181, 176));
    imagepng($im, $png);
    $r = $admin->request('POST', '/admin/api/multimedia', ['_token' => $admin->token('/admin/multimedia'), 'files[]' => new CURLFile($png, 'image/png', 'foto-prueba.png')], ['Accept: application/json'], true);
    $up = json_decode($r['body'], true);
    $mediaId = $up['items'][0]['id'] ?? null;
    check('Subida de imagen válida', $r['status'] === 200 && $mediaId, $r['body']);
    $mediaRow = $mediaId ? json_decode($admin->get('/admin/api/multimedia?q=foto')['body'], true)['items'][0] ?? null : null;
    check('Se generan variantes WebP optimizadas', $mediaRow && str_ends_with($mediaRow['thumb'], '.webp') && $guest->get(parse_url($mediaRow['thumb'], PHP_URL_PATH))['status'] === 200);
    file_put_contents("$tmp/falsa.jpg", "<?php echo 'hola'; ?>");
    $r = $admin->request('POST', '/admin/api/multimedia', ['_token' => $admin->token('/admin/multimedia'), 'files[]' => new CURLFile("$tmp/falsa.jpg", 'image/jpeg', 'falsa.jpg')], ['Accept: application/json'], true);
    check('Rechaza PHP disfrazado de JPG', $r['status'] === 422 && str_contains($r['body'], 'no permitido'));
    file_put_contents("$tmp/shell.php", "<?php system('id'); ?>");
    $r = $admin->request('POST', '/admin/api/multimedia', ['_token' => $admin->token('/admin/multimedia'), 'files[]' => new CURLFile("$tmp/shell.php", 'application/x-php', 'shell.php')], ['Accept: application/json'], true);
    check('Rechaza archivos .php', $r['status'] === 422);
    copy($png, "$tmp/doble.php.png");
    $r = $admin->request('POST', '/admin/api/multimedia', ['_token' => $admin->token('/admin/multimedia'), 'files[]' => new CURLFile("$tmp/doble.php.png", 'image/png', 'doble.png.php')], ['Accept: application/json'], true);
    check('Rechaza extensión que no coincide con el contenido', $r['status'] === 422);
    check('La carpeta de cargas no ejecuta PHP', is_file("$root/public/uploads/.htaccess") && str_contains((string) file_get_contents("$root/public/uploads/.htaccess"), 'php'));

    // Publicación nueva conectada con el sitio público.
    $deps = json_decode($admin->get('/admin/api/municipios?departamento=1')['body'], true);
    $munId = $deps['items'][0]['id'] ?? null;
    $catId = null;
    if (preg_match('/name="categories\[\]" value="(\d+)"/', $admin->get('/admin/contenido/turismo/nuevo')['body'], $m)) {
        $catId = $m[1];
    }
    $title = 'Laguna de prueba <script>alert(1)</script>';
    $r = $admin->post('/admin/contenido/turismo/nuevo', [
        'title' => $title, 'summary' => 'Resumen de la laguna de prueba.', 'body' => "## Subtítulo\n\nTexto con **negrita** y <img src=x onerror=alert(1)>.",
        'status' => 'published', 'department_id' => '1', 'municipality_id' => (string) $munId, 'lat' => '-4.1', 'lng' => '-69.9',
        'show_on_map' => '1', 'categories[]' => $catId, 'cover_media_id' => (string) $mediaId, 'media_ids[]' => (string) $mediaId,
        'meta_activities' => "Kayak\nAvistamiento", 'is_featured' => '1',
    ], '/admin/contenido/turismo/nuevo');
    preg_match('#/admin/publicaciones/(\d+)/editar#', $r['location'], $m);
    $newId = $m[1] ?? null;
    check('Crear y publicar un destino', $r['status'] === 302 && $newId, $r['location']);
    $pub = $guest->get('/turismo/laguna-de-prueba-script-alert-1-script');
    check('El destino aparece en el sitio público', $pub['status'] === 200 && str_contains($pub['body'], 'Kayak'));
    check('El título se escapa (sin XSS)', !str_contains($pub['body'], '<script>alert(1)</script>') && str_contains($pub['body'], '&lt;script&gt;'));
    check('El HTML del contenido no se interpreta', !str_contains($pub['body'], '<img src=x') && str_contains($pub['body'], '<strong>negrita</strong>'));
    $api = json_decode($guest->get('/api/mapa')['body'], true);
    check('El destino aparece en el mapa', (bool) array_filter($api['markers'], static fn ($mk) => (int) $mk['id'] === (int) $newId));

    // Validaciones
    $r = $admin->post('/admin/contenido/turismo/nuevo', ['title' => 'X', 'status' => 'published', 'lat' => '40', 'lng' => '3', 'department_id' => '1', 'municipality_id' => '999999'], '/admin/contenido/turismo/nuevo');
    $form = $admin->get('/admin/contenido/turismo/nuevo')['body'];
    check('Validación: título corto, coordenadas fuera de Colombia y municipio ajeno', str_contains($form, 'al menos 3 caracteres') && str_contains($form, 'fuera de Colombia') && str_contains($form, 'no pertenece'));

    // Programación y vista previa
    $future = date('Y-m-d\TH:i', time() + 86400 * 5);
    $r = $admin->post('/admin/contenido/noticias/nuevo', ['title' => 'Noticia programada de prueba', 'summary' => 'Resumen', 'status' => 'published', 'published_at' => $future], '/admin/contenido/noticias/nuevo');
    preg_match('#/admin/publicaciones/(\d+)/editar#', $r['location'], $m);
    $schedId = $m[1] ?? 0;
    check('Noticia programada no aparece todavía', $guest->get('/noticias/noticia-programada-de-prueba')['status'] === 404);
    check('Vista previa disponible para el administrador', $admin->get("/admin/publicaciones/$schedId/vista-previa")['status'] === 200);
    check('Vista previa bloqueada para visitantes', $guest->get("/admin/publicaciones/$schedId/vista-previa")['status'] === 302);

    // Archivar y eliminar
    $admin->post("/admin/publicaciones/$newId/estado", ['status' => 'archived'], "/admin/publicaciones/$newId/editar");
    check('Archivar oculta el destino', $guest->get('/turismo/laguna-de-prueba-script-alert-1-script')['status'] === 404);
    $admin->post("/admin/publicaciones/$newId/estado", ['status' => 'published'], "/admin/publicaciones/$newId/editar");
    check('Volver a publicar lo muestra', $guest->get('/turismo/laguna-de-prueba-script-alert-1-script')['status'] === 200);
    $r = $admin->post("/admin/publicaciones/$newId/eliminar", ['confirm' => 'no'], "/admin/publicaciones/$newId/editar");
    check('Eliminar exige confirmación escrita', $admin->get("/admin/publicaciones/$newId/editar")['status'] === 200);
    $admin->post("/admin/publicaciones/$newId/eliminar", ['confirm' => 'ELIMINAR'], "/admin/publicaciones/$newId/editar");
    check('Eliminar la publicación', $admin->get("/admin/publicaciones/$newId/editar")['status'] === 404);

    // Portada editable
    $home = $admin->get('/admin/inicio')['body'];
    preg_match_all('/name="hero_media_ids\[\]" value="(\d+)"/', $home, $hm);
    $admin->post('/admin/inicio', ['hero_title' => 'Un país para descubrir hoy', 'hero_cta' => 'Explora', 'hero_kicker' => 'Prueba', 'hero_subtitle' => 'Sub', 'hero_words' => 'mar', 'hero_media_ids[]' => $hm[1][0] ?? ''], '/admin/inicio');
    check('Cambios de portada se reflejan en el sitio', str_contains($guest->get('/')['body'], 'descubrir'));

    // Galería alimentada por la biblioteca
    $admin->post("/admin/multimedia/$mediaId", ['title' => 'Foto de prueba galería', 'alt_text' => 'Color turquesa', 'in_gallery' => '1', 'credit' => 'Equipo de pruebas'], "/admin/multimedia/$mediaId");
    $gal = json_decode($guest->get('/api/galeria?tipo=foto')['body'], true);
    check('La galería muestra imágenes marcadas como aptas', (bool) array_filter($gal['items'], static fn ($it) => $it['title'] === 'Foto de prueba galería'));

    echo "\nEditor (permisos)\n";
    $editor = new Client($baseUrl);
    $editor->login('editortest', 'Editor12345x');
    $ok = $editor->get('/admin')['status'] === 200;
    if (!$ok) {
        // La cuenta pudo quedar bloqueada por la prueba de intentos fallidos: se limpia el registro.
        test_pdo()->exec("DELETE FROM login_attempts");
        $editor->login('editortest', 'Editor12345x');
    }
    check('Editor puede ingresar', $editor->get('/admin')['status'] === 200);
    check('Editor gestiona contenido', $editor->get('/admin/contenido/cultura')['status'] === 200);
    check('Editor NO accede a usuarios (403)', $editor->get('/admin/usuarios')['status'] === 403);
    check('Editor NO accede a configuración (403)', $editor->get('/admin/configuracion')['status'] === 403);
    check('Editor NO accede a auditoría (403)', $editor->get('/admin/auditoria')['status'] === 403);
    $r = $editor->post('/admin/usuarios/nuevo', ['name' => 'Intruso', 'email' => 'x@x.co', 'username' => 'intruso', 'role' => 'admin', 'password' => 'Intruso12345', 'password_confirmation' => 'Intruso12345'], '/admin');
    check('Editor NO puede crear administradores por POST directo', $r['status'] === 403);
    check('Editor NO ve el menú de administración', !str_contains($editor->get('/admin')['body'], '/admin/usuarios'));

    echo "\nUsuarios y sesiones\n";
    $r = $admin->post('/admin/usuarios/nuevo', ['name' => 'Nueva Persona', 'email' => 'nueva@test.co', 'username' => 'nueva', 'role' => 'editor', 'password' => 'corta', 'password_confirmation' => 'corta'], '/admin/usuarios/nuevo');
    check('Rechaza contraseñas débiles', str_contains($admin->get('/admin/usuarios/nuevo')['body'], 'al menos 10'));
    $users = $admin->get('/admin/usuarios')['body'];
    preg_match('#/admin/usuarios/(\d+)/estado#', $users, $m);
    $admin->post('/admin/usuarios/' . $m[1] . '/estado', [], '/admin/usuarios');
    check('Desactivar una cuenta cierra su sesión inmediatamente', $editor->get('/admin')['status'] === 302);
    $editor->login('editortest', 'Editor12345x');
    check('Una cuenta desactivada no puede ingresar', $editor->get('/admin')['status'] === 302);
    $audit = $admin->get('/admin/auditoria')['body'];
    check('La auditoría registra creación, publicación y eliminación', str_contains($audit, 'Eliminó') && str_contains($audit, 'Publicó') && str_contains($audit, 'Subió'));
    $admin->post('/admin/logout', [], '/admin');
    check('Cerrar sesión', $admin->get('/admin')['status'] === 302);
} finally {
    proc_terminate($server);
    // Limpieza de archivos subidos durante la prueba.
    $pdo = test_pdo();
    foreach ($pdo->query("SELECT path, variants FROM media WHERE original_name LIKE 'foto-prueba%' OR original_name = 'portada-paisaje.webp' OR is_demo = 1")->fetchAll(PDO::FETCH_ASSOC) as $row) {
        foreach (array_merge([$row['path']], array_values(json_decode((string) $row['variants'], true) ?: [])) as $p) {
            @unlink("$root/public/uploads/$p");
        }
    }
    array_map('unlink', glob("$tmp/*") ?: []);
    @rmdir($tmp);
}

echo "\n$passed pruebas correctas, " . count($failed) . " fallidas.\n";
exit($failed ? 1 : 0);
