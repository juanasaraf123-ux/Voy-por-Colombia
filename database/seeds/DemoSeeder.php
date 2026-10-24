<?php
declare(strict_types=1);

use App\Core\Database as DB;
use App\Core\MediaProcessor;
use App\Models\Entry;
use App\Models\Route;
use App\Models\Setting;

/**
 * Contenido de DEMOSTRACIÓN. Todo lo que crea queda marcado con is_demo = 1 y se puede
 * eliminar desde el panel (Configuración › Datos de demostración) o con `php bin/remove-demo.php`.
 *
 * - Las imágenes son ilustraciones originales generadas por código (tools/demo-art), no fotografías.
 * - Los destinos, platos y festividades son lugares y tradiciones reales descritos de forma general.
 * - Los emprendimientos, la finca y las noticias son ficticios y se identifican como demostración.
 * - Las fechas de eventos son de referencia: deben confirmarse con los organizadores.
 */
final class DemoSeeder
{
    private const CREDIT = 'Ilustración de demostración · Voy por Colombia';
    private const LICENSE = 'Demo: reemplazar por fotografía con derechos';

    /** archivo => [título, texto alternativo, departamento, municipio, colección, destacada en galería] */
    private const MEDIA = [
        'caribe' => ['Playa caribeña', 'Ilustración de playa con palmeras y mar turquesa', 'magdalena', 'santa-marta', 'paisajes', true],
        'caribe-2' => ['Mar Caribe', 'Ilustración de costa caribeña con palmeras', 'magdalena', 'santa-marta', 'paisajes', false],
        'san-andres' => ['Mar de siete colores', 'Ilustración aérea de franjas de mar en tonos azules y verdes', 'san-andres-y-providencia', 'san-andres', 'paisajes', true],
        'cocora' => ['Valle de palmas de cera', 'Ilustración de colinas verdes con palmas de cera y neblina', 'quindio', 'salento', 'paisajes', true],
        'cocora-vertical' => ['Palmas entre la niebla', 'Ilustración vertical de palmas de cera', 'quindio', 'salento', 'paisajes', false],
        'paisaje-cafetero' => ['Montañas de café', 'Ilustración de laderas con surcos de café y una casa campesina', 'caldas', 'chinchina', 'cultura-cafetera', true],
        'paisaje-cafetero-2' => ['Surcos cafeteros', 'Ilustración de cultivos de café en ladera', 'quindio', 'filandia', 'cultura-cafetera', false],
        'cano-cristales' => ['Río de cinco colores', 'Ilustración de río con plantas acuáticas rojas, amarillas y verdes', 'meta', 'la-macarena', 'paisajes', true],
        'cano-cristales-vertical' => ['Colores del río', 'Ilustración vertical del río de colores', 'meta', 'la-macarena', 'paisajes', false],
        'cartagena' => ['Calle colonial colorida', 'Ilustración de casas coloniales con balcones y buganvilias', 'bolivar', 'cartagena-de-indias', 'paisajes', true],
        'villa-de-leyva' => ['Pueblo blanco', 'Ilustración de casas blancas con balcones de madera', 'boyaca', 'villa-de-leyva', 'paisajes', false],
        'guatape' => ['Zócalos de colores', 'Ilustración de fachadas de colores vivos', 'antioquia', 'guatape', 'paisajes', true],
        'barichara' => ['Pueblo de piedra', 'Ilustración vertical de casas de tapia y teja', 'santander', 'barichara', 'paisajes', false],
        'guajira' => ['Dunas junto al mar', 'Ilustración de dunas naranjas que llegan al mar', 'la-guajira', 'uribia', 'paisajes', true],
        'amazonas' => ['Río amazónico', 'Ilustración de río entre selva al atardecer', 'amazonas', 'leticia', 'paisajes', false],
        'amazonas-vertical' => ['Atardecer en la selva', 'Ilustración vertical de selva y río', 'amazonas', 'puerto-narino', 'paisajes', false],
        'llanos' => ['Atardecer llanero', 'Ilustración de llanura con palmas de moriche al atardecer', 'casanare', 'paz-de-ariporo', 'paisajes', true],
        'pacifico' => ['Ballena en el Pacífico', 'Ilustración de cola de ballena frente a la selva', 'choco', 'nuqui', 'paisajes', true],
        'tatacoa' => ['Desierto rojo', 'Ilustración de formaciones rocosas rojizas con cactus', 'huila', 'villavieja', 'paisajes', false],
        'tatacoa-noche' => ['Noche estrellada en el desierto', 'Ilustración de cielo estrellado sobre el desierto', 'huila', 'villavieja', 'paisajes', true],
        'medellin' => ['Ciudad entre montañas', 'Ilustración de ciudad nocturna con cable aéreo', 'antioquia', 'medellin', 'paisajes', false],
        'cascada' => ['Cascada en la selva', 'Ilustración de cascada entre paredes de roca y selva', 'putumayo', 'mocoa', 'paisajes', true],
        'san-agustin' => ['Estatuas de piedra', 'Ilustración de esculturas de piedra en un prado', 'huila', 'san-agustin', 'cultura-viva', false],
        'andes-amanecer' => ['Amanecer andino', 'Ilustración de cordilleras en capas al amanecer', 'narino', 'pasto', 'paisajes', false],
        'andes-violeta' => ['Cordillera violeta', 'Ilustración de montañas en tonos violeta', 'bogota-d-c', 'bogota-d-c', 'paisajes', true],
        'nevado' => ['Cumbres nevadas', 'Ilustración de picos nevados y montañas azules', 'boyaca', 'el-cocuy', 'paisajes', false],
        'paramo' => ['Páramo de frailejones', 'Ilustración de frailejones junto a una laguna de páramo', 'boyaca', 'mongui', 'paisajes', true],
        'bandeja-paisa' => ['Bandeja paisa', 'Ilustración cenital de bandeja paisa', 'antioquia', 'medellin', 'sabores', true],
        'ajiaco' => ['Ajiaco', 'Ilustración cenital de ajiaco con mazorca', 'bogota-d-c', 'bogota-d-c', 'sabores', false],
        'arepas' => ['Arepas', 'Ilustración cenital de arepas doradas', 'atlantico', 'luruaco', 'sabores', false],
        'sancocho' => ['Sancocho', 'Ilustración cenital de sancocho', 'valle-del-cauca', 'cali', 'sabores', false],
        'empanadas' => ['Empanadas', 'Ilustración cenital de empanadas con ají', 'cauca', 'popayan', 'sabores', true],
        'frutas' => ['Frutas de Colombia', 'Ilustración de frutas tropicales partidas', null, null, 'sabores', true],
        'cazuela' => ['Cazuela de mariscos', 'Ilustración cenital de cazuela de mariscos', 'bolivar', 'cartagena-de-indias', 'sabores', false],
        'tamal' => ['Tamal', 'Ilustración cenital de tamal en hoja de plátano', 'tolima', 'ibague', 'sabores', false],
        'jugos' => ['Jugos naturales', 'Ilustración de tres vasos de jugo de frutas', 'valle-del-cauca', 'cali', 'sabores', false],
        'taza-cafe' => ['Taza de café', 'Ilustración cenital de taza de café rodeada de granos', null, null, 'cultura-cafetera', true],
        'cerezas-cafe' => ['Cerezas de café', 'Ilustración de rama con cerezas de café rojas y amarillas', 'quindio', 'salento', 'cultura-cafetera', false],
        'granos-cafe' => ['Granos al sol', 'Ilustración de granos de café secándose en un patio', 'caldas', 'chinchina', 'cultura-cafetera', false],
        'metodo-filtrado' => ['Café filtrado', 'Ilustración de un método de filtrado de café', null, null, 'cultura-cafetera', false],
        'mochila-wayuu' => ['Tejido geométrico', 'Ilustración de patrones geométricos de colores inspirados en el tejido', 'la-guajira', 'uribia', 'cultura-viva', true],
        'mola' => ['Capas de color', 'Ilustración de capas de color superpuestas inspiradas en el arte textil', 'antioquia', 'necocli', 'cultura-viva', false],
        'sombrero-vueltiao' => ['Trenzado en espiral', 'Ilustración cenital de un sombrero con trenzas blancas y negras', 'cordoba', 'tuchin', 'cultura-viva', true],
        'silleta-flores' => ['Flores de feria', 'Ilustración de flores de colores', 'antioquia', 'medellin', 'cultura-viva', false],
        'carnaval' => ['Abanico de carnaval', 'Ilustración de abanico de colores y confeti', 'atlantico', 'barranquilla', 'cultura-viva', true],
        'musica' => ['Tambores y maracas', 'Ilustración de tambor, maracas y gaita', 'cesar', 'valledupar', 'cultura-viva', false],
        'pollera' => ['Pollera en movimiento', 'Ilustración abstracta de una falda de cumbia girando', 'magdalena', 'el-banco', 'cultura-viva', false],
        'ceramica' => ['Taller de cerámica', 'Ilustración de vasijas de barro en estanterías', 'boyaca', 'raquira', 'hecho-en-colombia', true],
        'tejidos' => ['Telares', 'Ilustración de tejidos colgados de colores', 'cauca', 'silvia', 'hecho-en-colombia', false],
        'mercado' => ['Mercado campesino', 'Ilustración de puesto de mercado con frutas', 'boyaca', 'villa-de-leyva', 'hecho-en-colombia', false],
        'miel' => ['Miel de abejas', 'Ilustración de frascos de miel sobre panal', 'santander', 'barichara', 'hecho-en-colombia', false],
    ];

