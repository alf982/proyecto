<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('conceptos_ingreso', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('tipo', ['tasa', 'multa', 'devolucion', 'transferencia', 'intereses', 'otro'])->default('tasa');
            $table->boolean('activo')->default(true);
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->restrictOnDelete();
            $table->enum('estado', ['abierta', 'cerrada'])->default('cerrada');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo', 25)->unique();
            $table->foreignId('caja_id')->constrained('cajas')->restrictOnDelete();
            $table->foreignId('concepto_ingreso_id')->constrained('conceptos_ingreso')->restrictOnDelete();
            $table->decimal('monto', 18, 2);
            $table->enum('forma_pago', ['efectivo', 'transferencia', 'cheque', 'punto'])->default('efectivo');
            $table->string('referencia_bancaria', 100)->nullable();
            $table->string('pagador_nombre', 200)->nullable();
            $table->string('pagador_rif', 15)->nullable();
            $table->date('fecha');
            $table->enum('estado', ['registrado', 'anulado'])->default('registrado');
            $table->string('motivo_anulacion')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('arqueos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->restrictOnDelete();
            $table->date('fecha');
            $table->decimal('monto_apertura', 18, 2)->default(0);
            $table->decimal('total_ingresos', 18, 2)->default(0);
            $table->decimal('monto_cierre', 18, 2)->default(0);
            $table->decimal('diferencia', 18, 2)->default(0);
            $table->enum('estado', ['abierto', 'cerrado', 'aprobado'])->default('abierto');
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('arqueos_caja');
        Schema::dropIfExists('ingresos');
        Schema::dropIfExists('cajas');
        Schema::dropIfExists('conceptos_ingreso');
    }
};
