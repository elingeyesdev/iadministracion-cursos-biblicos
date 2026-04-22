<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MiembroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Miembro::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'correo' => 'juan@example.com',
            'telefono' => '123456789',
            'rol' => 'miembro',
            'estado' => true,
        ]);

        \App\Models\Miembro::create([
            'nombre' => 'María',
            'apellido' => 'García',
            'correo' => 'maria@example.com',
            'telefono' => '987654321',
            'rol' => 'administrador',
            'estado' => true,
        ]);

        \App\Models\Miembro::create([
            'nombre' => 'Carlos',
            'apellido' => 'López',
            'correo' => 'carlos@example.com',
            'telefono' => '555666777',
            'rol' => 'supervisor',
            'estado' => true,
        ]);
    }
}
