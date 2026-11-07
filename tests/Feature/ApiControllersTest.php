<?php

namespace Tests\Feature;

use App\Enums\DiaSemana;
use App\Models\Cargo;
use App\Models\Culto;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Ingreso;
use App\Models\LiderIglesia;
use App\Models\Miembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllersTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devuelve_un_token_de_sanctum(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secreto123'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secreto123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rechaza_credenciales_invalidas(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secreto123'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrecta',
        ])->assertUnauthorized();
    }

    public function test_los_listados_de_la_api_responden(): void
    {
        foreach ([
            'miembros',
            'ministerios',
            'docentes',
            'actividades',
            'seguimientos',
            'cursos',
            'sesiones',
            'ingresos',
            'contratos',
            'egresos',
            'cultos',
        ] as $recurso) {
            $this->getJson('/api/'.$recurso)->assertOk();
        }
    }

    public function test_crea_un_miembro_con_los_campos_del_nuevo_esquema(): void
    {
        $this->postJson('/api/miembros', [
            'nombre' => 'Ana Torres',
            'email' => 'ana@example.com',
            'telefono' => '70000000',
            'sexo' => 'Femenino',
            'fecha_ingreso' => '2026-01-01',
            'fecha_conversion' => '2020-01-01',
            'fecha_bautismo' => '2020-02-01',
            'estado' => 'Activo',
        ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'Ana Torres');

        $this->assertDatabaseHas('miembros', [
            'nombre' => 'Ana Torres',
            'estado' => 'Activo',
        ]);
    }

    public function test_crea_un_culto_con_lider_y_dia_semana(): void
    {
        $lider = $this->lider();

        $this->postJson('/api/cultos', [
            'id_lider_iglesia' => $lider->id,
            'nombre' => 'Culto Dominical',
            'dia_semana' => DiaSemana::Domingo->value,
            'hora' => '10:00',
        ])
            ->assertCreated()
            ->assertJsonPath('dia_semana', 'Domingo');

        $this->assertDatabaseHas('cultos', [
            'nombre' => 'Culto Dominical',
            'id_lider_iglesia' => $lider->id,
        ]);
    }

    public function test_crea_un_curso_con_horario(): void
    {
        $this->postJson('/api/cursos', [
            'nombre' => 'Discipulado',
            'f_inicio' => '2026-01-05',
            'cupo_max' => 20,
            'dias_semana' => [1, 3],
            'hora_inicio' => '19:00',
            'hora_fin' => '21:00',
            'monto_inscripcion' => 100,
        ])
            ->assertCreated()
            ->assertJsonPath('nombre', 'Discipulado');

        $this->assertDatabaseHas('cursos', ['nombre' => 'Discipulado']);
    }

    public function test_crea_una_sesion_con_docente(): void
    {
        $miembro = $this->miembro('Beto Ruiz');
        $docente = Docente::create([
            'id_miembro' => $miembro->id,
            'especialidad' => 'Teologia',
            'activo' => true,
        ]);

        $curso = Curso::create([
            'nombre' => 'Discipulado',
            'f_inicio' => '2026-01-05',
            'cupo_max' => 20,
            'dias_semana' => [1],
            'hora_inicio' => '19:00',
            'hora_fin' => '21:00',
            'monto_inscripcion' => 0,
        ]);

        $this->postJson('/api/sesiones', [
            'id_curso' => $curso->id,
            'id_docente' => $docente->id,
            'fecha' => '2026-01-05',
            'hora' => '19:00',
            'tema' => 'Introduccion',
        ])->assertCreated();

        $this->assertDatabaseHas('sesiones', [
            'id_curso' => $curso->id,
            'id_docente' => $docente->id,
        ]);
    }

    public function test_crea_un_ingreso_con_categoria_derivada(): void
    {
        $this->postJson('/api/ingresos', [
            'monto_total' => 500,
            'fecha' => '2026-02-01',
            'tipo' => 'Ofrenda',
            'metodo_pago' => 'Efectivo',
        ])
            ->assertCreated()
            ->assertJsonPath('tipo', 'Ofrenda');

        $this->assertDatabaseHas('ingresos', [
            'tipo' => 'Ofrenda',
            'metodo_pago' => 'Efectivo',
        ]);

        $this->assertNotNull(
            Ingreso::where('tipo', 'Ofrenda')->value('categoria_id')
        );
    }

    public function test_crea_un_egreso_con_lider(): void
    {
        $lider = $this->lider('Wilson');

        $this->postJson('/api/egresos', [
            'id_lider_iglesia' => $lider->id,
            'tipo_egreso' => 'Mantenimiento',
            'monto' => 50,
            'fecha' => '2026-02-02',
            'responsable' => 'Wilson',
        ])
            ->assertCreated()
            ->assertJsonPath('tipo_egreso', 'Mantenimiento');

        $this->assertDatabaseHas('egresos', [
            'id_lider_iglesia' => $lider->id,
            'responsable' => 'Wilson',
        ]);
    }

    public function test_crea_un_contrato_con_compensacion(): void
    {
        $miembro = $this->miembro('Ana Torres');
        $lider = $this->lider('Wilson');

        $this->postJson('/api/contratos', [
            'id_miembro' => $miembro->id,
            'id_lider_iglesia' => $lider->id,
            'tipo_compensacion' => 'Salario',
            'salario' => 1000,
            'fecha_inicio' => '2026-01-01',
        ])
            ->assertCreated()
            ->assertJsonPath('tipo_compensacion', 'Salario');

        $this->assertDatabaseHas('contratos', [
            'id_miembro' => $miembro->id,
            'salario' => 1000,
        ]);
    }

    public function test_un_culto_con_ingresos_no_se_elimina(): void
    {
        $lider = $this->lider();

        $culto = Culto::create([
            'id_lider_iglesia' => $lider->id,
            'nombre' => 'Culto Dominical',
            'dia_semana' => 'Domingo',
            'hora' => '10:00',
        ]);

        Ingreso::create([
            'id_culto' => $culto->id,
            'monto_total' => 100,
            'fecha' => '2026-02-01',
            'tipo' => 'Ofrenda',
            'metodo_pago' => 'Efectivo',
        ]);

        $this->deleteJson('/api/cultos/'.$culto->id)->assertUnprocessable();

        $this->assertNotSoftDeleted('cultos', ['id' => $culto->id]);
    }

    private function miembro(string $nombre): Miembro
    {
        return Miembro::create([
            'nombre' => $nombre,
            'fecha_ingreso' => now()->toDateString(),
            'estado' => 'Activo',
        ]);
    }

    private function lider(string $nombre = 'Wilson'): LiderIglesia
    {
        $cargo = Cargo::create(['nombre' => 'Pastor', 'nivel_jerarquico' => 1]);

        return LiderIglesia::create([
            'id_cargo' => $cargo->id,
            'nombre' => $nombre,
            'fecha_inicio' => '2026-01-01',
            'activo' => true,
        ]);
    }
}
