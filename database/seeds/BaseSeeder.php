<?php
declare(strict_types=1);

use App\Core\Database as DB;

/**
 * Datos base necesarios para que la plataforma funcione (no son de demostración):
 * roles, regiones naturales, departamentos, municipios, categorías y secciones.
 * Es idempotente: puede ejecutarse varias veces sin duplicar registros.
 */
final class BaseSeeder
{
    public const REGIONS = [
        ['caribe', 'Caribe', '#12b5b0', 'Mar, ciénagas, sabanas y una cultura que se baila.'],
        ['andina', 'Andina', '#2bb673', 'Montañas, páramos, café y pueblos coloniales.'],
        ['pacifica', 'Pacífica', '#1d6fe0', 'Selva húmeda, ballenas, marimba y sabores de mar.'],
        ['orinoquia', 'Orinoquía', '#ff7a1a', 'Llanuras infinitas, ríos y atardeceres de joropo.'],
        ['amazonia', 'Amazonía', '#0f5c4a', 'El pulmón verde: ríos, selva y saberes ancestrales.'],
        ['insular', 'Insular', '#7b3fe4', 'Islas de mar de siete colores y cultura raizal.'],
    ];

    /** slug => [región, capital, resumen] */
    public const DEPARTMENTS = [
        'amazonas' => ['amazonia', 'Leticia', 'Selva amazónica, el gran río y comunidades que conservan saberes milenarios.'],
        'antioquia' => ['andina', 'Medellín', 'Montañas, pueblos de zócalos coloridos, café y una capital que se reinventó.'],
        'arauca' => ['orinoquia', 'Arauca', 'Llano abierto, ríos caudalosos y la tradición del joropo.'],
        'atlantico' => ['caribe', 'Barranquilla', 'Tierra del Carnaval, la brisa caribeña y la desembocadura del Magdalena.'],
        'bogota-d-c' => ['andina', 'Bogotá', 'La capital: museos, cerros, barrios históricos y una escena cultural vibrante.'],
        'bolivar' => ['caribe', 'Cartagena de Indias', 'Ciudad amurallada, islas del Rosario y la memoria de Mompox.'],
        'boyaca' => ['andina', 'Tunja', 'Pueblos coloniales, páramos, lagunas sagradas y artesanía en barro.'],
        'caldas' => ['andina', 'Manizales', 'Paisaje Cultural Cafetero, nevados y aguas termales.'],
        'caqueta' => ['amazonia', 'Florencia', 'Puerta de la Amazonía, con ríos, cascadas y piedemonte.'],
        'casanare' => ['orinoquia', 'Yopal', 'Hatos llaneros, avistamiento de fauna y cielos inmensos.'],
        'cauca' => ['pacifica', 'Popayán', 'Ciudad blanca, culturas indígenas, volcanes y costa pacífica.'],
        'cesar' => ['caribe', 'Valledupar', 'Cuna del vallenato, a los pies de la Sierra Nevada.'],
        'choco' => ['pacifica', 'Quibdó', 'Selva, ballenas jorobadas y una de las regiones más biodiversas del planeta.'],
        'cordoba' => ['caribe', 'Montería', 'Sabanas, ríos y el arte zenú del sombrero vueltiao.'],
        'cundinamarca' => ['andina', 'Bogotá', 'Lagunas, minas de sal, pueblos de clima templado y páramos.'],
        'guainia' => ['amazonia', 'Inírida', 'Cerros de Mavecure, ríos de aguas negras y la flor de Inírida.'],
        'guaviare' => ['amazonia', 'San José del Guaviare', 'Pinturas rupestres, raudales y transición entre llano y selva.'],
        'huila' => ['andina', 'Neiva', 'San Agustín, el desierto de la Tatacoa y el nacimiento del Magdalena.'],
        'la-guajira' => ['caribe', 'Riohacha', 'Desierto que se encuentra con el mar y territorio del pueblo wayuu.'],
        'magdalena' => ['caribe', 'Santa Marta', 'Sierra Nevada, Tayrona y playas donde la selva toca el Caribe.'],
        'meta' => ['orinoquia', 'Villavicencio', 'Caño Cristales, llanuras y la cultura llanera.'],
        'narino' => ['pacifica', 'Pasto', 'Volcanes, la laguna de La Cocha y el Carnaval de Negros y Blancos.'],
        'norte-de-santander' => ['andina', 'Cúcuta', 'Historia de la independencia, pueblos de montaña y frontera.'],
        'putumayo' => ['amazonia', 'Mocoa', 'Cascadas, piedemonte amazónico y medicina tradicional.'],
        'quindio' => ['andina', 'Armenia', 'Valle de Cocora, palmas de cera y el corazón del Eje Cafetero.'],
        'risaralda' => ['andina', 'Pereira', 'Termales, café y bosques de niebla.'],
        'san-andres-y-providencia' => ['insular', 'San Andrés', 'Mar de siete colores, arrecifes y cultura raizal.'],
        'santander' => ['andina', 'Bucaramanga', 'Cañón del Chicamocha, aventura y pueblos de piedra.'],
        'sucre' => ['caribe', 'Sincelejo', 'Golfo de Morrosquillo, manglares y sabanas.'],
        'tolima' => ['andina', 'Ibagué', 'Capital musical, nevados y el tamal tolimense.'],
        'valle-del-cauca' => ['pacifica', 'Cali', 'Salsa, el Pacífico, sabores intensos y biodiversidad.'],
        'vaupes' => ['amazonia', 'Mitú', 'Selva profunda, raudales y culturas indígenas del Vaupés.'],
        'vichada' => ['orinoquia', 'Puerto Carreño', 'Parque Nacional Tuparro, ríos y sabanas del Orinoco.'],
    ];

