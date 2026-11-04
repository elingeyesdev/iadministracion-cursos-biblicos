<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Permisos que recibe cada rol del sistema. El rol Administrador recibe
     * todos los permisos del catálogo.
     *
     * @var array<string, array<int, string>>
     */
    private array $asignacionesPorRol = [
        'Secretario' => [
            // Personas
            'miembros.ver', 'miembros.gestionar',
            'docentes.ver', 'docentes.gestionar',
            'lideres.ver', 'lideres.gestionar',
            'cargos.ver', 'cargos.gestionar',
            // Organización
            'ministerios.ver', 'ministerios.gestionar',
            'actividades.ver', 'actividades.gestionar',
            'seguimientos.ver', 'seguimientos.gestionar',
            // Académico
            'cursos.ver', 'cursos.gestionar',
            'sesiones.ver', 'sesiones.gestionar',
            'inscripciones.ver', 'inscripciones.gestionar',
            'asistencia.ver', 'asistencia.gestionar',
            // Consulta
            'reportes.ver',
            'biblia.ver',
        ],
        'Tesorero' => [
            // Finanzas
            'cultos.ver', 'cultos.gestionar',
            'ingresos.ver', 'ingresos.gestionar',
            'egresos.ver', 'egresos.gestionar',
            'contratos.ver', 'contratos.gestionar',
            // Consulta
            'miembros.ver',
            'docentes.ver',
            'lideres.ver',
            'cargos.ver',
            'cursos.ver',
            'sesiones.ver',
            'inscripciones.ver',
            'asistencia.ver',
            'reportes.ver',
            'biblia.ver',
        ],
    ];

    /**
     * Permisos de consulta que antes estaban disponibles para cualquier
     * usuario autenticado. Se usan como base para roles personalizados que
     * todavía no tienen permisos asignados.
     *
     * @var array<int, string>
     */
    private array $permisosBase = [
        'cursos.ver',
        'sesiones.ver',
        'docentes.ver',
        'cargos.ver',
        'lideres.ver',
        'inscripciones.ver',
        'asistencia.ver',
        'reportes.ver',
        'biblia.ver',
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Sincroniza el catálogo de permisos con la configuración.
        foreach (config('permisos.modulos', []) as $modulo => $datos) {
            foreach ($datos['permisos'] ?? [] as $nombre => $descripcion) {
                Permiso::updateOrCreate(
                    ['nombre' => $nombre],
                    ['descripcion' => $descripcion, 'modulo' => $modulo]
                );
            }
        }

        $ids = Permiso::query()->pluck('id', 'nombre');

        // 2. Administrador: todos los permisos del catálogo.
        $admin = Role::where('nombre', 'Administrador')->first();
        if ($admin) {
            $admin->permisos()->sync($ids->values()->all());
        }

        // 3. Roles del sistema con su conjunto inicial de permisos.
        foreach ($this->asignacionesPorRol as $rolNombre => $permisos) {
            $rol = Role::where('nombre', $rolNombre)->first();

            if (! $rol || $rol->permisos()->exists()) {
                continue;
            }

            $rol->permisos()->sync(
                collect($permisos)->map(fn ($permiso) => $ids[$permiso] ?? null)->filter()->values()->all()
            );
        }

        // 4. Roles personalizados sin permisos: se les deja el acceso de
        //    consulta que tenían antes de existir los permisos.
        $base = collect($this->permisosBase)
            ->map(fn ($permiso) => $ids[$permiso] ?? null)
            ->filter()
            ->values()
            ->all();

        Role::where('nombre', '!=', 'Administrador')
            ->whereDoesntHave('permisos')
            ->get()
            ->each(fn (Role $rol) => $rol->permisos()->sync($base));
    }
}
