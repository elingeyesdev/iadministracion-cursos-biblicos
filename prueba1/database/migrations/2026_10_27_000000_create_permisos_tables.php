<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion')->nullable();
            $table->string('modulo', 100)->default('General');
            $table->timestamps();
        });

        Schema::create('permiso_role', function (Blueprint $table) {
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['permiso_id', 'role_id']);
            $table->timestamps();
        });

        // Carga el catálogo de permisos y los asigna a los roles existentes,
        // de modo que la aplicación siga funcionando al aplicar la migración.
        (new \Database\Seeders\PermisoSeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permiso_role');
        Schema::dropIfExists('permisos');
    }
};
