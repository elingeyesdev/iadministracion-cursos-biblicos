<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cargos')) {
            Schema::create('cargos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->integer('nivel_jerarquico')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('lideres_iglesia')) {
            Schema::create('lideres_iglesia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_cargo')->constrained('cargos')->onDelete('cascade');
                $table->string('nombre');
                $table->string('telefono')->nullable();
                $table->date('fecha_inicio');
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lideres_iglesia');
        Schema::dropIfExists('cargos');
    }
};