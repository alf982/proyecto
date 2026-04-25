<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agregar numero_referencia y cuenta_bancaria a ordenes_pago para el pago
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->string('numero_referencia', 60)->nullable()->after('tipo_pago');
            $table->string('banco', 100)->nullable()->after('numero_referencia');
            $table->string('cuenta_bancaria_num', 30)->nullable()->after('banco');
            $table->date('fecha_pago')->nullable()->after('cuenta_bancaria_num');
        });

        // Agregar referencia a la orden de pago en pagos (para trazabilidad)
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('orden_pago_id')->nullable()
                  ->after('causacion_id')
                  ->constrained('ordenes_pago')
                  ->nullOnDelete();
            $table->boolean('generado_automatico')->default(false)->after('orden_pago_id');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['orden_pago_id']);
            $table->dropColumn(['orden_pago_id', 'generado_automatico']);
        });
        Schema::table('ordenes_pago', function (Blueprint $table) {
            $table->dropColumn(['numero_referencia', 'banco', 'cuenta_bancaria_num', 'fecha_pago']);
        });
    }
};
