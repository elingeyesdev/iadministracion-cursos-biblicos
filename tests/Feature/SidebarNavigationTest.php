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
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // Desktop sidebar
        $response->assertSee('class="app-chrome d-none d-lg-block sidebar', false);
        // Mobile offcanvas
        $response->assertSee('id="sidebarOffcanvas"', false);
        // Theme toggle
        $response->assertSee('data-theme-toggle', false);
        // Persistencia del estado del menú (sidebar.js usa localStorage 'church-sidebar')
        $response->assertSee('church-sidebar', false);
    }

    public function test_sidebar_conserva_todas_las_opciones_de_navegacion(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // Texto visible de las secciones principales
        $response->assertSee('Principal', false);
        $response->assertSee('Acceso y Seguridad', false);
        $response->assertSee('Parametrizaci', false); // Parametrización (con acento)
        $response->assertSee('Transaccional', false);
        $response->assertSee('Reporte', false);

        // Rutas reales intactas
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('miembros.index'), false);
        $response->assertSee(route('reportes.index'), false);
    }

    public function test_sidebar_incluye_logo_y_titulo(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // Logo y título en la cabecera del sidebar (church layout)
        $response->assertSee('sidebar-logo', false);
        $response->assertSee('sidebar-brand-text', false);
        $response->assertSee('Sistema Iglesia', false);
        $response->assertSee('Administraci', false); // Administración
    }

    public function test_submenu_de_usuario_es_flotante_y_reutiliza_el_logout(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // User menu wrap (Alpine.js component)
        $response->assertSee('id="user-menu-wrap"', false);
        $response->assertSee('x-data="userMenu"', false);
        $response->assertSee('user-menu-avatar', false);
        $response->assertSee('user-menu-text', false);
        $response->assertSee('Cerrar sesión', false); // Cerrar sesión
        // El cierre de sesión reutiliza la ruta y el formulario existentes
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee('name="_token"', false);
    }

    public function test_submenu_de_usuario_inicia_cerrado(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // El menú de usuario no debe tener la clase 'open' inicialmente (controlado por Alpine x-cloak)
        $response->assertDontSee('user-menu-wrap open', false);
        // El botón trigger tiene aria-expanded dinámico (controlado por Alpine)
        // Verificamos que el componente Alpine está presente con x-data
        $response->assertSee('x-data="userMenu"', false);
    }
}