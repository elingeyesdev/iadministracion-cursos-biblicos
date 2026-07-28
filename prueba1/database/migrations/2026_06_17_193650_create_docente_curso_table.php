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
        Schema::create('docente_curso', function (Blueprint $table) {
            $table->foreignId('id_docente')->constrained('docentes')->cascadeOnDelete();
            $table->foreignId('id_curso')->constrained('cursos')->cascadeOnDelete();
            $table->primary(['id_docente', 'id_curso']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente_curso');
    }
};
