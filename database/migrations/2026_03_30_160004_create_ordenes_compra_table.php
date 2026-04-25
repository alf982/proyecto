<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('solicitud_compra_id')->nullable()->constrained('solicitudes_compra')->nullOnDelete();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->restrictOnDelete();
            $table->foreignId('beneficiario_id')->nullable()->constrained('beneficiarios')->nullOnDelete();
            $table->string('proveedor_nombre', 200)->nullable();
            $table->string('proveedor_rif', 20)->nullable();
            $table->string('concepto', 500);
            $table->date('fecha_emision');
            $table->date('fecha_entrega_estimada')->nullable();
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(16);
            $table->decimal('iva_monto', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->enum('estado', ['emitida','confirmada','en_transito','completada','anulada'])->default('emitida');
            $table->enum('modalidad', ['compra_directa','concurso','licitacion'])->default('compra_directa');
            $table->string('numero_contrato')->nullable();
            $table->string('motivo_anulacion')->nullable();
            $table->text('condiciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ordenes_compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('articulo_id')->nullable()->constrained('articulos')->nullOnDelete();
            $table->string('descripcion', 300);
            $table->string('unidad_medida', 30)->default('unidad');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 18, 2);
            $table->decimal('subtotal', 18, 2);
            $table->decimal('cantidad_recibida', 12, 2)->default(0);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ordenes_compra_detalle');
        Schema::dropIfExists('ordenes_compra');
    }
};
