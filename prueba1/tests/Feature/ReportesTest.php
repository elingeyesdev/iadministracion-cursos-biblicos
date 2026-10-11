<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create();
    }

    public function test_indice_de_reportes_se_renderiza(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('reportes.index'))
            ->assertOk()
            ->assertSee('Reporte de Miembros');
    }

    public function test_reporte_de_miembros_se_renderiza_como_documento(): void
    {
        $response = $this->actingAs($this->usuario())->get(route('reportes.miembros'));

        $response->assertOk()
            ->assertSee('report-document', false)
            ->assertSee('report-page', false)
            ->assertSee('app-chrome', false)
            ->assertSee('<h1>Reporte de Miembros</h1>', false)
            ->assertSee('N.º', false);
    }

    public function test_reporte_de_asistencia_se_renderiza_como_documento(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('reportes.asistencia'))
            ->assertOk()
            ->assertSee('report-document', false)
            ->assertSee('<h1>Reporte de Asistencia</h1>', false);
    }

    public function test_reporte_financiero_se_renderiza_como_documento(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('reportes.financiero'))
            ->assertOk()
            ->assertSee('report-document', false)
            ->assertSee('<h1>Reporte Financiero</h1>', false);
    }
}
