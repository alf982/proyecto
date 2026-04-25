<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('periodo_contable_id')->constrained('periodos_contables')->restrictOnDelete();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->restrictOnDelete();
            $table->enum('tipo', ['manual','apertura','ajuste','cierre','reclasificacion'])->default('manual');
            $table->string('concepto', 500);
            $table->date('fecha_asiento');
            $table->decimal('total_debe', 18, 2)->default(0);
            $table->decimal('total_haber', 18, 2)->default(0);
            $table->enum('estado', ['borrador','registrado','anulado'])->default('borrador');
            $table->string('motivo_anulacion')->nullable();
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamp('fecha_anulacion')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos_contables');
    }
};
