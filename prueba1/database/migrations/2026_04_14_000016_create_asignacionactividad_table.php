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
        Schema::create('asignacionactividad', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_actividad')
            ->constrained('actividad')
            ->onDelete('cascade');

            $table->foreignId('id_miembro')
            ->constrained('miembro')
            ->onDelete('cascade');

            $table->string('rol_en_actividad')->nullable();
            $table->date('fecha_asignacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacionactividad');
    }
};
