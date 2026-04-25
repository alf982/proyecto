<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('modificaciones_presupuestarias', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales');
            $table->enum('tipo', ['traspaso','credito_adicional','reduccion'])->default('traspaso');
            $table->string('concepto', 500);
            $table->decimal('monto', 18, 2);
            // Para traspasos: crédito que cede y crédito que recibe
            $table->foreignId('credito_origen_id')->nullable()->constrained('creditos_presupuestarios');
            $table->foreignId('credito_destino_id')->nullable()->constrained('creditos_presupuestarios');
            $table->date('fecha_modificacion');
            $table->enum('estado', ['borrador','aprobada','anulada'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('modificaciones_presupuestarias'); }
};
