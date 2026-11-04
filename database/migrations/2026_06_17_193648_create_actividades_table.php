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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ministerio')->constrained('ministerios')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->date('fecha');
            $table->string('lugar', 200)->nullable();
            $table->enum('estado', ['planificada', 'en_curso', 'completada', 'cancelada'])->default('planificada');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
