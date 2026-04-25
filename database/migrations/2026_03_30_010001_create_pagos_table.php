<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('causacion_id')->constrained('causaciones');
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales');
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras');
            $table->string('beneficiario', 200);
            $table->string('rif_beneficiario', 20)->nullable();
            $table->enum('tipo_pago', ['cheque','transferencia','efectivo','otro'])->default('transferencia');
            $table->string('numero_referencia', 60)->nullable()->comment('N° cheque / referencia bancaria');
            $table->string('banco', 100)->nullable();
            $table->string('cuenta_bancaria', 30)->nullable();
            $table->decimal('monto_pagado', 18, 2);
            $table->date('fecha_pago');
            $table->string('concepto', 500);
            $table->enum('estado', ['pendiente','procesado','anulado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('pagos'); }
};
