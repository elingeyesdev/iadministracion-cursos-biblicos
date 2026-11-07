<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministerios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            // Relación polimórfica para liderable
            $table->nullableMorphs('liderable');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('miembro_ministerio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('miembro_id')->constrained('miembros')->onDelete('cascade');
            $table->foreignId('ministerio_id')->constrained('ministerios')->onDelete('cascade');
            $table->foreignId('cargo_id')->constrained('cargos')->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ministerio')->constrained('ministerios')->onDelete('cascade');
            $table->string('nombre');
            $table->date('fecha');
            $table->string('lugar')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('Programado');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('seguimiento_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_actividad')->constrained('actividades')->onDelete('cascade');
            $table->date('fecha_registro');
            $table->text('observacion')->nullable();
            $table->string('responsable');
            $table->decimal('porcentaje_avance', 5, 2)->default(0);
            $table->string('estado')->default('En Progreso');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_actividades');
        Schema::dropIfExists('actividades');
        Schema::dropIfExists('miembro_ministerio');
        Schema::dropIfExists('ministerios');
    }
};
