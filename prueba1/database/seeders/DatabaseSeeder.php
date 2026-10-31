<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Miembro;
use App\Models\Ministerio;
use App\Models\Docente;
use App\Models\Curso;
use App\Models\Sesion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Roles
        $adminRole = \App\Models\Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso total', 'estado' => 'activo']
        );
        $secreRole = \App\Models\Role::firstOrCreate(
            ['nombre' => 'Secretario'],
            ['descripcion' => 'Gestión de miembros y actividades', 'estado' => 'activo']
        );
        $tesoreRole = \App\Models\Role::firstOrCreate(
            ['nombre' => 'Tesorero'],
            ['descripcion' => 'Gestión financiera', 'estado' => 'activo']
        );

        // Catálogo de permisos y permisos iniciales de cada rol.
        $this->call(PermisoSeeder::class);

        // 1. Crear usuario Administrador principal para no perder acceso
        $userMain = User::updateOrCreate(
            ['email' => 'theanimeempire2024@gmail.com'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('12345678')
            ]
        );

        // El administrador principal no pasa por el registro público: se marca
        // como verificado ('email_verified_at' no es asignable en masa).
        if (! $userMain->hasVerifiedEmail()) {
            $userMain->markEmailAsVerified();
        }

        $userMain->profile()->updateOrCreate(
            ['user_id' => $userMain->id],
            [
                'username'       => 'admin_main',
                'telefono'       => '71111111',
                'estado'         => 'activo',
                'fecha_registro' => today(),
            ]
        );
        if (!$userMain->roles()->where('role_id', $adminRole->id)->exists()) {
            $userMain->roles()->attach($adminRole->id, [
                'fecha_asignacion' => today(),
                'estado'           => 'activo',
            ]);
        }

        // 1.2 Crear usuario admin@iglesia.com
        $userAdmin = User::updateOrCreate(
            ['email' => 'admin@iglesia.com'],
            [
                'name'     => 'Administrador',
                'email'    => 'admin@iglesia.com',
                'password' => Hash::make('admin123'),
            ]
        );

        if (! $userAdmin->hasVerifiedEmail()) {
            $userAdmin->markEmailAsVerified();
        }

        $userAdmin->profile()->updateOrCreate(
            ['user_id' => $userAdmin->id],
            [
                'username'       => 'admin',
                'telefono'       => '70000000',
                'estado'         => 'activo',
                'fecha_registro' => today(),
            ]
        );
        if (!$userAdmin->roles()->where('role_id', $adminRole->id)->exists()) {
            $userAdmin->roles()->attach($adminRole->id, [
                'fecha_asignacion' => today(),
                'estado'           => 'activo',
            ]);
        }

        // 2. Crear 5 Miembros con datos variados y únicos
        $miembro1 = Miembro::firstOrCreate(
            ['email' => 'juan.perez@example.com'],
            [
                'nombre' => 'Juan Pérez',
                'telefono' => '78451236',
                'f_nacimiento' => '1990-05-15',
                'estado' => 'activo',
                'direccion' => 'Calle Principal #123',
                'fecha_ingreso' => '2020-01-10',
                'sexo' => 'M'
            ]
        );

        $miembro2 = Miembro::firstOrCreate(
            ['email' => 'maria.gomez@example.com'],
            [
                'nombre' => 'María Gómez',
                'telefono' => '76543210',
                'f_nacimiento' => '1995-08-22',
                'estado' => 'activo',
                'direccion' => 'Avenida Central #456',
                'fecha_ingreso' => '2021-03-15',
                'sexo' => 'F'
            ]
        );

        $miembro3 = Miembro::firstOrCreate(
            ['email' => 'carlos.mendoza@example.com'],
            [
                'nombre' => 'Carlos Mendoza',
                'telefono' => '69874512',
                'f_nacimiento' => '1988-11-30',
                'estado' => 'activo',
                'direccion' => 'Barrio Lindo Calle 4',
                'fecha_ingreso' => '2019-06-20',
                'sexo' => 'M'
            ]
        );

        $miembro4 = Miembro::firstOrCreate(
            ['email' => 'ana.rios@example.com'],
            [
                'nombre' => 'Ana Ríos',
                'telefono' => '71234567',
                'f_nacimiento' => '1992-02-14',
                'estado' => 'activo',
                'direccion' => 'Urbanización El Prado #7',
                'fecha_ingreso' => '2022-09-01',
                'sexo' => 'F'
            ]
        );

        $miembro5 = Miembro::firstOrCreate(
            ['email' => 'santiago.perez@example.com'],
            [
                'nombre' => 'Santiago Pérez',
                'telefono' => '65478912',
                'f_nacimiento' => '2000-07-07',
                'estado' => 'activo',
                'direccion' => 'Av. San Martín #88',
                'fecha_ingreso' => '2023-02-28',
                'sexo' => 'M'
            ]
        );

        // 3. Crear 3 Ministerios referenciando miembros como líderes
        $min1 = Ministerio::firstOrCreate(
            ['nombre' => 'Alabanza y Adoración'],
            [
                'descripcion' => 'Ministerio encargado de la música y dirección del canto en la congregación.',
                'id_lider' => $miembro1->id
            ]
        );

        $min2 = Ministerio::firstOrCreate(
            ['nombre' => 'Jóvenes con Propósito'],
            [
                'descripcion' => 'Ministerio enfocado en el crecimiento espiritual de los jóvenes.',
                'id_lider' => $miembro2->id
            ]
        );

        $min3 = Ministerio::firstOrCreate(
            ['nombre' => 'Acción Social y Misiones'],
            [
                'descripcion' => 'Ministerio de ayuda humanitaria y extensión comunitaria.',
                'id_lider' => $miembro3->id
            ]
        );

        // 4. Crear 1 Docente vinculado al primer miembro
        $docente = Docente::firstOrCreate(
            ['id_miembro' => $miembro1->id],
            [
                'especialidad' => 'Teología Sistemática y Apologética',
                'titulo' => 'Licenciado en Ciencias Bíblicas',
                'activo' => true
            ]
        );

        // 5. Crear 2 Cursos, cada uno con 3 Sesiones
        $curso1 = Curso::firstOrCreate(
            ['nombre' => 'Doctrina Básica Cristiana'],
            [
                'f_inicio' => Carbon::now()->format('Y-m-d'),
                'f_fin' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'cupo_max' => 30,
                'tiene_pago' => false,
            ]
        );

        // Vincular docente al curso
        if (!$curso1->docentes()->where('docente_curso.id_docente', $docente->id)->exists()) {
            $curso1->docentes()->attach($docente->id);
        }

        Sesion::firstOrCreate(
            [
                'id_curso' => $curso1->id,
                'tema' => 'Introducción al Canon Bíblico'
            ],
            [
                'fecha' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'hora' => '19:30:00',
                'observacion' => 'Traer cuaderno y biblia.'
            ]
        );

        Sesion::firstOrCreate(
            [
                'id_curso' => $curso1->id,
                'tema' => 'La Naturaleza de Dios (Trinidad)'
            ],
            [
                'fecha' => Carbon::now()->addDays(9)->format('Y-m-d'),
                'hora' => '19:30:00',
                'observacion' => 'Lectura recomendada: Juan 1.'
            ]
        );

        Sesion::firstOrCreate(
            [
                'id_curso' => $curso1->id,
                'tema' => 'La Caída del Hombre y la Gracia'
            ],
            [
                'fecha' => Carbon::now()->addDays(16)->format('Y-m-d'),
                'hora' => '19:30:00',
                'observacion' => 'Evaluación rápida del primer módulo.'
            ]
        );

        $curso2 = Curso::firstOrCreate(
            ['nombre' => 'Liderazgo y Mentoreo Eficaz'],
            [
                'f_inicio' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'f_fin' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'cupo_max' => 15,
                'tiene_pago' => true,
                'monto_inscripcion' => 25.00
            ]
        );

        // Vincular docente al curso 2
        if (!$curso2->docentes()->where('docente_curso.id_docente', $docente->id)->exists()) {
            $curso2->docentes()->attach($docente->id);
        }

        Sesion::firstOrCreate(
            [
                'id_curso' => $curso2->id,
                'tema' => 'Cualidades del Carácter de un Líder'
            ],
            [
                'fecha' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'hora' => '18:00:00',
                'observacion' => 'Estudio en el libro de Nehemías.'
            ]
        );

        Sesion::firstOrCreate(
            [
                'id_curso' => $curso2->id,
                'tema' => 'El Arte de Escuchar y Aconsejar'
            ],
            [
                'fecha' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'hora' => '18:00:00',
                'observacion' => 'Dinámicas de rol playing en clase.'
            ]
        );

        Sesion::firstOrCreate(
            [
                'id_curso' => $curso2->id,
                'tema' => 'Planificación Estratégica Eclesial'
            ],
            [
                'fecha' => Carbon::now()->addDays(21)->format('Y-m-d'),
                'hora' => '18:00:00',
                'observacion' => 'Entrega de propuesta de proyecto final.'
            ]
        );
    }
}