    /** sección => [[nombre, icono, color], ...] */
    public const CATEGORIES = [
        'tourism' => [
            ['Naturaleza', 'leaf', '#2bb673'], ['Aventura', 'mountain', '#ff7a1a'], ['Playas', 'wave', '#12b5b0'],
            ['Patrimonio', 'columns', '#a8622f'], ['Turismo rural', 'farm', '#8fbf3a'], ['Gastronomía', 'fork', '#ff5a5f'],
            ['Cultura', 'mask', '#ff3f8e'], ['Avistamiento de fauna', 'bird', '#1d6fe0'], ['Ciudades', 'city', '#7b3fe4'],
            ['Experiencias comunitarias', 'users', '#ff7a1a'],
        ],
        'gastronomy' => [
            ['Platos típicos', 'fork', '#ff6b2c'], ['Bebidas', 'cup', '#12b5b0'], ['Postres y dulces', 'star', '#ff3f8e'],
            ['Ingredientes', 'leaf', '#2bb673'], ['Recetas', 'book', '#7b3fe4'], ['Tradiciones culinarias', 'fire', '#c4452f'],
            ['Experiencias', 'compass', '#1d6fe0'],
        ],
        'coffee' => [
            ['Fincas', 'farm', '#2f7a3a'], ['Productores', 'user', '#8a4b2a'], ['Proceso', 'cycle', '#c4452f'],
            ['Preparación', 'cup', '#6b3a1a'], ['Catas', 'star', '#ff7a1a'], ['Rutas cafeteras', 'route', '#0f9d77'],
            ['Emprendimientos cafeteros', 'store', '#7b3fe4'],
        ],
        'entrepreneurship' => [
            ['Turismo', 'compass', '#0f9d77'], ['Artesanías', 'hand', '#ff7a1a'], ['Gastronomía', 'fork', '#ff5a5f'],
            ['Café', 'coffee', '#8a4b2a'], ['Productos culturales', 'mask', '#ff3f8e'], ['Agroindustria', 'leaf', '#2bb673'],
        ],
        'culture' => [
            ['Festividades', 'party', '#ff3f8e'], ['Música', 'music', '#1d6fe0'], ['Danza', 'dance', '#ff7a1a'],
            ['Artesanías', 'hand', '#a8622f'], ['Patrimonio', 'columns', '#7b3fe4'], ['Tradiciones', 'fire', '#2bb673'],
            ['Historias locales', 'book', '#12b5b0'],
        ],
        'news' => [
            ['Turismo', 'compass', '#0f9d77'], ['Eventos', 'calendar', '#ff3f8e'], ['Festividades', 'party', '#ff7a1a'],
            ['Territorios', 'map', '#2bb673'], ['Historias', 'book', '#7b3fe4'], ['Plataforma', 'star', '#1d6fe0'],
        ],
    ];

    public const SECTIONS = [
        ['tourism', 'Turismo', 'Selva, mar, montaña y desierto en un solo mapa', 'Explora destinos por departamento y municipio, filtra por experiencias y arma tu ruta.'],
        ['gastronomy', 'Gastronomía', 'Un país que se cuenta en la mesa', 'Platos, bebidas, ingredientes y tradiciones de cada región.'],
        ['coffee', 'Café', 'Del grano a la taza', 'Fincas, productores, procesos, catas y rutas cafeteras.'],
        ['entrepreneurship', 'Emprendimiento', 'Hecho en los territorios', 'Proyectos locales de turismo, artesanía, gastronomía y café.'],
        ['culture', 'Cultura', 'Fiestas, música y saberes vivos', 'Festividades, danzas, artesanías y patrimonio de las comunidades.'],
        ['gallery', 'Galería de fotos', 'Colombia en imágenes', 'Paisajes, rostros y colores en un mosaico para recorrer.'],
        ['news', 'Noticias', 'Lo que está pasando en los territorios', 'Eventos, festividades, novedades e historias para viajar informado.'],
    ];

