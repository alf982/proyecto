<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('causaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique()->comment('Nro. de la causación/orden de pago');
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales');
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras');
            $table->foreignId('credito_presupuestario_id')->constrained('creditos_presupuestarios');
            $table->foreignId('partida_presupuestaria_id')->constrained('partidas_presupuestarias');
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos_sia');

            // Beneficiario
            $table->string('beneficiario', 200)->comment('Proveedor o beneficiario');
            $table->string('rif_beneficiario', 20)->nullable();

            // Documento soporte
            $table->string('tipo_documento', 30)->default('factura')
                ->comment('factura, contrato, recibo, planilla, otro');
            $table->string('numero_documento', 60)->nullable();
            $table->date('fecha_documento')->nullable();
            $table->string('descripcion_documento', 300)->nullable();

            // Montos
            $table->decimal('monto_causado', 18, 2)->default(0);
            $table->decimal('monto_retencion', 18, 2)->default(0)->comment('ISLR, IVA u otras retenciones');
            $table->decimal('monto_neto', 18, 2)->storedAs('monto_causado - monto_retencion');

            // Control
            $table->string('concepto', 500)->comment('Descripción del gasto');
            $table->enum('estado', ['borrador','aprobada','pagada','anulada'])->default('borrador');
            $table->date('fecha_causacion');
            $table->date('fecha_aprobacion')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('causaciones');
    }
};
