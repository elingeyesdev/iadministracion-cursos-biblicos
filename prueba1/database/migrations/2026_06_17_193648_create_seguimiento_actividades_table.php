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
        Schema::create('seguimiento_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_actividad')->constrained('actividades')->cascadeOnDelete();
            $table->date('fecha_registro');
            $table->string('estado', 50);
            $table->text('observacion')->nullable();
            $table->string('responsable', 100)->nullable();
            $table->decimal('porcentaje_avance', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_actividades');
    }
};
