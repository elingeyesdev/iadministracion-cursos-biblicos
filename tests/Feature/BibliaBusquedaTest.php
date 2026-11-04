<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Services\BibliaDigitalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BibliaBusquedaTest extends TestCase
{
    use RefreshDatabase;

    private function fakeBibliaApi(): void
    {
        Http::fake([
            'api.getbible.net/v2/valera/books.json' => Http::response([
                '19' => ['nr' => 19, 'name' => 'Salmos'],
                '43' => ['nr' => 43, 'name' => 'Juan'],
                '46' => ['nr' => 46, 'name' => '1 Corintios'],
            ], 200),
            'api.getbible.net/v2/valera/43/3.json' => Http::response([
                'translation' => 'Reina Valera (1909)',
                'book_nr' => 43,
                'book_name' => 'Juan',
                'chapter' => 3,
                'verses' => [
                    ['chapter' => 3, 'verse' => 16, 'text' => 'Porque de tal manera amó Dios al mundo...'],
                    ['chapter' => 3, 'verse' => 17, 'text' => 'Porque no envió Dios a su Hijo al mundo...'],
                ],
            ], 200),
            'api.getbible.net/v2/valera/43/4.json' => Http::response([
                'translation' => 'Reina Valera (1909)',
                'book_nr' => 43,
                'book_name' => 'Juan',
                'chapter' => 4,
                'verses' => [
                    ['chapter' => 4, 'verse' => 1, 'text' => 'Cuando, pues, el Señor entendió...'],
                ],
            ], 200),
            'api.getbible.net/v2/valera/19/23.json' => Http::response([
                'translation' => 'Reina Valera (1909)',
                'book_nr' => 19,
                'book_name' => 'Salmos',
                'chapter' => 23,
                'verses' => [
                    ['chapter' => 23, 'verse' => 1, 'text' => 'Jehová es mi pastor; nada me faltará.'],
                ],
            ], 200),
            'api.getbible.net/v2/valera/46/13.json' => Http::response([
                'translation' => 'Reina Valera (1909)',
                'book_nr' => 46,
                'book_name' => '1 Corintios',
                'chapter' => 13,
                'verses' => [
                    ['chapter' => 13, 'verse' => 4, 'text' => 'El amor es sufrido, es benigno...'],
                    ['chapter' => 13, 'verse' => 5, 'text' => 'No se porta indecorosamente...'],
                ],
            ], 200),
        ]);
    }

    private function usuario(): User
    {
        $user = User::factory()->create();

        $rol = Role::firstOrCreate(['nombre' => 'Consulta'], ['estado' => 'activo']);
        $permiso = Permiso::firstOrCreate(
            ['nombre' => 'biblia.ver'],
            ['descripcion' => 'Consultar la Biblia digital', 'modulo' => 'Biblia']
        );

        $rol->permisos()->syncWithoutDetaching([$permiso->id]);
        $user->roles()->attach($rol->id, ['fecha_asignacion' => now(), 'estado' => 'activo']);

        return $user;
    }

    public function test_devuelve_el_versiculo_solicitado(): void
    {
        $this->fakeBibliaApi();

        $response = $this->actingAs($this->usuario())
            ->getJson('/biblia/buscar/versiculos?q='.urlencode('Juan 3:16'));

        $response->assertOk()
            ->assertJsonPath('reference', 'Juan 3:16')
            ->assertJsonCount(1, 'verses')
            ->assertJsonPath('verses.0.text', 'Porque de tal manera amó Dios al mundo...');
    }

    public function test_al_cambiar_de_capitulo_cambian_los_versiculos(): void
    {
        $this->fakeBibliaApi();

        $usuario = $this->usuario();

        $capitulo3 = $this->actingAs($usuario)
            ->getJson('/biblia/buscar/versiculos?q='.urlencode('Juan 3'))
            ->assertOk()
            ->json();

        $capitulo4 = $this->actingAs($usuario)
            ->getJson('/biblia/buscar/versiculos?q='.urlencode('Juan 4'))
            ->assertOk()
            ->json();

        $this->assertSame('Juan 3', $capitulo3['reference']);
        $this->assertSame('Juan 4', $capitulo4['reference']);
        $this->assertNotEquals($capitulo3['verses'], $capitulo4['verses']);
    }

    public function test_resuelve_abreviaturas_y_libros_numerados(): void
    {
        $this->fakeBibliaApi();

        $this->actingAs($this->usuario())
            ->getJson('/biblia/buscar/versiculos?q='.urlencode('Sal 23'))
            ->assertOk()
            ->assertJsonPath('reference', 'Salmos 23');

        $this->actingAs($this->usuario())
            ->getJson('/biblia/buscar/versiculos?q='.urlencode('1 Corintios 13:4-5'))
            ->assertOk()
            ->assertJsonPath('reference', '1 Corintios 13:4-5')
            ->assertJsonCount(2, 'verses');
    }

    public function test_referencia_no_reconocida_devuelve_error(): void
    {
        $this->fakeBibliaApi();

        // Evitamos que el fallback de palabra clave intente salir a la red
        // (su API externa no está configurada en este proyecto).
        $this->mock(BibliaDigitalService::class, function ($mock) {
            $mock->shouldReceive('searchVerses')->andReturn(['errors' => []]);
        });

        $this->actingAs($this->usuario())
            ->getJson('/biblia/buscar/versiculos?q='.urlencode('palabra inventada xyz'))
            ->assertStatus(404);
    }
}
