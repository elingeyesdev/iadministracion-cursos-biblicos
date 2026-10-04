<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('actividades', 'deleted_at')) {
            Schema::table('actividades', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('seguimiento_actividades', 'deleted_at')) {
            Schema::table('seguimiento_actividades', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('seguimiento_actividades', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};