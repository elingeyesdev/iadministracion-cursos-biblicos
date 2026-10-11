<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioAdministracionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso total', 'estado' => 'activo']
        );

        $admin = User::factory()->create();
        $admin->roles()->attach($role->id, [
            'fecha_asignacion' => now(),
            'estado' => 'activo',
        ]);

        return $admin;
    }

    public function test_los_usuarios_creados_desde_el_panel_quedan_verificados(): void
    {
        $response = $this->actingAs($this->admin())->post(route('usuarios.store'), [
            'name' => 'Usuario Panel',
            'email' => 'panel@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('usuarios.index'));

        $nuevo = User::where('email', 'panel@example.com')->first();

        $this->assertNotNull($nuevo);
        $this->assertTrue($nuevo->hasVerifiedEmail());

        // Al quedar verificado, puede entrar al sistema sin pasar por el
        // enlace de Gmail.
        $this->actingAs($nuevo)
            ->get(route('reportes.index'))
            ->assertOk();
    }
}