    public static function run(callable $log): void
    {
        DB::transaction(static function () use ($log) {
            foreach ([['admin', 'Administrador', 'Acceso total, incluida la gestión de usuarios y configuración.'], ['editor', 'Editor', 'Gestiona contenidos, multimedia, mapa y portada.']] as [$slug, $name, $desc]) {
                if (!DB::value('SELECT id FROM roles WHERE slug = ?', [$slug])) {
                    DB::insert('roles', ['slug' => $slug, 'name' => $name, 'description' => $desc]);
                }
            }
            foreach (self::REGIONS as $i => [$slug, $name, $color, $summary]) {
                if (!DB::value('SELECT id FROM regions WHERE slug = ?', [$slug])) {
                    DB::insert('regions', ['slug' => $slug, 'name' => $name, 'color' => $color, 'summary' => $summary, 'sort_order' => $i]);
                }
            }
            $regionIds = array_column(DB::all('SELECT id, slug FROM regions'), 'id', 'slug');

            $geo = json_decode((string) file_get_contents(PUBLIC_PATH . '/data/geo/departamentos.geojson'), true);
            foreach ($geo['features'] as $feature) {
                $p = $feature['properties'];
                [$region, $capital, $summary] = self::DEPARTMENTS[$p['slug']] ?? [null, null, null];
                if (!DB::value('SELECT id FROM departments WHERE slug = ?', [$p['slug']])) {
                    DB::insert('departments', [
                        'region_id' => $region ? (int) $regionIds[$region] : null,
                        'name' => $p['name'],
                        'slug' => $p['slug'],
                        'iso_code' => $p['iso'] ?: null,
                        'capital' => $capital,
                        'summary' => $summary,
                        'search_name' => search_normalize($p['name']),
                    ]);
                }
            }
            $log('Departamentos: ' . DB::value('SELECT COUNT(*) FROM departments'));

            $depIds = array_column(DB::all('SELECT id, slug FROM departments'), 'id', 'slug');
            $existing = [];
            foreach (DB::all('SELECT department_id, slug FROM municipalities') as $m) {
                $existing[$m['department_id'] . '/' . $m['slug']] = true;
            }
            $fh = fopen(BASE_PATH . '/database/data/municipios.csv', 'r');
            fgetcsv($fh, null, ',', '"', '');
            while (($row = fgetcsv($fh, null, ',', '"', '')) !== false) {
                [$dep, $name, $slug, $lat, $lng] = $row;
                $depId = $depIds[$dep] ?? null;
                if (!$depId || isset($existing["$depId/$slug"])) {
                    continue;
                }
                DB::insert('municipalities', [
                    'department_id' => (int) $depId, 'name' => $name, 'slug' => $slug,
                    'lat' => (float) $lat, 'lng' => (float) $lng, 'search_name' => search_normalize($name),
                ]);
            }
            fclose($fh);
            $log('Municipios: ' . DB::value('SELECT COUNT(*) FROM municipalities'));

            foreach (self::CATEGORIES as $section => $cats) {
                foreach ($cats as $i => [$name, $icon, $color]) {
                    $slug = slugify($name);
                    if (!DB::value('SELECT id FROM categories WHERE section = ? AND slug = ?', [$section, $slug])) {
                        DB::insert('categories', ['section' => $section, 'name' => $name, 'slug' => $slug, 'icon' => $icon, 'color' => $color, 'sort_order' => $i]);
                    }
                }
            }
            foreach (self::SECTIONS as $i => [$key, $title, $tagline, $desc]) {
                if (!DB::value('SELECT id FROM sections WHERE section_key = ?', [$key])) {
                    DB::insert('sections', ['section_key' => $key, 'title' => $title, 'tagline' => $tagline, 'description' => $desc, 'sort_order' => $i, 'show_on_home' => 1]);
                }
            }
            foreach ([['Paisajes', 'Montañas, mares, selvas y desiertos.'], ['Sabores', 'La cocina de las regiones.'], ['Cultura cafetera', 'Fincas, granos y tazas.'], ['Cultura viva', 'Fiestas, música y artesanías.'], ['Hecho en Colombia', 'Productos y oficios de los territorios.']] as $i => [$name, $desc]) {
                if (!DB::value('SELECT id FROM collections WHERE slug = ?', [slugify($name)])) {
                    DB::insert('collections', ['name' => $name, 'slug' => slugify($name), 'description' => $desc, 'sort_order' => $i, 'created_at' => now()]);
                }
            }
            $log('Roles, regiones, categorías, secciones y colecciones listos.');
        });
        self::brandMedia($log);
    }

    /** Imagen de portada de la marca (no es contenido de demostración). */
    private static function brandMedia(callable $log): void
    {
        $file = BASE_PATH . '/database/seeds/brand/portada-paisaje.webp';
        if (!is_file($file) || DB::value("SELECT id FROM media WHERE original_name = 'portada-paisaje.webp'")) {
            return;
        }
        $data = \App\Core\MediaProcessor::importLocal($file, 'portada-paisaje.webp');
        $id = DB::insert('media', $data + [
            'title' => 'Paisajes de Colombia',
            'alt_text' => 'Costa con aguas turquesa, selva, montañas y una cascada al atardecer',
            'credit' => 'Imagen de portada · Voy por Colombia',
            'in_gallery' => 0, 'gallery_featured' => 0, 'is_demo' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        if (!\App\Models\Setting::json('hero_media_ids')) {
            \App\Models\Setting::set('hero_media_ids', json_encode([$id]));
        }
        $log('Imagen de portada importada.');
    }
}
