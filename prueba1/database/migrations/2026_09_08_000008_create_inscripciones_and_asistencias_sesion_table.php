<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inscripciones')) {
            Schema::create('inscripciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('miembro_id')->constrained('miembros')->onDelete('cascade');
                $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
                $table->date('fecha_inscripcion');
                $table->string('estado')->default('Activo');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('asistencias_sesion')) {
            Schema::create('asistencias_sesion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('miembro_id')->constrained('miembros')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('sesion_id')->constrained('sesiones')->onDelete('cascade');
                $table->string('estado');
                $table->text('observacion')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_sesion');
        Schema::dropIfExists('inscripciones');
    }
};