    private static array $mediaIds = [];
    private static array $deps = [];

    public static function run(callable $log): void
    {
        if (DB::value('SELECT COUNT(*) FROM entries WHERE is_demo = 1') > 0) {
            $log('El contenido de demostración ya existe. Para regenerarlo ejecuta primero: php bin/remove-demo.php');
            return;
        }
        self::$deps = array_column(DB::all('SELECT id, slug FROM departments'), 'id', 'slug');
        self::importMedia($log);
        $entries = self::entries();
        $ids = [];
        foreach ($entries as $e) {
            $ids[$e['key'] ?? $e['title']] = self::createEntry($e);
        }
        $log('Publicaciones de demostración: ' . count($ids));
        self::routes($ids);
        self::sectionsAndHome();
        $log('Rutas, portada y secciones configuradas.');
    }

    private static function importMedia(callable $log): void
    {
        $collections = array_column(DB::all('SELECT id, slug FROM collections'), 'id', 'slug');
        $sectionByCollection = ['paisajes' => 'tourism', 'sabores' => 'gastronomy', 'cultura-cafetera' => 'coffee', 'cultura-viva' => 'culture', 'hecho-en-colombia' => 'entrepreneurship'];
        foreach (self::MEDIA as $file => [$title, $alt, $dep, $mun, $col, $featured]) {
            $path = __DIR__ . "/demo-media/$file.webp";
            if (!is_file($path)) {
                continue;
            }
            $data = MediaProcessor::importLocal($path, "$file.webp");
            [$depId, $munId] = self::place($dep, $mun);
            self::$mediaIds[$file] = DB::insert('media', $data + [
                'title' => $title,
                'alt_text' => $alt,
                'caption' => $title . '. Imagen ilustrativa de demostración.',
                'credit' => self::CREDIT,
                'license' => self::LICENSE,
                'department_id' => $depId,
                'municipality_id' => $munId,
                'collection_id' => $collections[$col] ?? null,
                'gallery_section' => $sectionByCollection[$col] ?? null,
                'in_gallery' => 1,
                'gallery_featured' => $featured ? 1 : 0,
                'is_demo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $log('Ilustraciones importadas: ' . count(self::$mediaIds));
    }

    private static function place(?string $dep, ?string $mun): array
    {
        $depId = $dep ? (self::$deps[$dep] ?? null) : null;
        $munId = $depId && $mun ? DB::value('SELECT id FROM municipalities WHERE department_id = ? AND slug = ?', [$depId, $mun]) : null;
        return [$depId ? (int) $depId : null, $munId ? (int) $munId : null];
    }

    private static function createEntry(array $e): int
    {
        [$depId, $munId] = self::place($e['dep'] ?? null, $e['mun'] ?? null);
        $catIds = [];
        foreach ($e['cats'] ?? [] as $catName) {
            $cid = DB::value('SELECT id FROM categories WHERE section = ? AND slug = ?', [$e['section'], slugify($catName)]);
            if ($cid) {
                $catIds[] = (int) $cid;
            }
        }
        $publishedAt = date('Y-m-d H:i:s', strtotime(($e['days'] ?? -random_int(2, 60)) . ' days'));
        $data = [
            'section' => $e['section'],
            'title' => $e['title'],
            'slug' => Entry::uniqueSlug($e['section'], $e['title']),
            'summary' => $e['summary'],
            'body' => $e['body'] ?? null,
            'status' => $e['status'] ?? 'published',
            'published_at' => $publishedAt,
            'department_id' => $depId,
            'municipality_id' => $munId,
            'lat' => $e['lat'] ?? null,
            'lng' => $e['lng'] ?? null,
            'show_on_map' => isset($e['lat']) ? 1 : 0,
            'cover_media_id' => self::$mediaIds[$e['cover']] ?? null,
            'is_featured' => !empty($e['featured']) ? 1 : 0,
            'featured_order' => (int) ($e['order'] ?? 0),
            'is_hidden_gem' => !empty($e['gem']) ? 1 : 0,
            'event_start' => $e['event_start'] ?? null,
            'event_end' => $e['event_end'] ?? null,
            'author_name' => $e['author'] ?? null,
            'meta' => json_encode($e['meta'] ?? [], JSON_UNESCAPED_UNICODE),
            'is_demo' => 1,
        ];
        $gallery = array_values(array_filter(array_map(static fn ($k) => self::$mediaIds[$k] ?? null, $e['gallery'] ?? [])));
        return Entry::save(null, $data, $catIds, $gallery, null);
    }

    private static function routes(array $ids): void
    {
        $routes = [
            ['Caribe esencial', 'De la ciudad amurallada a las playas del Tayrona y el desierto que toca el mar.', '7 a 10 días', 'caribe', ['cartagena', 'tayrona', 'cabo-de-la-vela', 'mompox']],
            ['Eje Cafetero: montaña y taza', 'Palmas de cera, pueblos de bahareque y fincas donde nace el café.', '4 a 6 días', 'paisaje-cafetero', ['cocora', 'paisaje-cafetero', 'guatape']],
            ['Sur mágico', 'Estatuas milenarias, un desierto bajo las estrellas y cascadas en el piedemonte amazónico.', '6 a 8 días', 'tatacoa-noche', ['san-agustin', 'tatacoa', 'fin-del-mundo']],
        ];
        foreach ($routes as [$title, $summary, $duration, $cover, $stops]) {
            $stopRows = [];
            foreach ($stops as $key) {
                if (isset($ids[$key])) {
                    $stopRows[] = ['entry_id' => $ids[$key], 'note' => null];
                }
            }
            Route::save(null, [
                'title' => $title,
                'slug' => Route::uniqueSlug($title),
                'summary' => $summary,
                'body' => "Ruta sugerida de demostración. Ajusta el orden y los días según tu ritmo y confirma el estado de las vías antes de viajar.",
                'duration' => $duration,
                'cover_media_id' => self::$mediaIds[$cover] ?? null,
                'status' => 'published',
                'is_featured' => 1,
                'is_demo' => 1,
            ], $stopRows, null);
        }
    }

    private static function sectionsAndHome(): void
    {
        $covers = ['tourism' => 'cocora', 'gastronomy' => 'bandeja-paisa', 'coffee' => 'paisaje-cafetero', 'entrepreneurship' => 'ceramica', 'culture' => 'carnaval', 'gallery' => 'san-andres', 'news' => 'medellin'];
        foreach ($covers as $key => $file) {
            DB::update('sections', ['cover_media_id' => self::$mediaIds[$file] ?? null], 'section_key = :k', ['k' => $key]);
        }
        $hero = array_values(array_filter(array_map(static fn ($k) => self::$mediaIds[$k] ?? null, ['cano-cristales', 'caribe', 'cocora', 'cartagena', 'guajira'])));
        Setting::set('hero_media_ids', json_encode($hero));
        foreach (['bolivar' => 'cartagena', 'quindio' => 'cocora', 'meta' => 'cano-cristales', 'la-guajira' => 'guajira', 'huila' => 'tatacoa', 'magdalena' => 'caribe', 'san-andres-y-providencia' => 'san-andres', 'antioquia' => 'guatape', 'boyaca' => 'villa-de-leyva', 'amazonas' => 'amazonas', 'choco' => 'pacifico', 'casanare' => 'llanos', 'putumayo' => 'cascada', 'santander' => 'barichara', 'caldas' => 'paisaje-cafetero', 'bogota-d-c' => 'andes-violeta', 'narino' => 'andes-amanecer'] as $dep => $file) {
            DB::update('departments', ['cover_media_id' => self::$mediaIds[$file] ?? null], 'slug = :s', ['s' => $dep]);
        }
    }

    private static function entries(): array
    {
        $demoNote = "\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.";
        return [
            // ---------------- TURISMO ----------------
            ['key' => 'cartagena', 'section' => 'tourism', 'title' => 'Cartagena de Indias, la ciudad amurallada', 'dep' => 'bolivar', 'mun' => 'cartagena-de-indias', 'lat' => 10.4236, 'lng' => -75.5503,
                'cats' => ['Patrimonio', 'Playas', 'Cultura', 'Ciudades'], 'cover' => 'cartagena', 'gallery' => ['cartagena', 'caribe-2', 'cazuela'], 'featured' => true, 'order' => 1,
                'summary' => 'Balcones con flores, murallas centenarias y atardeceres sobre el Caribe en una ciudad declarada Patrimonio de la Humanidad.',
                'body' => "Caminar por el centro histórico es recorrer siglos de historia entre plazas, iglesias y casas de colores. Al caer la tarde, las murallas se llenan de gente que espera el atardecer frente al mar.\n\n## Para no perderse\n- El centro histórico y el barrio Getsemaní.\n- Un paseo por las murallas al atardecer.\n- Las islas cercanas para un día de mar." . $demoNote,
                'meta' => ['activities' => ['Recorrido por el centro histórico', 'Atardecer en las murallas', 'Paseo a islas cercanas', 'Gastronomía caribeña'], 'best_time' => 'Todo el año; de diciembre a abril hay menos lluvias.', 'altitude' => 'Nivel del mar · clima cálido']],
            ['key' => 'tayrona', 'section' => 'tourism', 'title' => 'Parque Nacional Natural Tayrona', 'dep' => 'magdalena', 'mun' => 'santa-marta', 'lat' => 11.3089, 'lng' => -74.0753,
                'cats' => ['Naturaleza', 'Playas', 'Aventura'], 'cover' => 'caribe', 'gallery' => ['caribe', 'caribe-2'], 'featured' => true, 'order' => 2,
                'summary' => 'Donde la selva de la Sierra Nevada se encuentra con playas de arena dorada y grandes rocas.',
                'body' => "Senderos entre bosque seco y húmedo conducen a bahías de agua cristalina. Es territorio sagrado para los pueblos de la Sierra Nevada, por lo que el parque cierra algunos periodos del año para su descanso.\n\n## Viaja con respeto\nLleva tus residuos de regreso, respeta las indicaciones de los guardaparques y consulta las fechas de cierre antes de planear." . $demoNote,
                'meta' => ['activities' => ['Senderismo', 'Playas', 'Avistamiento de aves', 'Camping'], 'best_time' => 'Consulta los cierres temporales anunciados por Parques Nacionales.', 'recommendations' => ['Usa calzado cómodo', 'Lleva agua y bloqueador biodegradable', 'No todas las playas son aptas para nadar']]],
            ['key' => 'cano-cristales', 'section' => 'tourism', 'title' => 'Caño Cristales, el río de los cinco colores', 'dep' => 'meta', 'mun' => 'la-macarena', 'lat' => 2.2647, 'lng' => -73.7928,
                'cats' => ['Naturaleza', 'Aventura'], 'cover' => 'cano-cristales', 'gallery' => ['cano-cristales', 'cano-cristales-vertical'], 'featured' => true, 'order' => 3,
                'summary' => 'Una planta acuática tiñe el río de rojo, amarillo, verde y rosado durante algunos meses del año.',
                'body' => "En la Serranía de La Macarena, el río se convierte en una paleta viva gracias a la *Macarenia clavigera*. La temporada de visita suele ir de junio a noviembre y el acceso es guiado y controlado para proteger el ecosistema.\n\n## Antes de ir\nEl ingreso requiere guías locales y charla de inducción. No se permite usar bloqueador ni repelente en el agua." . $demoNote,
                'meta' => ['activities' => ['Caminatas guiadas', 'Baño en pozos autorizados', 'Fotografía de naturaleza'], 'best_time' => 'Temporada habitual: junio a noviembre.', 'how_to_get' => 'Vuelo a La Macarena y traslados con operadores locales autorizados.']],
            ['key' => 'cocora', 'section' => 'tourism', 'title' => 'Valle de Cocora y las palmas de cera', 'dep' => 'quindio', 'mun' => 'salento', 'lat' => 4.6386, 'lng' => -75.4875,
                'cats' => ['Naturaleza', 'Turismo rural', 'Aventura'], 'cover' => 'cocora', 'gallery' => ['cocora', 'cocora-vertical', 'cerezas-cafe'], 'featured' => true, 'order' => 4,
                'summary' => 'Las palmas de cera, árbol nacional de Colombia, se elevan entre la niebla del valle.',
                'body' => "La palma de cera del Quindío puede superar los 50 metros de altura y es una especie protegida. Caminos entre potreros, bosque de niebla y puentes colgantes conducen a miradores inolvidables.\n\nMuy cerca está Salento, con su calle Real y sus casas de bahareque de colores." . $demoNote,
                'meta' => ['activities' => ['Caminata por el valle', 'Cabalgatas', 'Avistamiento de colibríes', 'Visita a Salento'], 'altitude' => 'Entre 1.800 y 2.400 m · clima frío', 'best_time' => 'Temprano en la mañana, antes de que llegue la niebla.']],
            ['key' => 'tatacoa', 'section' => 'tourism', 'title' => 'Desierto de la Tatacoa', 'dep' => 'huila', 'mun' => 'villavieja', 'lat' => 3.2333, 'lng' => -75.1667,
                'cats' => ['Naturaleza', 'Aventura'], 'cover' => 'tatacoa', 'gallery' => ['tatacoa', 'tatacoa-noche'], 'featured' => true, 'order' => 5,
                'summary' => 'Laberintos de arcilla roja y gris de día; uno de los cielos más limpios del país de noche.',
                'body' => "Este bosque seco tropical esconde cárcavas rojizas en la zona de El Cuzco y formaciones grises en Los Hoyos. Al anochecer, la observación astronómica es la gran protagonista." . $demoNote,
                'meta' => ['activities' => ['Observación de estrellas', 'Caminatas por las cárcavas', 'Piscinas naturales', 'Fotografía'], 'best_time' => 'Temporadas secas para cielos despejados.', 'recommendations' => ['Lleva sombrero e hidratación', 'Evita caminar en las horas de más calor']]],
            ['key' => 'cabo-de-la-vela', 'section' => 'tourism', 'title' => 'Cabo de la Vela', 'dep' => 'la-guajira', 'mun' => 'uribia', 'lat' => 12.2, 'lng' => -72.15,
                'cats' => ['Playas', 'Cultura', 'Aventura'], 'cover' => 'guajira', 'gallery' => ['guajira', 'mochila-wayuu'], 'featured' => true, 'order' => 6,
                'summary' => 'Desierto, viento y mar en el territorio ancestral del pueblo wayuu.',
                'body' => "El Cabo de la Vela, Jepirra para los wayuu, es un lugar sagrado. Aquí el desierto se encuentra con el mar y los atardeceres desde el Pilón de Azúcar son legendarios.\n\nLas rancherías ofrecen hospedaje en chinchorros y la oportunidad de conocer el tejido y la cocina wayuu." . $demoNote,
                'meta' => ['activities' => ['Kitesurf', 'Atardecer en el Pilón de Azúcar', 'Hospedaje en ranchería', 'Encuentro con artesanas wayuu'], 'recommendations' => ['Viaja con operadores locales', 'Pide permiso antes de fotografiar personas']]],
            ['key' => 'san-andres', 'section' => 'tourism', 'title' => 'San Andrés, el mar de siete colores', 'dep' => 'san-andres-y-providencia', 'mun' => 'san-andres', 'lat' => 12.5847, 'lng' => -81.7006,
                'cats' => ['Playas', 'Naturaleza', 'Cultura'], 'cover' => 'san-andres', 'gallery' => ['san-andres', 'caribe-2'], 'featured' => true, 'order' => 7,
                'summary' => 'Arrecifes, cayos y una cultura raizal con su propia lengua, música y cocina.',
                'body' => "Las tonalidades del mar se deben a la profundidad y al fondo de arena blanca y corales. Más allá de las playas, la isla guarda la cultura raizal, el creole y platos como el *rondón*." . $demoNote,
                'meta' => ['activities' => ['Buceo y careteo', 'Vuelta a la isla', 'Visita a cayos', 'Gastronomía raizal'], 'altitude' => 'Nivel del mar · clima cálido']],
            ['key' => 'villa-de-leyva', 'section' => 'tourism', 'title' => 'Villa de Leyva', 'dep' => 'boyaca', 'mun' => 'villa-de-leyva', 'lat' => 5.6333, 'lng' => -73.5236,
                'cats' => ['Patrimonio', 'Cultura', 'Turismo rural'], 'cover' => 'villa-de-leyva', 'gallery' => ['villa-de-leyva', 'mercado'], 'featured' => false,
                'summary' => 'Casas blancas, calles empedradas y una de las plazas más grandes del país.',
                'body' => "Un pueblo colonial rodeado de paisajes semidesérticos, fósiles, viñedos y pozos de agua azul. Ideal para recorrer sin prisa." . $demoNote,
                'meta' => ['activities' => ['Recorrido por la plaza mayor', 'Museo paleontológico', 'Ciclismo', 'Mercado campesino'], 'altitude' => '2.150 m · clima templado frío']],
            ['key' => 'barichara', 'section' => 'tourism', 'title' => 'Barichara y el Camino Real', 'dep' => 'santander', 'mun' => 'barichara', 'lat' => 6.6347, 'lng' => -73.2231,
                'cats' => ['Patrimonio', 'Turismo rural', 'Aventura'], 'cover' => 'barichara', 'gallery' => ['barichara', 'miel'], 'gem' => true,
                'summary' => 'Un pueblo de piedra y tapia pisada desde donde parte un antiguo camino hacia Guane.',
                'body' => "Barichara conserva su arquitectura en piedra y tierra. El Camino Real, empedrado, atraviesa el cañón hasta el pequeño pueblo de Guane en una caminata de pocas horas." . $demoNote,
                'meta' => ['activities' => ['Caminata del Camino Real', 'Talleres de papel artesanal', 'Miradores del cañón'], 'best_time' => 'Temprano en la mañana para caminar con fresco.']],
            ['key' => 'guatape', 'section' => 'tourism', 'title' => 'Guatapé y la Piedra del Peñol', 'dep' => 'antioquia', 'mun' => 'guatape', 'lat' => 6.2208, 'lng' => -75.1781,
                'cats' => ['Aventura', 'Cultura', 'Naturaleza'], 'cover' => 'guatape', 'gallery' => ['guatape'], 'featured' => true, 'order' => 8,
                'summary' => 'Zócalos de colores que cuentan historias y una roca gigante con vista a un mar de islas.',
                'body' => "Los zócalos, relieves pintados en la base de las casas, convierten a Guatapé en una galería al aire libre. Muy cerca, más de 700 escalones llevan a la cima de la Piedra del Peñol." . $demoNote,
                'meta' => ['activities' => ['Subir la Piedra del Peñol', 'Paseo en lancha por el embalse', 'Recorrer la calle de los zócalos']]],
            ['key' => 'san-agustin', 'section' => 'tourism', 'title' => 'Parque Arqueológico de San Agustín', 'dep' => 'huila', 'mun' => 'san-agustin', 'lat' => 1.8858, 'lng' => -76.2969,
                'cats' => ['Patrimonio', 'Cultura', 'Naturaleza'], 'cover' => 'san-agustin', 'gallery' => ['san-agustin'],
                'summary' => 'Cientos de esculturas monolíticas talladas por una cultura ancestral, en medio de montañas verdes.',
                'body' => "Declarado Patrimonio de la Humanidad, el parque reúne estatuas, tumbas y montículos funerarios. Los alrededores guardan el estrecho del Magdalena y otros sitios arqueológicos." . $demoNote,
                'meta' => ['activities' => ['Recorrido arqueológico', 'Estrecho del Magdalena', 'Cabalgatas a sitios cercanos']]],
            ['key' => 'leticia', 'section' => 'tourism', 'title' => 'Leticia y el río Amazonas', 'dep' => 'amazonas', 'mun' => 'leticia', 'lat' => -4.2153, 'lng' => -69.9406,
                'cats' => ['Naturaleza', 'Avistamiento de fauna', 'Cultura'], 'cover' => 'amazonas', 'gallery' => ['amazonas', 'amazonas-vertical'], 'featured' => true, 'order' => 9,
                'summary' => 'La puerta a la selva amazónica, en la triple frontera con Brasil y Perú.',
                'body' => "Desde Leticia se navega el Amazonas para conocer lagos con delfines rosados, comunidades indígenas y la reserva de selva. Al atardecer, miles de garzas llegan al Parque Santander." . $demoNote,
                'meta' => ['activities' => ['Navegación por el Amazonas', 'Avistamiento de delfines', 'Caminatas nocturnas guiadas', 'Visita a comunidades con operadores locales']]],
            ['key' => 'puerto-narino', 'section' => 'tourism', 'title' => 'Puerto Nariño, el pesebre natural', 'dep' => 'amazonas', 'mun' => 'puerto-narino', 'lat' => -3.7703, 'lng' => -70.3831,
                'cats' => ['Naturaleza', 'Avistamiento de fauna'], 'cover' => 'amazonas-vertical', 'gallery' => ['amazonas-vertical'], 'gem' => true,
                'summary' => 'Un pueblo sin carros, con senderos peatonales y lagos donde nadan delfines rosados.',
                'body' => "A orillas del río Loretoyacu, Puerto Nariño es un ejemplo de convivencia con la selva. Cerca están los lagos de Tarapoto." . $demoNote,
                'meta' => ['activities' => ['Lagos de Tarapoto', 'Mirador del pueblo', 'Avistamiento de delfines']]],
            ['key' => 'nuqui', 'section' => 'tourism', 'title' => 'Nuquí y las ballenas jorobadas', 'dep' => 'choco', 'mun' => 'nuqui', 'lat' => 5.7125, 'lng' => -77.27,
                'cats' => ['Naturaleza', 'Avistamiento de fauna', 'Playas'], 'cover' => 'pacifico', 'gallery' => ['pacifico'], 'gem' => true,
                'summary' => 'Selva que llega al mar, termales y la visita anual de las ballenas jorobadas.',
                'body' => "Entre julio y octubre, las ballenas jorobadas llegan al Pacífico colombiano para aparearse y dar a luz. Nuquí combina playas solitarias, cascadas y aguas termales." . $demoNote,
                'meta' => ['activities' => ['Avistamiento de ballenas', 'Termales', 'Surf', 'Caminatas a cascadas'], 'best_time' => 'Temporada de ballenas: julio a octubre.']],
            ['key' => 'mongui', 'section' => 'tourism', 'title' => 'Páramo de Ocetá, Monguí', 'dep' => 'boyaca', 'mun' => 'mongui', 'lat' => 5.7, 'lng' => -72.82,
                'cats' => ['Naturaleza', 'Aventura', 'Turismo rural'], 'cover' => 'paramo', 'gallery' => ['paramo'], 'gem' => true, 'featured' => true, 'order' => 10,
                'summary' => 'Un bosque de frailejones, lagunas y formaciones rocosas a más de 3.000 metros.',
                'body' => "Muchos lo consideran uno de los páramos más bellos del mundo. La caminata parte de Monguí, un pueblo boyacense famoso por sus balones artesanales." . $demoNote,
                'meta' => ['activities' => ['Caminata guiada al páramo', 'Lagunas de alta montaña', 'Taller de balones en Monguí'], 'altitude' => 'Más de 3.000 m · clima frío de páramo', 'recommendations' => ['Contrata guías locales', 'No pises ni arranques frailejones']]],
            ['key' => 'el-cocuy', 'section' => 'tourism', 'title' => 'Sierra Nevada de El Cocuy', 'dep' => 'boyaca', 'mun' => 'el-cocuy', 'lat' => 6.41, 'lng' => -72.33,
                'cats' => ['Naturaleza', 'Aventura'], 'cover' => 'nevado', 'gallery' => ['nevado'],
                'summary' => 'Picos nevados, lagunas glaciares y caminatas de alta montaña.',
                'body' => "El parque protege la mayor masa glaciar de Colombia. El ingreso está regulado y solo se permite por senderos autorizados para proteger el ecosistema y el territorio del pueblo U'wa." . $demoNote,
                'meta' => ['activities' => ['Senderismo de alta montaña', 'Lagunas glaciares'], 'altitude' => 'Hasta más de 5.000 m']],
            ['key' => 'medellin', 'section' => 'tourism', 'title' => 'Medellín, la ciudad de la eterna primavera', 'dep' => 'antioquia', 'mun' => 'medellin', 'lat' => 6.2476, 'lng' => -75.5658,
                'cats' => ['Ciudades', 'Cultura', 'Gastronomía'], 'cover' => 'medellin', 'gallery' => ['medellin', 'silleta-flores', 'bandeja-paisa'],
                'summary' => 'Metrocable, arte urbano, parques biblioteca y un clima amable todo el año.',
                'body' => "La transformación de Medellín se recorre en sus barrios, en el arte de la Comuna 13 y en el metrocable que sube a las montañas." . $demoNote,
                'meta' => ['activities' => ['Recorrido de arte urbano', 'Metrocable', 'Museos y parques', 'Feria de las Flores (agosto)']]],
            ['key' => 'mompox', 'section' => 'tourism', 'title' => 'Santa Cruz de Mompox', 'dep' => 'bolivar', 'mun' => 'mompos', 'lat' => 9.2386, 'lng' => -74.4269,
                'cats' => ['Patrimonio', 'Cultura'], 'cover' => 'barichara', 'gallery' => ['villa-de-leyva'], 'gem' => true,
                'summary' => 'Una ciudad detenida en el tiempo a orillas del río Magdalena, famosa por su filigrana.',
                'body' => "Mompox conserva su trazado colonial frente al río. Sus orfebres mantienen viva la filigrana en plata y su Semana Santa es una de las más tradicionales del país." . $demoNote,
                'meta' => ['activities' => ['Recorrido por el centro histórico', 'Talleres de filigrana', 'Paseo por la ciénaga de Pijiño']]],
            ['key' => 'paz-de-ariporo', 'section' => 'tourism', 'title' => 'Llanos de Casanare', 'dep' => 'casanare', 'mun' => 'paz-de-ariporo', 'lat' => 5.88, 'lng' => -71.89,
                'cats' => ['Turismo rural', 'Avistamiento de fauna', 'Naturaleza'], 'cover' => 'llanos', 'gallery' => ['llanos'], 'gem' => true,
                'summary' => 'Hatos llaneros, chigüiros, garzas y atardeceres que parecen no terminar.',
                'body' => "En los hatos de Casanare se vive la cultura llanera: trabajo de llano, coleo, joropo y una fauna que se deja ver en las sabanas inundables." . $demoNote,
                'meta' => ['activities' => ['Safari llanero', 'Avistamiento de aves', 'Vida en el hato', 'Joropo']]],
            ['key' => 'fin-del-mundo', 'section' => 'tourism', 'title' => 'Cascada Fin del Mundo', 'dep' => 'putumayo', 'mun' => 'mocoa', 'lat' => 1.1361, 'lng' => -76.6461,
                'cats' => ['Naturaleza', 'Aventura'], 'cover' => 'cascada', 'gallery' => ['cascada'], 'gem' => true,
                'summary' => 'Una caminata por la selva del piedemonte amazónico que termina en una caída de agua de unos 70 metros.',
                'body' => "El sendero pasa por varios pozos de agua cristalina antes de llegar al borde de la cascada. Es una de las experiencias de naturaleza más populares de Mocoa." . $demoNote,
                'meta' => ['activities' => ['Senderismo', 'Baño en pozos naturales'], 'recommendations' => ['Usa calzado con buen agarre', 'No te acerques al borde de la cascada']]],
            ['key' => 'la-cocha', 'section' => 'tourism', 'title' => 'Laguna de La Cocha', 'dep' => 'narino', 'mun' => 'pasto', 'lat' => 1.07, 'lng' => -77.15,
                'cats' => ['Naturaleza', 'Gastronomía', 'Turismo rural'], 'cover' => 'andes-amanecer', 'gallery' => ['andes-amanecer'], 'gem' => true,
                'summary' => 'Un gran lago andino con la isla de La Corota y casitas de colores en sus orillas.',
                'body' => "A media hora de Pasto, la laguna sorprende por su tamaño y su neblina. En El Encano se puede probar trucha y visitar el santuario de flora de la isla La Corota." . $demoNote,
                'meta' => ['activities' => ['Paseo en lancha', 'Isla La Corota', 'Gastronomía de trucha'], 'altitude' => '2.760 m · clima frío']],
            ['key' => 'bogota', 'section' => 'tourism', 'title' => 'Bogotá y La Candelaria', 'dep' => 'bogota-d-c', 'mun' => 'bogota-d-c', 'lat' => 4.5981, 'lng' => -74.0758,
                'cats' => ['Ciudades', 'Patrimonio', 'Cultura'], 'cover' => 'andes-violeta', 'gallery' => ['andes-violeta', 'ajiaco'],
                'summary' => 'Museos, cerros, barrios coloniales y una de las escenas culturales más intensas de América Latina.',
                'body' => "La Candelaria concentra museos, teatros y arte urbano. Desde los cerros orientales se observa la ciudad extendida sobre la sabana." . $demoNote,
                'meta' => ['activities' => ['Museos del centro', 'Subir a un cerro tutelar', 'Ciclovía dominical', 'Ajiaco santafereño']]],

            // ---------------- GASTRONOMÍA ----------------
            ['section' => 'gastronomy', 'title' => 'Bandeja paisa', 'dep' => 'antioquia', 'mun' => 'medellin', 'cats' => ['Platos típicos'], 'cover' => 'bandeja-paisa', 'gallery' => ['bandeja-paisa'], 'featured' => true, 'order' => 1,
                'summary' => 'El plato más abundante de Antioquia: fríjoles, arroz, chicharrón, huevo, aguacate, arepa y plátano.',
                'body' => 'Nacida de la cocina campesina y arriera, la bandeja reúne en un solo plato la energía necesaria para largas jornadas en la montaña.',
                'meta' => ['dish_type' => 'Plato típico', 'ingredients' => ['Fríjoles rojos', 'Arroz blanco', 'Chicharrón', 'Carne molida', 'Chorizo', 'Huevo frito', 'Tajadas de plátano maduro', 'Aguacate', 'Arepa'], 'origin' => 'Antioquia y el Eje Cafetero', 'history' => 'Su forma actual se popularizó en el siglo XX, pero sus ingredientes provienen de la alimentación arriera.']],
            ['section' => 'gastronomy', 'title' => 'Ajiaco santafereño', 'dep' => 'bogota-d-c', 'mun' => 'bogota-d-c', 'cats' => ['Platos típicos', 'Recetas'], 'cover' => 'ajiaco', 'gallery' => ['ajiaco'], 'featured' => true, 'order' => 2,
                'summary' => 'Sopa espesa de tres papas, pollo, mazorca y guascas, servida con crema, alcaparras y aguacate.',
                'meta' => ['dish_type' => 'Receta', 'ingredients' => ['Papa criolla', 'Papa pastusa', 'Papa sabanera', 'Pechuga de pollo', 'Mazorca', 'Guascas', 'Crema de leche', 'Alcaparras', 'Aguacate'],
                    'preparation' => ['Cocina el pollo con cebolla larga, ajo y sal.', 'Agrega las papas en trozos y las mazorcas.', 'Cuando la papa criolla se deshaga y espese, añade las guascas.', 'Desmecha el pollo y sirve con crema, alcaparras y aguacate.'],
                    'origin' => 'Bogotá y el altiplano cundiboyacense']],
            ['section' => 'gastronomy', 'title' => 'Arepa de huevo', 'dep' => 'atlantico', 'mun' => 'luruaco', 'cats' => ['Platos típicos', 'Tradiciones culinarias'], 'cover' => 'arepas', 'gallery' => ['arepas'], 'featured' => true, 'order' => 3,
                'summary' => 'Arepa frita, inflada y rellena con un huevo entero: un clásico de la costa Caribe.',
                'meta' => ['dish_type' => 'Plato típico', 'ingredients' => ['Masa de maíz', 'Huevos', 'Sal', 'Aceite para freír'], 'origin' => 'Costa Caribe; Luruaco celebra un festival dedicado a ella.']],
            ['section' => 'gastronomy', 'title' => 'Sancocho de gallina', 'dep' => 'valle-del-cauca', 'mun' => 'cali', 'cats' => ['Platos típicos', 'Tradiciones culinarias'], 'cover' => 'sancocho', 'gallery' => ['sancocho'],
                'summary' => 'La sopa del encuentro: se cocina en leña, en olla grande y en familia.',
                'meta' => ['dish_type' => 'Plato típico', 'ingredients' => ['Gallina criolla', 'Plátano verde', 'Yuca', 'Papa', 'Mazorca', 'Cilantro', 'Cebolla larga'], 'history' => 'Cada región tiene su versión: de gallina, de pescado, trifásico o de costilla.']],
            ['section' => 'gastronomy', 'title' => 'Empanadas de pipián', 'dep' => 'cauca', 'mun' => 'popayan', 'cats' => ['Platos típicos'], 'cover' => 'empanadas', 'gallery' => ['empanadas'], 'gem' => true,
                'summary' => 'Pequeñas empanadas rellenas de papa colorada y maní, acompañadas con ají de maní.',
                'meta' => ['dish_type' => 'Plato típico', 'ingredients' => ['Masa de maíz', 'Papa colorada', 'Maní tostado', 'Hogao', 'Ají de maní'], 'origin' => 'Popayán, ciudad UNESCO de la gastronomía.']],
            ['section' => 'gastronomy', 'title' => 'Frutas que solo conocerás aquí', 'cats' => ['Ingredientes', 'Bebidas'], 'cover' => 'frutas', 'gallery' => ['frutas', 'jugos'], 'featured' => true, 'order' => 4,
                'summary' => 'Lulo, maracuyá, pitahaya, guanábana, uchuva y borojó: un arcoíris de sabores tropicales.',
                'body' => 'La diversidad de climas hace de Colombia uno de los países con más frutas del mundo. Pruébalas en jugos, salpicones o directamente en las plazas de mercado.',
                'meta' => ['dish_type' => 'Ingrediente', 'ingredients' => ['Lulo', 'Maracuyá', 'Pitahaya', 'Guanábana', 'Uchuva', 'Borojó', 'Mango']]],
            ['section' => 'gastronomy', 'title' => 'Cazuela de mariscos', 'dep' => 'bolivar', 'mun' => 'cartagena-de-indias', 'cats' => ['Platos típicos'], 'cover' => 'cazuela', 'gallery' => ['cazuela'],
                'summary' => 'Mariscos en salsa cremosa con leche de coco: el sabor del Caribe en una olla de barro.',
                'meta' => ['dish_type' => 'Plato típico', 'ingredients' => ['Camarones', 'Calamar', 'Mejillones', 'Pescado', 'Leche de coco', 'Ají dulce', 'Cebolla', 'Ajo']]],
            ['section' => 'gastronomy', 'title' => 'Tamal tolimense', 'dep' => 'tolima', 'mun' => 'ibague', 'cats' => ['Platos típicos', 'Tradiciones culinarias'], 'cover' => 'tamal', 'gallery' => ['tamal'], 'gem' => true,
                'summary' => 'Masa de arroz y maíz con carnes, huevo y verduras, envuelta en hoja de plátano.',
                'meta' => ['dish_type' => 'Plato típico', 'ingredients' => ['Masa de maíz y arroz', 'Pollo', 'Cerdo', 'Huevo', 'Zanahoria', 'Arveja', 'Hoja de plátano'], 'history' => 'Es protagonista del desayuno de domingo, acompañado de chocolate y arepa.']],
            ['section' => 'gastronomy', 'title' => 'Lulada caleña', 'dep' => 'valle-del-cauca', 'mun' => 'cali', 'cats' => ['Bebidas'], 'cover' => 'jugos', 'gallery' => ['jugos'],
                'summary' => 'Lulo picado, limón, azúcar y hielo: la bebida refrescante del Valle del Cauca.',
                'meta' => ['dish_type' => 'Bebida', 'ingredients' => ['Lulo', 'Limón', 'Azúcar', 'Hielo', 'Agua'], 'preparation' => ['Pica el lulo en trozos pequeños sin licuar.', 'Agrega jugo de limón y azúcar al gusto.', 'Completa con agua y hielo y mezcla.']]],

            // ---------------- CAFÉ ----------------
            ['section' => 'coffee', 'title' => 'Siembra y cultivo', 'cats' => ['Proceso'], 'cover' => 'paisaje-cafetero-2', 'summary' => 'El café arábico crece en las laderas andinas, entre sombra, montaña y lluvias bien distribuidas.', 'meta' => ['step' => '1']],
            ['section' => 'coffee', 'title' => 'Cosecha a mano', 'cats' => ['Proceso'], 'cover' => 'cerezas-cafe', 'summary' => 'Recolectores seleccionan solo las cerezas maduras, grano por grano, en las laderas.', 'meta' => ['step' => '2']],
            ['section' => 'coffee', 'title' => 'Despulpado y fermentación', 'cats' => ['Proceso'], 'cover' => 'granos-cafe', 'summary' => 'Se retira la pulpa y el grano fermenta para desarrollar sus sabores; luego se lava.', 'meta' => ['step' => '3']],
            ['section' => 'coffee', 'title' => 'Secado al sol', 'cats' => ['Proceso'], 'cover' => 'granos-cafe', 'summary' => 'El café pergamino se seca en patios, marquesinas o silos hasta alcanzar la humedad ideal.', 'meta' => ['step' => '4']],
            ['section' => 'coffee', 'title' => 'Tostión', 'cats' => ['Proceso'], 'cover' => 'metodo-filtrado', 'summary' => 'El calor transforma el grano verde en aromas de chocolate, frutas, caramelo o flores.', 'meta' => ['step' => '5']],
            ['section' => 'coffee', 'title' => 'La taza', 'cats' => ['Proceso', 'Preparación'], 'cover' => 'taza-cafe', 'summary' => 'Molienda, agua y método: el último paso de un largo viaje desde la montaña.', 'meta' => ['step' => '6']],
            ['key' => 'paisaje-cafetero', 'section' => 'coffee', 'title' => 'Paisaje Cultural Cafetero', 'dep' => 'caldas', 'mun' => 'chinchina', 'lat' => 4.98, 'lng' => -75.6, 'cats' => ['Rutas cafeteras'], 'cover' => 'paisaje-cafetero', 'gallery' => ['paisaje-cafetero', 'paisaje-cafetero-2', 'cerezas-cafe'], 'featured' => true, 'order' => 1,
                'summary' => 'Patrimonio de la Humanidad desde 2011: montañas, pueblos de bahareque y la cultura del café.',
                'body' => 'El paisaje abarca zonas de Caldas, Quindío, Risaralda y Valle del Cauca. Su valor está tanto en el territorio como en la tradición de las familias caficultoras.',
                'meta' => ['altitude' => 'Entre 1.000 y 2.000 m', 'process' => 'Predomina el beneficio lavado']],
            ['section' => 'coffee', 'title' => 'Finca La Esperanza (demo)', 'dep' => 'quindio', 'mun' => 'filandia', 'lat' => 4.68, 'lng' => -75.66, 'cats' => ['Fincas', 'Catas'], 'cover' => 'cerezas-cafe', 'gallery' => ['cerezas-cafe', 'granos-cafe', 'taza-cafe'], 'featured' => true, 'order' => 2,
                'summary' => 'Ficha de DEMOSTRACIÓN para mostrar cómo se ve una finca cafetera con recorrido y cata.',
                'body' => "Esta finca no existe: es un ejemplo para que el equipo editorial vea cómo se presenta una ficha completa.\n\n## Experiencia\nRecorrido por el cultivo, recolección, beneficio y cata guiada.",
                'meta' => ['altitude' => '1.850 m', 'varieties' => 'Castillo, Caturra', 'process' => 'Lavado', 'tasting_notes' => 'Panela, naranja, chocolate', 'contact_email' => 'finca@example.com', 'address' => 'Dirección de ejemplo']],
            ['section' => 'coffee', 'title' => 'El tinto de todos los días', 'cats' => ['Preparación'], 'cover' => 'taza-cafe', 'gallery' => ['taza-cafe', 'metodo-filtrado'],
                'summary' => 'Así llamamos al café negro que se ofrece en casas, plazas y oficinas como gesto de bienvenida.',
                'meta' => ['tasting_notes' => 'Depende del origen y la tostión']],

            // ---------------- EMPRENDIMIENTO (DEMO) ----------------
            ['section' => 'entrepreneurship', 'title' => 'Barro y Fuego (demo)', 'dep' => 'boyaca', 'mun' => 'raquira', 'lat' => 5.54, 'lng' => -73.63, 'cats' => ['Artesanías'], 'cover' => 'ceramica', 'gallery' => ['ceramica'], 'featured' => true,
                'summary' => 'Emprendimiento de DEMOSTRACIÓN: taller ficticio de cerámica para mostrar la ficha de un emprendimiento.',
                'meta' => ['products' => ['Vasijas utilitarias', 'Talleres de modelado', 'Piezas decorativas'], 'contact_name' => 'Nombre de ejemplo', 'contact_email' => 'contacto@example.com', 'website' => 'https://example.com']],
            ['section' => 'entrepreneurship', 'title' => 'Hilos del Viento (demo)', 'dep' => 'cauca', 'mun' => 'silvia', 'lat' => 2.61, 'lng' => -76.38, 'cats' => ['Artesanías', 'Productos culturales'], 'cover' => 'tejidos', 'gallery' => ['tejidos'],
                'summary' => 'Emprendimiento de DEMOSTRACIÓN: colectivo ficticio de tejido para ilustrar la sección.',
                'meta' => ['products' => ['Ruanas', 'Mochilas', 'Tapices'], 'contact_email' => 'contacto@example.com']],
            ['section' => 'entrepreneurship', 'title' => 'Mercado de la Vereda (demo)', 'dep' => 'boyaca', 'mun' => 'villa-de-leyva', 'cats' => ['Agroindustria', 'Gastronomía'], 'cover' => 'mercado', 'gallery' => ['mercado'],
                'summary' => 'Emprendimiento de DEMOSTRACIÓN: mercado campesino ficticio para mostrar filtros y búsqueda.',
                'meta' => ['products' => ['Frutas y verduras', 'Lácteos', 'Conservas']]],
            ['section' => 'entrepreneurship', 'title' => 'Miel del Cañón (demo)', 'dep' => 'santander', 'mun' => 'barichara', 'cats' => ['Agroindustria'], 'cover' => 'miel', 'gallery' => ['miel'],
                'summary' => 'Emprendimiento de DEMOSTRACIÓN: apiario ficticio con productos derivados de la miel.',
                'meta' => ['products' => ['Miel', 'Polen', 'Propóleo'], 'instagram' => 'ejemplo']],
            ['section' => 'entrepreneurship', 'title' => 'Origen Alto (demo)', 'dep' => 'quindio', 'mun' => 'salento', 'cats' => ['Café', 'Turismo'], 'cover' => 'taza-cafe', 'gallery' => ['taza-cafe', 'granos-cafe'],
                'summary' => 'Emprendimiento de DEMOSTRACIÓN: tostadora ficticia de cafés especiales con experiencias de cata.',
                'meta' => ['products' => ['Café tostado', 'Catas guiadas', 'Barismo básico'], 'website' => 'https://example.com']],

            // ---------------- CULTURA ----------------
            ['section' => 'culture', 'title' => 'Carnaval de Barranquilla', 'dep' => 'atlantico', 'mun' => 'barranquilla', 'lat' => 10.9685, 'lng' => -74.7813, 'cats' => ['Festividades', 'Danza', 'Música', 'Patrimonio'], 'cover' => 'carnaval', 'gallery' => ['carnaval', 'pollera'], 'featured' => true, 'order' => 1,
                'event_start' => '2027-02-06 09:00:00', 'event_end' => '2027-02-09 23:00:00',
                'summary' => 'Obra Maestra del Patrimonio Oral e Inmaterial de la Humanidad: cuatro días de danza, disfraces y música.',
                'body' => "Marimondas, congos, cumbia, mapalé y letanías llenan las calles durante los días previos al Miércoles de Ceniza.\n\n> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['venue' => 'Vía 40 y calles de Barranquilla', 'community' => 'Hacedores del Carnaval, comparsas y cumbiambas', 'schedule' => 'Sábado a martes antes del Miércoles de Ceniza']],
            ['section' => 'culture', 'title' => 'Carnaval de Negros y Blancos', 'dep' => 'narino', 'mun' => 'pasto', 'lat' => 1.2136, 'lng' => -77.2811, 'cats' => ['Festividades', 'Patrimonio'], 'cover' => 'carnaval', 'gallery' => ['carnaval', 'andes-amanecer'], 'featured' => true, 'order' => 2,
                'event_start' => '2027-01-02 09:00:00', 'event_end' => '2027-01-07 23:00:00',
                'summary' => 'Carrozas monumentales, juego con talco y un mensaje de convivencia en el sur andino.',
                'body' => "Reconocido como Patrimonio Cultural Inmaterial de la Humanidad, el carnaval celebra la diversidad con desfiles y artesanos que trabajan todo el año.\n\n> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['venue' => 'Senda del Carnaval, Pasto', 'schedule' => 'Del 2 al 7 de enero']],
            ['section' => 'culture', 'title' => 'Feria de las Flores', 'dep' => 'antioquia', 'mun' => 'medellin', 'lat' => 6.2442, 'lng' => -75.5812, 'cats' => ['Festividades', 'Tradiciones'], 'cover' => 'silleta-flores', 'gallery' => ['silleta-flores'],
                'event_start' => '2027-07-30 09:00:00', 'event_end' => '2027-08-08 23:00:00',
                'summary' => 'El desfile de silleteros lleva sobre la espalda arreglos florales de hasta 70 kilos.',
                'body' => "La tradición silletera nació en el corregimiento de Santa Elena y hoy es Patrimonio Cultural de la Nación.\n\n> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['community' => 'Silleteros de Santa Elena', 'schedule' => 'Primeras semanas de agosto']],
            ['section' => 'culture', 'title' => 'Festival de la Leyenda Vallenata', 'dep' => 'cesar', 'mun' => 'valledupar', 'lat' => 10.4631, 'lng' => -73.2532, 'cats' => ['Festividades', 'Música'], 'cover' => 'musica', 'gallery' => ['musica'], 'featured' => true, 'order' => 3,
                'event_start' => '2027-04-28 09:00:00', 'event_end' => '2027-05-01 23:00:00',
                'summary' => 'Acordeón, caja y guacharaca compiten por la corona de rey vallenato.',
                'body' => "El vallenato tradicional fue inscrito por la UNESCO en la Lista de Salvaguardia Urgente. El festival reúne a juglares y nuevas generaciones.\n\n> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['venue' => 'Parque de la Leyenda Vallenata', 'schedule' => 'Finales de abril']],
            ['section' => 'culture', 'title' => 'Festival Petronio Álvarez', 'dep' => 'valle-del-cauca', 'mun' => 'cali', 'lat' => 3.4516, 'lng' => -76.532, 'cats' => ['Festividades', 'Música'], 'cover' => 'pacifico', 'gallery' => ['pacifico', 'musica'],
                'event_start' => '2027-08-12 16:00:00', 'event_end' => '2027-08-17 23:00:00',
                'summary' => 'Marimba, cantos y cocina del Pacífico se toman Cali en uno de los festivales afro más grandes de América.',
                'body' => "> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['community' => 'Agrupaciones del litoral Pacífico', 'schedule' => 'Agosto']],
            ['section' => 'culture', 'title' => 'Fiestas de la Independencia de Cartagena', 'dep' => 'bolivar', 'mun' => 'cartagena-de-indias', 'lat' => 10.4236, 'lng' => -75.5503, 'cats' => ['Festividades', 'Danza'], 'cover' => 'cartagena', 'gallery' => ['cartagena'],
                'event_start' => '2026-11-07 10:00:00', 'event_end' => '2026-11-11 23:00:00',
                'summary' => 'Cabildos, desfiles y música conmemoran la independencia de Cartagena del 11 de noviembre de 1811.',
                'body' => "> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['schedule' => 'Alrededor del 11 de noviembre']],
            ['section' => 'culture', 'title' => 'Noche de las Velitas', 'cats' => ['Tradiciones', 'Festividades'], 'cover' => 'tatacoa-noche', 'gallery' => ['tatacoa-noche'],
                'event_start' => '2026-12-07 18:00:00', 'event_end' => '2026-12-08 02:00:00',
                'summary' => 'La víspera del 8 de diciembre, familias de todo el país encienden velas y faroles para abrir la Navidad.',
                'meta' => ['schedule' => '7 de diciembre en la noche']],
            ['section' => 'culture', 'title' => 'Semana Santa en Popayán', 'dep' => 'cauca', 'mun' => 'popayan', 'lat' => 2.4418, 'lng' => -76.6063, 'cats' => ['Patrimonio', 'Tradiciones'], 'cover' => 'villa-de-leyva', 'gallery' => ['villa-de-leyva'],
                'event_start' => '2027-03-23 19:00:00', 'event_end' => '2027-03-27 23:00:00',
                'summary' => 'Procesiones nocturnas centenarias recorren la ciudad blanca; Patrimonio Inmaterial de la Humanidad.',
                'body' => "> Fechas de referencia: confirma la programación oficial.",
                'meta' => ['respect_note' => 'Es una celebración religiosa: guarda silencio durante las procesiones y no uses flash.']],
            ['section' => 'culture', 'title' => 'El sombrero vueltiao', 'dep' => 'cordoba', 'mun' => 'tuchin', 'lat' => 9.1856, 'lng' => -75.5531, 'cats' => ['Artesanías', 'Patrimonio'], 'cover' => 'sombrero-vueltiao', 'gallery' => ['sombrero-vueltiao'], 'featured' => true, 'order' => 4,
                'summary' => 'Trenzado en caña flecha por el pueblo zenú, es símbolo cultural de la nación.',
                'body' => 'El número de vueltas de trenza indica la finura del sombrero. Tuchín es uno de los centros artesanales más reconocidos.',
                'meta' => ['community' => 'Pueblo zenú', 'respect_note' => 'Compra directamente a artesanas y artesanos y valora el tiempo de elaboración.']],
            ['section' => 'culture', 'title' => 'Mochila wayuu', 'dep' => 'la-guajira', 'mun' => 'uribia', 'lat' => 11.7137, 'lng' => -72.2655, 'cats' => ['Artesanías', 'Tradiciones'], 'cover' => 'mochila-wayuu', 'gallery' => ['mochila-wayuu'],
                'summary' => 'Cada kanaas, o dibujo tejido, guarda un significado de la cosmovisión wayuu.',
                'body' => 'Según la tradición, el tejido fue enseñado por Wale\'kerü, la araña tejedora. Una mochila puede tardar semanas en terminarse.',
                'meta' => ['community' => 'Pueblo wayuu', 'respect_note' => 'Pide permiso antes de fotografiar y prefiere el comercio justo.']],
            ['section' => 'culture', 'title' => 'La cumbia', 'dep' => 'magdalena', 'mun' => 'el-banco', 'lat' => 9.0, 'lng' => -73.97, 'cats' => ['Música', 'Danza'], 'cover' => 'pollera', 'gallery' => ['pollera', 'musica'],
                'summary' => 'Tambores, gaitas y polleras que giran: un ritmo nacido del encuentro de culturas en el Caribe.',
                'body' => 'El Banco, Magdalena, celebra cada año el Festival Nacional de la Cumbia.',
                'meta' => ['schedule' => 'Festival Nacional de la Cumbia: mitad de año']],
            ['section' => 'culture', 'title' => 'Molas del pueblo guna dule', 'dep' => 'antioquia', 'mun' => 'necocli', 'cats' => ['Artesanías'], 'cover' => 'mola', 'gallery' => ['mola'], 'gem' => true,
                'summary' => 'Telas superpuestas y recortadas a mano forman figuras que hablan de la naturaleza y el mundo espiritual.',
                'meta' => ['community' => 'Pueblo guna dule', 'respect_note' => 'Las molas son parte de la indumentaria tradicional: valora su significado más allá de lo decorativo.']],

            // ---------------- NOTICIAS (DEMO) ----------------
            ['section' => 'news', 'title' => 'Bienvenidos a Voy por Colombia', 'cats' => ['Plataforma'], 'cover' => 'cano-cristales', 'featured' => true, 'days' => -1, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Noticia de demostración: así funciona la plataforma para descubrir la diversidad del país.',
                'body' => "Esta es una **noticia de demostración**. La plataforma reúne destinos, sabores, café, emprendimientos y cultura en un mapa interactivo con datos geográficos reales.\n\n## Qué puedes hacer\n- Explorar el mapa por departamento y municipio.\n- Guardar tus lugares favoritos.\n- Armar tu propia ruta y compartirla.\n\nTodo el contenido se administra desde el panel privado."],
            ['section' => 'news', 'title' => 'Temporada de ballenas en el Pacífico', 'dep' => 'choco', 'mun' => 'nuqui', 'cats' => ['Turismo', 'Territorios'], 'cover' => 'pacifico', 'days' => -4, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Noticia de demostración: entre julio y octubre las ballenas jorobadas visitan la costa pacífica.',
                'body' => "Las ballenas jorobadas recorren miles de kilómetros desde aguas australes. Elige operadores que respeten las distancias de observación.\n\nTexto de demostración."],
            ['section' => 'news', 'title' => 'Cinco joyas poco conocidas para tu próximo viaje', 'cats' => ['Turismo', 'Historias'], 'cover' => 'paramo', 'days' => -8, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Noticia de demostración: páramos, pueblos sin carros y cascadas que merecen estar en tu lista.',
                'body' => "- Páramo de Ocetá, en Monguí.\n- Puerto Nariño, en el Amazonas.\n- Nuquí, en el Chocó.\n- Mompox, a orillas del Magdalena.\n- La Cascada Fin del Mundo, en Mocoa.\n\nTexto de demostración."],
            ['section' => 'news', 'title' => 'Calendario de festividades: fechas para agendar', 'cats' => ['Festividades', 'Eventos'], 'cover' => 'carnaval', 'days' => -12, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Noticia de demostración: carnavales, ferias y festivales que se viven en todo el país.',
                'body' => "Consulta el calendario de la sección Cultura. Las fechas publicadas son de referencia y deben confirmarse con los organizadores.\n\nTexto de demostración."],
            ['section' => 'news', 'title' => 'Turismo respetuoso con las comunidades', 'cats' => ['Historias', 'Territorios'], 'cover' => 'mochila-wayuu', 'days' => -20, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Noticia de demostración: recomendaciones para viajar valorando los territorios y sus saberes.',
                'body' => "- Contrata guías y operadores locales.\n- Pide permiso antes de fotografiar personas.\n- Compra directamente a artesanos y productores.\n- Lleva tus residuos de regreso.\n\nTexto de demostración."],
            ['section' => 'news', 'title' => 'Nueva ruta por el Eje Cafetero (programada)', 'cats' => ['Turismo'], 'cover' => 'paisaje-cafetero', 'days' => 3, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Noticia de demostración PROGRAMADA: se publicará automáticamente en unos días.',
                'body' => 'Ejemplo de publicación programada: no aparece en la página pública hasta su fecha.'],
            ['section' => 'news', 'title' => 'Borrador: guía de mercados campesinos', 'cats' => ['Territorios'], 'cover' => 'mercado', 'status' => 'draft', 'days' => 0, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Ejemplo de BORRADOR: solo visible en el panel y en la vista previa.', 'body' => 'Texto en preparación.'],
            ['section' => 'news', 'title' => 'Archivo: temporada de fin de año anterior', 'cats' => ['Eventos'], 'cover' => 'andes-amanecer', 'status' => 'archived', 'days' => -300, 'author' => 'Equipo editorial (demo)',
                'summary' => 'Ejemplo de publicación ARCHIVADA: ya no aparece en la página pública.', 'body' => 'Contenido archivado.'],
        ];
    }
}
