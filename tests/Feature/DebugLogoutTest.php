<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_logout_text(): void
    {
        $role = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['estado' => 'Activo', 'nivel_acceso' => 1]
        );

        $admin = User::factory()->create(['email' => 'debug_logout@test.com']);
        $admin->roles()->attach($role->id);

        $response = $this->actingAs($admin)->get('/reportes/miembros');
        
        $content = $response->getContent();
        
        // Search for the logout text
        if (preg_match('/Cerrar sesi[^<]*/i', $content, $matches)) {
            dump('Found: ' . $matches[0]);
            dump('Hex: ' . bin2hex($matches[0]));
        } else {
            dump('Not found');
            // Show a snippet around user-menu
            if (preg_match('/user-menu[^<]{0,500}/i', $content, $matches)) {
                dump('user-menu context: ' . $matches[0]);
            }
        }
        
        $this->assertTrue(true);
    }
}