<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cursos', 'dias_semana')) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->json('dias_semana')->nullable()->after('cupo_max');
                $table->time('hora_inicio')->nullable()->after('dias_semana');
                $table->time('hora_fin')->nullable()->after('hora_inicio');
            });
        }

        if (!Schema::hasColumn('cursos', 'deleted_at')) {
            Schema::table('cursos', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        Schema::table('cursos', function (Blueprint $table) {
            $table->integer('cupo_max')->nullable()->default(30)->change();
            $table->decimal('monto_inscripcion', 10, 2)->nullable()->default(0.00)->change();
        });

        if (!Schema::hasColumn('docente_curso', 'deleted_at')) {
            Schema::table('docente_curso', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('sesiones', 'id_docente')) {
            Schema::table('sesiones', function (Blueprint $table) {
                $table->foreignId('id_docente')->nullable()->after('id_curso')
                      ->constrained('docentes')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('sesiones', 'deleted_at')) {
            Schema::table('sesiones', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            if (Schema::hasColumn('cursos', 'dias_semana')) {
                $table->dropColumn(['dias_semana', 'hora_inicio', 'hora_fin']);
            }
            $table->dropSoftDeletes();
        });

        Schema::table('docente_curso', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('sesiones', function (Blueprint $table) {
            if (Schema::hasColumn('sesiones', 'id_docente')) {
                $table->dropConstrainedForeignId('id_docente');
            }
            $table->dropSoftDeletes();
        });
    }
};