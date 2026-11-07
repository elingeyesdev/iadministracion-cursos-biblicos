<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SecuritySmokeTest extends TestCase
{
    use DatabaseMigrations;

    private User $admin;

    private User $secretario;

    private User $tesorero;

    private const ROUTE_LOGIN = '/login';

    private const ROUTE_CULTOS = '/cultos';

    private const ROUTE_CARGOS = '/cargos';

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Administrador', 'Secretario', 'Tesorero'] as $nombre) {
            Role::firstOrCreate(['nombre' => $nombre], ['estado' => 'Activo', 'nivel_acceso' => 1]);
        }

        $this->admin = User::factory()->create(['email' => 'admin_test@proyecto.com']);
        $this->admin->roles()->attach(Role::where('nombre', 'Administrador')->first());

        $this->secretario = User::factory()->create(['email' => 'secretario_test@proyecto.com']);
        $this->secretario->roles()->attach(Role::where('nombre', 'Secretario')->first());

        $this->tesorero = User::factory()->create(['email' => 'tesorero_test@proyecto.com']);
        $this->tesorero->roles()->attach(Role::where('nombre', 'Tesorero')->first());
    }

    protected function tearDown(): void
    {
        foreach ([$this->admin, $this->secretario, $this->tesorero] as $user) {
            $user?->roles()->detach();
            $user?->forceDelete();
        }
        parent::tearDown();
    }

    public function test_guest_no_accede_a_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(self::ROUTE_LOGIN);
    }

    public function test_el_registro_publico_no_existe(): void
    {
        // El sistema es interno: los usuarios solo se crean desde /users.
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();
    }

    public function test_administrador_accede_a_todo(): void
    {
        $this->actingAs($this->admin);
        $this->get('/users')->assertOk();
        $this->get(self::ROUTE_CULTOS)->assertOk();
        $this->get(self::ROUTE_CARGOS)->assertOk();
    }

    public function test_secretario_sin_acceso_a_admin_y_finanzas(): void
    {
        $this->actingAs($this->secretario);
        $this->get(self::ROUTE_CARGOS)->assertOk();
        $this->get(self::ROUTE_CULTOS)->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('/ingresos')->assertForbidden();
    }

    public function test_tesorero_sin_acceso_a_personas(): void
    {
        $this->actingAs($this->tesorero);
        $this->get(self::ROUTE_CULTOS)->assertOk();
        $this->get(self::ROUTE_CARGOS)->assertForbidden();
        $this->get('/miembros')->assertForbidden();
    }

    public function test_cabeceras_de_seguridad_presentes(): void
    {
        $response = $this->get(self::ROUTE_LOGIN);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_login_envia_mensaje_por_credenciales_incorrectas(): void
    {
        $this->post(self::ROUTE_LOGIN, [
            'email' => 'nobody@proyecto.com',
            'password' => 'incorrecta',
        ])->assertRedirect();
    }
}
