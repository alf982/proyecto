<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('solicitudes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->restrictOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->restrictOnDelete();
            $table->string('motivo', 500);
            $table->enum('tipo', ['bienes','servicios','mixta'])->default('bienes');
            $table->enum('prioridad', ['baja','media','alta','urgente'])->default('media');
            $table->date('fecha_requerida')->nullable();
            $table->enum('estado', ['borrador','enviada','revisada','aprobada','rechazada','procesada'])->default('borrador');
            $table->string('motivo_rechazo')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('solicitado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('solicitudes_compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_compra_id')->constrained('solicitudes_compra')->cascadeOnDelete();
            $table->foreignId('articulo_id')->nullable()->constrained('articulos')->nullOnDelete();
            $table->string('descripcion', 300);
            $table->string('unidad_medida', 30)->default('unidad');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_estimado', 18, 2)->default(0);
            $table->string('especificaciones')->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('solicitudes_compra_detalle');
        Schema::dropIfExists('solicitudes_compra');
    }
};
