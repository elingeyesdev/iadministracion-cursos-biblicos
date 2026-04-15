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
        Schema::create('seguimiento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_actividad')
            ->constrained('actividad')
            ->onDelete('cascade');

            $table->date('fecha');
            $table->text('comentario')->nullable();

            $table->integer('porcentaje_avance')->default(0); // 0 a 100
            $table->enum('estado', ['pendiente', 'en_proceso', 'finalizada'])->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento');
    }
};
