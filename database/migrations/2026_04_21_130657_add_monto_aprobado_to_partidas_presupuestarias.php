<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            // Monto presupuestario aprobado original — nunca cambia después de asignado
            $table->decimal('monto_aprobado', 18, 2)->default(0)->after('saldo_actual');
        });

        // Inicializar monto_aprobado con el saldo_actual existente
        // (para partidas que ya tenían saldo cargado)
        DB::statement('UPDATE partidas_presupuestarias SET monto_aprobado = saldo_actual WHERE saldo_actual > 0');
    }

    public function down(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            $table->dropColumn('monto_aprobado');
        });
    }
};
