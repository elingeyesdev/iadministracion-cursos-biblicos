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
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_culto')
                  ->constrained('cultos','id_culto')
                  ->cascadeOnDelete();
            $table->decimal('monto_total', 10, 2);
            $table->date('fecha');
            $table->string('tipo', 100)->nullable();
            $table->string('metodo_pago', 100)->nullable(); // efectivo, transferencia, otro
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
