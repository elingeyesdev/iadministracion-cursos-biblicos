<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('docentes', 'deleted_at')) {
            Schema::table('docentes', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('contratos', 'id_lider_iglesia')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->foreignId('id_lider_iglesia')->nullable()->after('id_miembro')
                      ->constrained('lideres_iglesia')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('contratos', 'tipo_compensacion')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->string('tipo_compensacion')->default('Salario')->after('id_lider_iglesia');
            });
        }

        if (!Schema::hasColumn('contratos', 'salario')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->decimal('salario', 10, 2)->default(0)->after('tipo_compensacion');
            });
        }

        if (!Schema::hasColumn('contratos', 'activo')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('fecha_fin');
            });
        }

        if (!Schema::hasColumn('contratos', 'deleted_at')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('contratos', function (Blueprint $table) {
            if (Schema::hasColumn('contratos', 'id_lider_iglesia')) {
                $table->dropConstrainedForeignId('id_lider_iglesia');
            }
            if (Schema::hasColumn('contratos', 'tipo_compensacion')) {
                $table->dropColumn(['tipo_compensacion', 'salario', 'activo']);
            }
            $table->dropSoftDeletes();
        });
    }
};