<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('cuenta_bancaria_id')->constrained('cuentas_bancarias')->cascadeOnDelete();
            $table->enum('tipo', ['debito', 'credito', 'transferencia_entrada', 'transferencia_salida', 'nota_debito', 'nota_credito'])->default('debito');
            $table->string('concepto', 300);
            $table->decimal('monto', 18, 2);
            $table->date('fecha_movimiento');
            $table->date('fecha_valor')->nullable();
            $table->string('referencia', 100)->nullable();    // N° cheque / transferencia
            $table->string('banco_origen', 100)->nullable();  // para transferencias
            $table->string('cuenta_origen', 30)->nullable();
            $table->string('beneficiario_nombre', 200)->nullable();
            $table->string('beneficiario_rif', 15)->nullable();
            $table->enum('origen', ['pago', 'ingreso', 'manual', 'conciliacion'])->default('manual');
            $table->nullableMorphs('origen_modelo'); // polymorphic: pago_id, etc.
            $table->enum('estado', ['pendiente', 'conciliado', 'anulado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->string('motivo_anulacion', 300)->nullable();
            $table->foreignId('ejercicio_fiscal_id')->nullable()->constrained('ejercicios_fiscales')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cuenta_bancaria_id', 'fecha_movimiento']);
            $table->index('estado');
        });
    }

    public function down(): void { Schema::dropIfExists('movimientos_bancarios'); }
};
