<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conciliaciones_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('cuenta_bancaria_id')->constrained('cuentas_bancarias')->cascadeOnDelete();
            $table->foreignId('ejercicio_fiscal_id')->nullable()->constrained('ejercicios_fiscales')->nullOnDelete();
            $table->year('anio');
            $table->tinyInteger('mes');   // 1-12
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->decimal('saldo_segun_banco', 18, 2)->default(0);
            $table->decimal('saldo_segun_libros', 18, 2)->default(0);
            $table->decimal('diferencia', 18, 2)->default(0);
            $table->enum('estado', ['borrador', 'aprobada', 'anulada'])->default('borrador');
            $table->date('fecha_aprobacion')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cuenta_bancaria_id', 'anio', 'mes']);
        });

        // Detalle: cada movimiento del banco vs movimiento interno
        Schema::create('conciliacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conciliacion_bancaria_id')->constrained('conciliaciones_bancarias')->cascadeOnDelete();
            $table->foreignId('movimiento_bancario_id')->nullable()->constrained('movimientos_bancarios')->nullOnDelete();
            $table->enum('tipo_partida', ['en_banco_no_en_libros', 'en_libros_no_en_banco', 'conciliado'])->default('conciliado');
            $table->string('descripcion', 300)->nullable();
            $table->decimal('monto', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('conciliacion_detalles');
        Schema::dropIfExists('conciliaciones_bancarias');
    }
};
