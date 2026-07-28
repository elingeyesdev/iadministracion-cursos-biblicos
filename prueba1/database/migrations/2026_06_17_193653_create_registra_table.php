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
        Schema::create('registra', function (Blueprint $table) {
            $table->foreignId('id_miembro')->constrained('miembros')->cascadeOnDelete();
            $table->foreignId('id_sesion')->constrained('sesiones')->cascadeOnDelete();
            $table->enum('estado', ['presente', 'ausente', 'justificado'])->default('presente');
            $table->text('observacion')->nullable();
            $table->primary(['id_miembro', 'id_sesion']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registra');
    }
};
