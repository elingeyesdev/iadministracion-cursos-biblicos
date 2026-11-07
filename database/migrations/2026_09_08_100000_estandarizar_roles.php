<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Estandariza los roles del sistema: renombra "Admin" a "Administrador"
     * y garantiza la existencia de "Secretario" y "Tesorero".
     */
    public function up(): void
    {
        DB::table('roles')->where('nombre', 'Admin')->update(['nombre' => 'Administrador', 'descripcion' => 'Administrador con acceso total al sistema.']);

        $roles = [
            ['nombre' => 'Secretario', 'descripcion' => 'Gestiona personas y módulos operativos del sistema.', 'estado' => 'Activo', 'nivel_acceso' => 2],
            ['nombre' => 'Tesorero', 'descripcion' => 'Gestiona cultos y las finanzas (ingresos, egresos, contratos).', 'estado' => 'Activo', 'nivel_acceso' => 3],
        ];

        $timestamps = now();

        foreach ($roles as $rol) {
            $existe = DB::table('roles')->where('nombre', $rol['nombre'])->exists();
            if (! $existe) {
                DB::table('roles')->insert(array_merge($rol, [
                    'guard_name' => 'web',
                    'created_at' => $timestamps,
                    'updated_at' => $timestamps,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('nombre', ['Secretario', 'Tesorero'])->delete();
        DB::table('roles')->where('nombre', 'Administrador')->update(['nombre' => 'Admin']);
    }
};
