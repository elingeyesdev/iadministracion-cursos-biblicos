<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisoSeeder::class);
    }

    private function usuarioConRol(string $nombreRol, array $permisos = []): User
    {
        $rol = Role::create(['nombre' => $nombreRol, 'estado' => 'activo']);
        $rol->permisos()->sync(
            Permiso::whereIn('nombre', $permisos)->pluck('id')->all()
        );

        $user = User::factory()->create();
        $user->roles()->attach($rol->id, [
            'fecha_asignacion' => now(),
            'estado' => 'activo',
        ]);

        return $user;
    }

    public function test_administrador_ve_roles_sin_permisos_explicitos(): void
    {
        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)->get(route('roles.index'))->assertOk();
        $this->actingAs($admin)->get(route('roles.create'))->assertOk();
    }

    public function test_usuario_sin_permisos_no_puede_ver_roles(): void
    {
        $user = $this->usuarioConRol('Consulta');

        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
    }

    public function test_gestionar_incluye_ver(): void
    {
        $user = $this->usuarioConRol('Gestor', ['roles.gestionar']);

        $this->actingAs($user)->get(route('roles.index'))->assertOk();
        $this->actingAs($user)->get(route('roles.create'))->assertOk();
        $this->actingAs($user)->post(route('roles.store'), [
            'nombre' => 'Nuevo Rol',
            'descripcion' => 'Creado en el test',
            'estado' => 'activo',
        ])->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['nombre' => 'Nuevo Rol']);
        $this->assertSame(0, Role::where('nombre', 'Nuevo Rol')->first()->permisos()->count());
    }

    public function test_solo_ver_no_permite_gestionar(): void
    {
        $user = $this->usuarioConRol('Lector', ['roles.ver']);

        $this->actingAs($user)->get(route('roles.index'))->assertOk();
        $this->actingAs($user)->get(route('roles.create'))->assertForbidden();
    }

    public function test_la_pantalla_de_edicion_de_rol_no_permite_editar_permisos(): void
    {
        $rol = Role::create(['nombre' => 'Editor', 'estado' => 'activo']);
        $rol->permisos()->sync([Permiso::where('nombre', 'roles.ver')->value('id')]);

        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)
            ->get(route('roles.edit', $rol))
            ->assertOk()
            ->assertDontSee('name="permisos[]"', false)
            ->assertSee('Administrar Permisos');

        // Aunque se envíe el campo, la edición del rol ya no lo procesa.
        $this->actingAs($admin)
            ->put(route('roles.update', $rol), [
                'nombre' => 'Editor',
                'estado' => 'activo',
                'permisos' => [Permiso::where('nombre', 'miembros.gestionar')->value('id')],
            ])
            ->assertRedirect(route('roles.index'));

        $rol->refresh();

        $this->assertTrue($rol->tienePermiso('roles.ver'));
        $this->assertFalse($rol->tienePermiso('miembros.gestionar'));
    }

    public function test_el_catalogo_de_permisos_requiere_su_permiso(): void
    {
        $sinAcceso = $this->usuarioConRol('Sin acceso a permisos');
        $this->actingAs($sinAcceso)->get(route('permisos.index'))->assertForbidden();

        $lector = $this->usuarioConRol('Lector de permisos', ['permisos.ver']);
        $this->actingAs($lector)->get(route('permisos.index'))->assertOk();
    }

    public function test_se_pueden_editar_los_permisos_de_un_rol_desde_administrar_permisos(): void
    {
        $rol = Role::create(['nombre' => 'Operador', 'estado' => 'activo']);
        $admin = $this->usuarioConRol('Administrador');

        $this->actingAs($admin)
            ->get(route('permisos.index'))
            ->assertOk()
            ->assertSee('Editar permisos')
            ->assertSee('name="permisos[]"', false)
            ->assertSee('miembros.ver');

        $this->actingAs($admin)
            ->put(route('permisos.roles.update', $rol), [
                'permisos' => [Permiso::where('nombre', 'miembros.ver')->value('id')],
            ])
            ->assertRedirect(route('permisos.index', ['rol' => $rol->id]));

        $this->assertTrue($rol->fresh()->tienePermiso('miembros.ver'));
    }

    public function test_editar_permisos_de_un_rol_requiere_permisos_gestionar(): void
    {
        $rol = Role::create(['nombre' => 'Revisor', 'estado' => 'activo']);
        $lector = $this->usuarioConRol('Solo lectura de permisos', ['permisos.ver']);

        $this->actingAs($lector)
            ->get(route('permisos.index'))
            ->assertOk()
            ->assertSee('Solo lectura')
            ->assertDontSee('name="permisos[]"', false);

        $this->actingAs($lector)
            ->put(route('permisos.roles.update', $rol), ['permisos' => []])
            ->assertForbidden();
    }
}
