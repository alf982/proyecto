<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_partidas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();   // MP-2026-0001

            // Partida principal
            $table->foreignId('partida_presupuestaria_id')
                  ->constrained('partidas_presupuestarias')
                  ->cascadeOnDelete();

            // Para modificaciones: la partida contrapartida
            $table->foreignId('partida_contrapartida_id')
                  ->nullable()
                  ->constrained('partidas_presupuestarias')
                  ->nullOnDelete();

            // Apunta al movimiento espejo (modificacion_salida <-> modificacion_entrada)
            $table->unsignedBigInteger('movimiento_relacionado_id')->nullable();

            // Contexto
            $table->foreignId('cuenta_bancaria_id')
                  ->nullable()
                  ->constrained('cuentas_bancarias')
                  ->nullOnDelete();
            $table->foreignId('ejercicio_fiscal_id')
                  ->nullable()
                  ->constrained('ejercicios_fiscales')
                  ->nullOnDelete();

            // Tipo de movimiento
            $table->enum('tipo', [
                'asignacion',
                'credito_adicional',
                'modificacion_entrada',
                'modificacion_salida',
                'ejecucion',
                'reintegro',
                'nota_credito',
                'nota_debito',
            ]);

            // Datos del movimiento
            $table->string('concepto', 300);
            $table->decimal('monto', 18, 2);
            $table->date('fecha_movimiento');
            $table->string('referencia', 100)->nullable();

            // Saldos snapshot
            $table->decimal('saldo_anterior', 18, 2)->default(0);
            $table->decimal('saldo_posterior', 18, 2)->default(0);

            // Estado
            $table->enum('estado', ['pendiente', 'confirmado', 'anulado'])->default('pendiente');
            $table->text('motivo_anulacion')->nullable();

            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // FK auto-referencial después de crear la tabla
            $table->foreign('movimiento_relacionado_id')
                  ->references('id')
                  ->on('movimientos_partidas')
                  ->nullOnDelete();

            $table->index('partida_presupuestaria_id');
            $table->index('tipo');
            $table->index('fecha_movimiento');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_partidas');
    }
};
