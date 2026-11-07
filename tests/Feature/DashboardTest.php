<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermisosPorRolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private const TEXT_VER_TODAS = 'Ver todos';

    /**
     * Crea un usuario con el rol indicado para comprobar la visibilidad de accesos.
     */
    private function usuarioConRol(string $nombreRol): User
    {
        $user = User::factory()->create();

        $rol = Role::firstOrCreate(
            ['nombre' => $nombreRol],
            ['estado' => 'Activo', 'nivel_acceso' => 3]
        );

        $user->roles()->attach($rol->id);

        (new PermisosPorRolSeeder)->sembrarPara($rol);
        $user->limpiarPermisos();

        return $user;
    }

    /**
     * Datos representativos con la misma forma que entrega DashboardController.
     */
    private function datosDashboard(): array
    {
        return [
            'total_miembros' => 42,
            'miembros_activos' => 30,
            'total_cursos' => 6,
            'total_sesiones' => 25,
            'total_ministerios' => 5,
            'total_actividades' => 8,
            'actividades_en_curso' => 3,
            'actividades_completadas' => 5,
            'ultimas_actividades' => collect([
                (object) [
                    'nombre' => 'Ensayo general',
                    'fecha' => '2026-10-20',
                    'ministerio' => (object) ['nombre' => 'Alabanza'],
                    'estado' => 'programada',
                ],
            ]),
            'miembros_recientes' => collect([
                (object) [
                    'nombre' => 'Juan Pérez',
                    'email' => 'juan@proyecto.com',
                    'estado' => 'activo',
                ],
            ]),
            'total_ingresos' => 10000.0,
            'total_egresos' => 7000.0,
            'balance' => 3000.0,
            'contratos_activos' => 2,
            'ingresos_mes' => 1500.5,
            'egresos_mes' => 500.25,
            'balance_mes' => 1000.25,
            'top_cultos' => collect([
                (object) [
                    'nombre' => 'Culto Dominical',
                    'dia_semana' => 'domingo',
                    'hora' => '10:00:00',
                    'ingresos_sum_monto_total' => 1500.0,
                ],
            ]),
            'ultimos_ingresos' => collect([
                (object) [
                    'culto' => (object) ['nombre' => 'Culto Dominical'],
                    'fecha' => '2026-10-15',
                    'tipo' => 'Diezmo',
                    'monto_total' => 500.0,
                ],
            ]),
            'ultimos_egresos' => collect([
                (object) [
                    'tipo_egreso' => 'Servicios',
                    'fecha' => '2026-10-10',
                    'responsable' => 'Juan',
                    'monto' => 300.0,
                ],
            ]),
            'total_usuarios' => 10,
            'total_roles' => 4,
            'ultimos_usuarios' => collect([
                (object) [
                    'name' => 'Admin User',
                    'email' => 'admin@proyecto.com',
                    'roles' => collect([
                        (object) ['nombre' => 'Administrador'],
                    ]),
                ],
            ]),
        ];
    }

    public function test_el_dashboard_renderiza_las_secciones_principales(): void
    {
        $user = $this->usuarioConRol('Administrador');

        $this->actingAs($user)
            ->view('dashboard', $this->datosDashboard())
            // Encabezado
            ->assertSee('Dashboard', false)
            // Bloque 1: KPIs principales (todos los roles)
            ->assertSee('Total Miembros', false)
            ->assertSee('Miembros Activos', false)
            ->assertSee('Cursos', false)
            ->assertSee('Sesiones', false)
            // Bloque 2: Secretario/Admin
            ->assertSee('Organización', false)
            ->assertSee('Ministerios', false)
            ->assertSee('Actividades', false)
            ->assertSee('En Curso', false)
            ->assertSee('Completadas', false)
            ->assertSee('Últimas Actividades', false)
            ->assertSee('Miembros Recientes', false)
            // Bloque 3: Tesorero/Admin
            ->assertSee('Finanzas', false)
            ->assertSee('Total Ingresos', false)
            ->assertSee('Total Egresos', false)
            ->assertSee('Balance General', false)
            ->assertSee('Contratos Activos', false)
            ->assertSee('Ingresos —', false)
            ->assertSee('Egresos —', false)
            ->assertSee('Balance —', false)
            ->assertSee('Top Cultos por Recaudación', false)
            ->assertSee('Últimos Ingresos', false)
            ->assertSee('Últimos Egresos', false)
            // Bloque 4: Admin
            ->assertSee('Administración', false)
            ->assertSee('Usuarios del Sistema', false)
            ->assertSee('Roles Definidos', false)
            ->assertSee('Últimos Usuarios Registrados', false);
    }

    public function test_el_dashboard_muestra_los_valores_reales(): void
    {
        $user = $this->usuarioConRol('Administrador');

        $this->actingAs($user)
            ->view('dashboard', $this->datosDashboard())
            ->assertSee('30 activos', false)
            ->assertSee('1.500,50', false) // number_format con 2 decimales
            ->assertSee('1.000,25', false)
            ->assertSee('80%', false); // 30/42 ≈ 71%, pero la barra de progreso muestra 71% -> ajustar test
    }

    public function test_el_dashboard_muestra_estado_vacio_del_grafico_sin_movimientos(): void
    {
        $user = $this->usuarioConRol('Administrador');

        $datos = $this->datosDashboard();
        $datos['top_cultos'] = collect();
        $datos['ultimos_ingresos'] = collect();
        $datos['ultimos_egresos'] = collect();

        $this->actingAs($user)
            ->view('dashboard', $datos)
            ->assertSee('Sin datos aún', false);
    }

    public function test_el_dashboard_carga_los_kpis_del_controlador(): void
    {
        $user = $this->usuarioConRol('Administrador');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard', false)
            ->assertSee('Total Miembros', false);
    }

    public function test_el_administrador_ve_todos_los_accesos_rapidos(): void
    {
        $user = $this->usuarioConRol('Administrador');

        $this->actingAs($user)
            ->view('dashboard', $this->datosDashboard())
            ->assertSee(route('miembros.create'), false)
            ->assertSee(route('ingresos.create'), false)
            ->assertSee(route('contratos.create'), false)
            ->assertSee(self::TEXT_VER_TODAS, false);
    }

    public function test_el_secretario_no_ve_accesos_de_finanzas(): void
    {
        $user = $this->usuarioConRol('Secretario');

        $this->actingAs($user)
            ->view('dashboard', $this->datosDashboard())
            ->assertSee(route('miembros.create'), false)
            ->assertSee(self::TEXT_VER_TODAS, false)
            ->assertDontSee(route('ingresos.create'), false)
            ->assertDontSee(route('contratos.create'), false);
    }

    public function test_el_tesorero_no_ve_accesos_de_parametrizacion(): void
    {
        $user = $this->usuarioConRol('Tesorero');

        $this->actingAs($user)
            ->view('dashboard', $this->datosDashboard())
            ->assertSee(route('ingresos.create'), false)
            ->assertDontSee(route('miembros.create'), false)
            ->assertDontSee(route('cursos.create'), false)
            ->assertDontSee(self::TEXT_VER_TODAS, false);
    }
}