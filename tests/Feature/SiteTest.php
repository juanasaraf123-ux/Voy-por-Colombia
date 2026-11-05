<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::$withDemo = true;
        $this->seed(DatabaseSeeder::class);
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $f) {
            File::delete(public_path('uploads/' . $f));
        }
        parent::tearDown();
    }

    private function user(string $role = 'admin', bool $active = true): User
    {
        return User::create(['name' => ucfirst($role) . ' Prueba', 'email' => $role . '@test.co', 'username' => $role . 'test', 'password' => 'Clave12345x', 'role' => $role, 'is_active' => $active]);
    }

    public function test_paginas_publicas_responden(): void
    {
        foreach (['/', '/turismo', '/gastronomia', '/cafe', '/emprendimiento', '/cultura', '/noticias', '/galeria', '/buscar?q=cafe', '/rutas', '/favoritos', '/mi-ruta', '/departamentos/meta', '/turismo/cano-cristales-el-rio-de-los-cinco-colores'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get('/no-existe-esta-pagina')->assertNotFound();
        $this->get('/turismo/no-existe')->assertNotFound();
    }

    public function test_la_portada_trae_el_mapa_completo(): void
    {
        $this->get('/')->assertInertia(fn ($p) => $p->component('Home')->has('markers', 48)->has('routes', 3)->has('chips')->where('texts.title', 'Colombia te espera'));
    }

    public function test_api_del_mapa(): void
    {
        $this->getJson('/api/mapa')->assertOk()->assertJsonStructure(['markers' => [['id', 'lat', 'lng', 'title', 'media']], 'counts']);
        $this->getJson('/api/departamentos/meta')->assertOk()->assertJsonPath('name', 'Meta');
        $this->getJson('/api/departamentos/meta/municipios/la-macarena')->assertOk()->assertJsonCount(1, 'items');
        $this->getJson('/api/rutas')->assertOk()->assertJsonCount(3, 'routes');
        $id = Entry::where('slug', 'like', 'cano-cristales%')->value('id');
        $this->getJson("/api/publicaciones/$id/multimedia")->assertOk()->assertJsonCount(1, 'videos')->assertJsonCount(1, 'audios');
    }

    public function test_busqueda_sin_tildes_y_con_simbolos(): void
    {
        $this->getJson('/api/buscar?q=cano cristales')->assertOk()->assertJsonPath('results.0.title', 'Caño Cristales, el río de los cinco colores');
        $this->getJson('/api/buscar?q=%25%25%25')->assertOk()->assertJsonCount(0, 'results');
        $this->getJson("/api/buscar?q=' OR 1=1 --")->assertOk();
    }

    public function test_borradores_y_programadas_no_son_publicas(): void
    {
        $e = Entry::where('section', 'tourism')->first();
        $e->update(['status' => 'draft']);
        $this->get($e->path())->assertNotFound();
        $this->getJson("/api/publicaciones/{$e->id}/multimedia")->assertNotFound();
        $e->update(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->get($e->path())->assertNotFound();
        $e->update(['published_at' => now()->subMinute()]);
        $this->get($e->path())->assertOk();
    }

    public function test_el_panel_exige_sesion(): void
    {
        $this->user();
        foreach (['/admin', '/admin/contenido/turismo', '/admin/multimedia', '/admin/usuarios', '/admin/configuracion'] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
        $this->post('/admin/contenido/turismo/nuevo', ['title' => 'x'])->assertRedirect('/admin/login');
        $this->getJson('/admin/api/multimedia')->assertUnauthorized();
    }

    public function test_inicio_de_sesion_y_bloqueo_por_intentos(): void
    {
        $this->user();
        $this->post('/admin/login', ['login' => 'admintest', 'password' => 'mala-clave'])->assertSessionHasErrors('login');
        $this->assertGuest();
        $codes = [];
        for ($i = 0; $i < 6; $i++) {
            $r = $this->post('/admin/login', ['login' => 'admintest', 'password' => 'otra-mala']);
            $codes[] = $r->status();
        }
        // 5 intentos por cuenta cada 15 minutos (el primero se hizo arriba).
        $this->assertSame([302, 302, 302, 302, 429, 429], $codes);
        $this->post('/admin/login', ['login' => 'admintest', 'password' => 'Clave12345x'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_inicio_de_sesion_correcto(): void
    {
        $this->user();
        $this->post('/admin/login', ['login' => 'ADMIN@test.co', 'password' => 'Clave12345x'])->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_primera_cuenta_solo_sin_cuentas_y_desde_el_mismo_equipo(): void
    {
        $this->get('/admin/instalar', ['X-Forwarded-For' => '8.8.8.8'])->assertNotFound();
        $this->withServerVariables(['REMOTE_ADDR' => '200.1.1.1'])->get('/admin/instalar')->assertNotFound();
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
        $this->get('/admin/instalar')->assertOk();
        $this->post('/admin/instalar', ['name' => 'A', 'email' => 'a@a.co', 'username' => 'aa', 'password' => 'corta', 'password_confirmation' => 'corta'])->assertSessionHasErrors(['password', 'username']);
        $this->post('/admin/instalar', ['name' => 'Ana', 'email' => 'ana@test.co', 'username' => 'ana', 'password' => 'Clave12345x', 'password_confirmation' => 'Clave12345x'])->assertRedirect('/admin');
        $this->assertSame('admin', User::first()->role);
        auth()->logout();
        $this->get('/admin/instalar')->assertNotFound();
        $this->get('/admin/login')->assertOk();
    }

    public function test_el_editor_no_administra_cuentas_ni_configuracion(): void
    {
        $editor = $this->user('editor');
        $this->actingAs($editor)->get('/admin/contenido/turismo')->assertOk();
        foreach (['/admin/usuarios', '/admin/configuracion', '/admin/auditoria'] as $path) {
            $this->actingAs($editor)->get($path)->assertForbidden();
        }
        $this->actingAs($editor)->post('/admin/usuarios/nuevo', ['name' => 'X', 'email' => 'x@x.co', 'username' => 'xx', 'role' => 'admin', 'password' => 'Clave12345x', 'password_confirmation' => 'Clave12345x'])->assertForbidden();
        $this->actingAs($editor)->post('/admin/configuracion/eliminar-demo', ['confirm' => 'ELIMINAR'])->assertForbidden();
        $this->assertSame(1, User::count());
    }

    public function test_una_cuenta_desactivada_pierde_la_sesion(): void
    {
        $editor = $this->user('editor', false);
        $this->actingAs($editor)->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_crear_publicar_y_escapar_html(): void
    {
        $admin = $this->user();
        $this->actingAs($admin)->post('/admin/contenido/turismo/nuevo', [
            'title' => 'Laguna de prueba', 'summary' => 'Resumen', 'body' => "## Hola\n<script>alert(1)</script> [x](javascript:alert(1))",
            'status' => 'published', 'department_id' => 9, 'lat' => 5.5, 'lng' => -73.4, 'show_on_map' => true, 'meta' => ['activities' => "Caminar\nNadar"],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $e = Entry::where('title', 'Laguna de prueba')->firstOrFail();
        $this->assertSame(['Caminar', 'Nadar'], $e->meta['activities']);
        $this->get($e->path())->assertOk()->assertInertia(fn ($p) => $p->component('Entry')
            ->where('entry.body', fn ($html) => !str_contains($html, '<script>') && str_contains($html, '&lt;script&gt;') && !str_contains($html, 'href="javascript')));
        $this->assertDatabaseHas('audit_log', ['action' => 'publish', 'entity_id' => $e->id]);
    }

    public function test_validaciones_del_formulario(): void
    {
        $admin = $this->user();
        $this->actingAs($admin)->post('/admin/contenido/turismo/nuevo', ['title' => 'x', 'status' => 'published', 'lat' => 40, 'lng' => -73, 'slug' => 'Mal Slug'])
            ->assertSessionHasErrors(['title', 'lat', 'slug']);
        $this->actingAs($admin)->post('/admin/contenido/turismo/nuevo', ['title' => 'Sin resumen', 'status' => 'published'])->assertSessionHasErrors(['summary']);
        $this->actingAs($admin)->post('/admin/contenido/turismo/nuevo', ['title' => 'Municipio ajeno', 'status' => 'draft', 'department_id' => 1, 'municipality_id' => 1100])->assertSessionHasErrors(['municipality_id']);
    }

    public function test_carga_de_archivos_segura(): void
    {
        $admin = $this->user();
        $fake = UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo "hola"; ?>');
        $this->actingAs($admin)->postJson('/admin/multimedia/subir', ['files' => [$fake]])->assertStatus(422)->assertJsonPath('items', []);
        $img = UploadedFile::fake()->image('paisaje.png', 1600, 900);
        $r = $this->actingAs($admin)->postJson('/admin/multimedia/subir', ['files' => [$img]])->assertOk()->assertJsonCount(1, 'items');
        $m = \App\Models\Media::find($r->json('items.0.id'));
        $this->assertSame('image/webp', $m->mime);
        $this->createdFiles = array_merge([$m->path], array_values($m->variantList()));
        $this->assertFileExists(public_path('uploads/' . $m->path));
        $this->assertStringEndsWith('.webp', $m->path);
        $this->createdFiles = array_unique($this->createdFiles);
    }

    public function test_quitar_contenido_de_demostracion(): void
    {
        $admin = $this->user();
        // No se borran archivos reales de la carpeta demo en la prueba: se desvinculan antes.
        \App\Models\Media::where('is_demo', true)->update(['path' => null, 'variants' => null]);
        $this->actingAs($admin)->post('/admin/configuracion/eliminar-demo', ['confirm' => 'NO'])->assertSessionHas('error');
        $this->actingAs($admin)->post('/admin/configuracion/eliminar-demo', ['confirm' => 'ELIMINAR'])->assertSessionHas('success');
        $this->assertSame(0, Entry::count());
        $this->get('/')->assertOk();
    }
}
