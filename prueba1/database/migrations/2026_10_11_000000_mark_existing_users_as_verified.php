<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Al introducir la verificación de correo obligatoria, las cuentas creadas
     * antes de esta funcionalidad se marcan como verificadas para no dejar a
     * nadie bloqueado sin acceso al sistema.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    /**
     * No se revierte: no hay forma de saber qué cuentas estaban sin verificar
     * antes de esta migración.
     */
    public function down(): void
    {
        //
    }
};
