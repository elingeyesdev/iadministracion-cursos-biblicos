<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DebugSidebarTest extends TestCase
{
    use DatabaseMigrations;

    public function test_debug_sidebar_html(): void
    {
        $rol = Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['estado' => 'Activo', 'nivel_acceso' => 1]
        );

        $admin = User::factory()->create(['email' => 'debug_sidebar@test.com']);
        $admin->roles()->attach($rol);

        // Usar una ruta que sabemos que renderiza el layout completo
        $response = $this->actingAs($admin)->get('/dashboard');

        dump('Status: ' . $response->getStatusCode());
        dump('Content length: ' . strlen($response->getContent()));

        $content = $response->getContent();

        // Volcar los primeros 2000 caracteres para ver la estructura
        dump('First 2000 chars: ' . substr($content, 0, 2000));

        // Buscar sidebar
        if (preg_match('/sidebar[^>]*/', $content, $matches)) {
            dump('sidebar found: ' . $matches[0]);
        }

        $this->assertTrue(true); // Test dummy
    }
}