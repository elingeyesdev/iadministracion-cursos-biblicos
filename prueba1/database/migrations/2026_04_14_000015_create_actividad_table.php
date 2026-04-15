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
        Schema::create('actividad', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->text('descripcion')->nullable();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->enum('estado', ['pendiente', 'en_proceso', 'finalizada'])->default('pendiente');

            $table->foreignId('id_categoria')->constrained('categoriaactividad')->onDelete('cascade');
            $table->foreignId('id_responsable')->constrained('miembro')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad');
    }
};
