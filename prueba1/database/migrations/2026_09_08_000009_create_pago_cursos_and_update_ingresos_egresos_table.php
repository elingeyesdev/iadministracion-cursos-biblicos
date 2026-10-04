<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pago_cursos')) {
            Schema::create('pago_cursos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inscripcion_id')->constrained('inscripciones')->onDelete('cascade');
                $table->decimal('monto', 10, 2);
                $table->date('fecha_pago');
                $table->string('metodo_pago');
                $table->string('comprobante')->nullable();
                $table->foreignId('registrado_por')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('ingresos', 'id_actividad')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->foreignId('id_actividad')->nullable()->after('id')
                      ->constrained('actividades')->onDelete('set null');
            });
        }

        if (Schema::hasColumn('ingresos', 'id_culto')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->dropForeign(['id_culto']);
                $table->foreignId('id_culto')->nullable()->change();
                $table->foreign('id_culto')->references('id_culto')->on('cultos')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('ingresos', 'pago_curso_id')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->foreignId('pago_curso_id')->nullable()->after('id_culto')
                      ->constrained('pago_cursos')->onDelete('set null');
            });
        }

        Schema::table('ingresos', function (Blueprint $table) {
            $table->decimal('monto_total', 12, 2)->change();
        });

        if (!Schema::hasColumn('ingresos', 'deleted_at')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('egresos', 'deleted_at')) {
            Schema::table('egresos', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('egresos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('ingresos', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('ingresos', 'pago_curso_id')) {
                $table->dropConstrainedForeignId('pago_curso_id');
            }
            if (Schema::hasColumn('ingresos', 'id_actividad')) {
                $table->dropConstrainedForeignId('id_actividad');
            }
            $table->decimal('monto_total', 10, 2)->change();
        });

        Schema::dropIfExists('pago_cursos');
    }
};