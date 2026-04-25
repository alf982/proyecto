<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recepciones_bienes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->restrictOnDelete();
            $table->date('fecha_recepcion');
            $table->string('recibido_por', 150);
            $table->string('entregado_por', 150)->nullable();
            $table->string('numero_guia', 60)->nullable();
            $table->string('numero_factura', 60)->nullable();
            $table->decimal('total_recibido', 18, 2)->default(0);
            $table->enum('estado', ['conforme','no_conforme','parcial'])->default('conforme');
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('recepciones_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')->constrained('recepciones_bienes')->cascadeOnDelete();
            $table->foreignId('orden_detalle_id')->constrained('ordenes_compra_detalle')->restrictOnDelete();
            $table->foreignId('articulo_id')->nullable()->constrained('articulos')->nullOnDelete();
            $table->decimal('cantidad_recibida', 12, 2);
            $table->decimal('precio_unitario', 18, 2)->default(0);
            $table->enum('condicion', ['bueno','malo','incompleto'])->default('bueno');
            $table->string('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos')->restrictOnDelete();
            $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();
            $table->enum('tipo', ['entrada','salida','ajuste','traslado'])->default('entrada');
            $table->string('origen_tipo')->nullable(); // 'recepcion', 'manual', etc.
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 18, 2)->default(0);
            $table->decimal('stock_anterior', 12, 2)->default(0);
            $table->decimal('stock_nuevo', 12, 2)->default(0);
            $table->string('concepto', 300);
            $table->date('fecha');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('recepciones_detalle');
        Schema::dropIfExists('recepciones_bienes');
    }
};
