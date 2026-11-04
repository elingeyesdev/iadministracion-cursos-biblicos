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
        Schema::create('miembros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email')->unique();
            $table->string('telefono', 20)->nullable();
            $table->date('f_nacimiento')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'visita'])->default('activo');
            $table->string('direccion', 200)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->enum('sexo', ['M', 'F', 'otro'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miembros');
    }
};
