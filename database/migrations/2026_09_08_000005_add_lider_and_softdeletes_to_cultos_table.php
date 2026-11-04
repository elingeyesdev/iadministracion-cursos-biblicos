<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cultos', 'id_lider_iglesia')) {
            Schema::table('cultos', function (Blueprint $table) {
                $table->foreignId('id_lider_iglesia')->nullable()->after('nombre')
                      ->constrained('lideres_iglesia')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('cultos', 'deleted_at')) {
            Schema::table('cultos', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('cultos', function (Blueprint $table) {
            if (Schema::hasColumn('cultos', 'id_lider_iglesia')) {
                $table->dropConstrainedForeignId('id_lider_iglesia');
            }
            $table->dropSoftDeletes();
        });
    }
};