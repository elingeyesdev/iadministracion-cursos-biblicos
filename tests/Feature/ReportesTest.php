<?php

namespace Tests\Feature;

use App\Models\Miembro;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use DatabaseMigrations;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $rol = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['estado' => 'Activo', 'nivel_acceso' => 1]
        );

        $this->admin = User::factory()->create(['email' => 'reportes_test@proyecto.com']);
        $this->admin->roles()->attach($rol);
    }

    protected function tearDown(): void
    {
        $this->admin?->roles()->detach();
        $this->admin?->forceDelete();
        parent::tearDown();
    }

    private function miembro(array $attributes = []): Miembro
    {
        return Miembro::create(array_merge([
            'nombre' => 'Miembro de Prueba',
            'telefono' => '70000000',
            'sexo' => 'Masculino',
            'fecha_ingreso' => '2024-01-15',
            'estado' => 'Activo',
        ], $attributes));
    }

    private const ROUTE_REPORTES_MIEMBROS = '/reportes/miembros';

    private const TEXT_NOMBRE_A = 'Ana Activa Test';

    private const TEXT_NOMBRE_B = 'Beto Inactivo Test';

    public function test_guest_no_accede_a_reportes(): void
    {
        $this->get(self::ROUTE_REPORTES_MIEMBROS)->assertRedirect('/login');
    }

    public function test_reporte_miembros_renderiza_documento_y_totales(): void
    {
        $this->miembro(['nombre' => self::TEXT_NOMBRE_A, 'estado' => 'Activo']);
        $this->miembro(['nombre' => self::TEXT_NOMBRE_B, 'estado' => 'Inactivo']);

        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        // Documento imprimible presente con encabezado y estructura.
        $response->assertSee('report-document', false);
        $response->assertSee('Reporte de Miembros');
        $response->assertSee('Total de miembros');
        $response->assertSee('Miembros activos');
        $response->assertSee('Miembros inactivos');
        $response->assertSee('Ana Activa Test');
        $response->assertSee('Beto Inactivo Test');
    }

    public function test_filtro_estado_actualiza_tabla_y_estadisticas(): void
    {
        $this->miembro(['nombre' => self::TEXT_NOMBRE_A, 'estado' => 'Activo']);
        $this->miembro(['nombre' => self::TEXT_NOMBRE_B, 'estado' => 'Inactivo']);

        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS.'?estado=Activo');

        $response->assertOk();
        $response->assertSee(self::TEXT_NOMBRE_A);
        $response->assertDontSee(self::TEXT_NOMBRE_B);
        // El estado filtrado se refleja en los metadatos del encabezado.
        $response->assertSee('Estado');
    }

    public function test_filtro_fechas_de_ingreso(): void
    {
        $this->miembro(['nombre' => 'Dentro Rango Test', 'fecha_ingreso' => '2024-06-01']);
        $this->miembro(['nombre' => 'Fuera Rango Test', 'fecha_ingreso' => '2022-01-01']);

        $response = $this->actingAs($this->admin)
            ->get(self::ROUTE_REPORTES_MIEMBROS.'?desde=2024-01-01&hasta=2024-12-31');

        $response->assertOk();
        $response->assertSee('Dentro Rango Test');
        $response->assertDontSee('Fuera Rango Test');
    }

    public function test_reporte_asistencia_renderiza_documento(): void
    {
        $response = $this->actingAs($this->admin)->get('/reportes/asistencia');

        $response->assertOk();
        $response->assertSee('report-document', false);
        $response->assertSee('Reporte de Asistencia');
        $response->assertSee('Presentes');
    }

    public function test_reporte_financiero_renderiza_documento(): void
    {
        $response = $this->actingAs($this->admin)->get('/reportes/financiero');

        $response->assertOk();
        $response->assertSee('report-document', false);
        $response->assertSee('Reporte Financiero');
        $response->assertSee('Balance');
    }

    public function test_panel_de_control_conserva_filtros(): void
    {
        $response = $this->actingAs($this->admin)->get(self::ROUTE_REPORTES_MIEMBROS);

        $response->assertOk();
        $response->assertSee('report-panel', false);
        $response->assertSee('Aplicar filtros');
        $response->assertSee('Vista previa');
        $response->assertSee('Exportar PDF');
        $response->assertSee('name="estado"', false);
        $response->assertSee('name="desde"', false);
        $response->assertSee('name="hasta"', false);
        // Los botones ejecutan acciones reales: el script de impresión se inyecta.
        $response->assertSee('window.print()', false);
        $response->assertSee('data-report-preview', false);
    }

    public function test_tabla_del_reporte_envuelve_scroll_sin_afectar_la_impresion(): void
    {
        $this->miembro(['nombre' => 'Ana Scroll Test']);

        // En pantalla la tabla se envuelve para que su scroll no desborde la hoja.
        $this->actingAs($this->admin)
            ->get(self::ROUTE_REPORTES_MIEMBROS)
            ->assertOk()
            ->assertSee('report-table-scroll', false);

        // El scroll queda aislado en pantalla y se libera al imprimir/exportar PDF.
        $css = file_get_contents(public_path('css/style.css'));
        $this->assertStringContainsString('@media screen and (max-width: 768px)', $css);
        $this->assertMatchesRegularExpression(
            '/@media print.*\.report-table-scroll\s*\{\s*overflow:\s*visible/s',
            $css
        );
    }
}
