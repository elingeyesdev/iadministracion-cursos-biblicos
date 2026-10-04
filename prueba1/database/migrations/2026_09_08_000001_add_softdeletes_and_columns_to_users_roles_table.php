<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('roles', 'nivel_acceso')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->integer('nivel_acceso')->default(1)->after('estado');
                $table->string('guard_name')->default('web')->after('nivel_acceso');
            });
        }

        if (!Schema::hasColumn('roles', 'deleted_at')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('role_user', 'deleted_at')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        if (Schema::hasColumn('roles', 'nivel_acceso')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn(['nivel_acceso', 'guard_name']);
            });
        }

        Schema::table('roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};