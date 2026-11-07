<?php

use Database\Seeders\PermisosPorRolSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Inicializa los permisos de los roles existentes. La matriz de permisos
     * vive en PermisosPorRolSeeder para reutilizarla en seeders y tests.
     */
    public function up(): void
    {
        (new PermisosPorRolSeeder)->run();
    }

    public function down(): void
    {
        DB::table('permisos')->truncate();
    }
};
