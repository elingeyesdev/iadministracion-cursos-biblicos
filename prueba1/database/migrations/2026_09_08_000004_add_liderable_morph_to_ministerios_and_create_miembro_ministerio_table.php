<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ministerios', 'liderable_type')) {
            Schema::table('ministerios', function (Blueprint $table) {
                $table->nullableMorphs('liderable');
            });
        }

        if (!Schema::hasTable('miembro_ministerio')) {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('miembro_ministerio');

        if (Schema::hasColumn('ministerios', 'liderable_type')) {
            Schema::table('ministerios', function (Blueprint $table) {
                $table->dropMorphs('liderable');
            });
        }
    }
};