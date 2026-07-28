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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ministerio')->nullable()->constrained('ministerios')->nullOnDelete();
            $table->foreignId('id_contrato')->nullable()->constrained('contratos')->nullOnDelete();
            $table->string('tipo_egreso', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('responsable', 100)->nullable();
            $table->string('comprobante', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
