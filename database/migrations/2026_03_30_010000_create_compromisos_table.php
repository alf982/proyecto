<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('compromisos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales');
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras');
            $table->foreignId('credito_presupuestario_id')->constrained('creditos_presupuestarios');
            $table->foreignId('partida_presupuestaria_id')->constrained('partidas_presupuestarias');
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos_sia');
            $table->string('beneficiario', 200);
            $table->string('rif_beneficiario', 20)->nullable();
            $table->string('concepto', 500);
            $table->decimal('monto', 18, 2);
            $table->date('fecha_compromiso');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estado', ['borrador','aprobado','causado','anulado'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('compromisos'); }
};
