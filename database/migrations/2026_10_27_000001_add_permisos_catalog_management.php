<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Agrega al catálogo los permisos de administración del catálogo de
     * permisos (permisos.ver / permisos.gestionar) y los asigna al rol
     * Administrador.
     */
    public function up(): void
    {
        (new \Database\Seeders\PermisoSeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Permiso::whereIn('nombre', ['permisos.ver', 'permisos.gestionar'])->delete();
    }
};
