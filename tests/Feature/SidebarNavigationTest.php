<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use DatabaseMigrations;

    private User $admin;

    private const ROUTE_REPORTES_MIEMBROS = '/reportes/miembros';

    protected function setUp(): void
    {
        parent::setUp();

        $rol = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['estado' => 'Activo', 'nivel_acceso' => 1]
        );

        $this->admin = User::factory()->create(['email' => 'sidebar_test@proyecto.com']);
        $this->admin->roles()->attach($rol);
    }

    protected function tearDown(): void
    {
        $this->admin?->roles()->detach();
        $this->admin?->forceDelete();
        parent::tearDown();
    }

    public function test_sidebar_tiene_boton_de_alternar_y_estado_persistente(): void
    {
        // Se usa una ruta que renderiza el layout y funciona sobre SQLite
        // (el dashboard usa DATE_FORMAT(), específico de MySQL).
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        $response->assertSee('id="app-sidebar"', false);
        $response->assertSee('id="sidebar-toggle"', false);
        $response->assertSee('aria-controls="app-sidebar"', false);
        // Persistencia del estado del menú reutilizando localStorage.
        $response->assertSee('actua_sidebar', false);
    }

    public function test_sidebar_conserva_todas_las_opciones_de_navegacion(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        $response->assertSee('data-title="Principal"', false);
        $response->assertSee('data-title="Acceso y Seguridad"', false);
        $response->assertSee('data-title="Parametrización"', false);
        $response->assertSee('data-title="Transaccional"', false);
        $response->assertSee('data-title="Reporte"', false);

        // Rutas reales intactas.
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('miembros.index'), false);
        $response->assertSee(route('reportes.index'), false);
    }

    public function test_sidebar_incluye_logo_compacto_y_avatar(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // Logo compacto para el estado contraído y avatar del usuario visible.
        $response->assertSee('class="logo-mark"', false);
        $response->assertSee('class="logo-text"', false);
        $response->assertSee('user-avatar', false);
    }

    public function test_submenu_de_usuario_es_flotante_y_reutiliza_el_logout(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        $response->assertSee('id="user-menu-wrap"', false);
        $response->assertSee('aria-haspopup="true"', false);
        $response->assertSee('aria-controls="user-menu"', false);
        $response->assertSee('id="user-menu"', false);
        $response->assertSee('role="menu"', false);
        $response->assertSee('role="menuitem"', false);
        $response->assertSee('Cerrar Sesión');
        // El cierre de sesión reutiliza la ruta y el formulario existentes.
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee('name="_token"', false);
    }

    public function test_submenu_de_usuario_inicia_cerrado(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // No debe abrirse automáticamente: el contenedor no empieza con la clase open.
        $response->assertDontSee('sidebar-user-wrap open', false);
        $response->assertSee('aria-expanded="false"', false);
    }
}
