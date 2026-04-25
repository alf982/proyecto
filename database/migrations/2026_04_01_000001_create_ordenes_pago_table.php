<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes_pago', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->restrictOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->restrictOnDelete();
            $table->foreignId('beneficiario_id')->nullable()->constrained('beneficiarios')->nullOnDelete();
            $table->foreignId('causacion_id')->nullable()->constrained('causaciones')->nullOnDelete();
            $table->string('concepto', 500);
            $table->decimal('monto_total', 18, 2);
            $table->enum('tipo_pago', ['cheque', 'transferencia', 'efectivo'])->default('transferencia');
            $table->enum('estado', ['borrador', 'revisada', 'aprobada', 'enviada', 'pagada', 'anulada'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->string('motivo_anulacion')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ordenes_pago_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_pago_id')->constrained('ordenes_pago')->cascadeOnDelete();
            $table->string('descripcion', 300);
            $table->decimal('monto', 18, 2);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ordenes_pago_detalle');
        Schema::dropIfExists('ordenes_pago');
    }
};
