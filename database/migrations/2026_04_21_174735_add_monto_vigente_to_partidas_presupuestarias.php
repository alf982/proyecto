<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            // Crédito vigente autorizado = aprobado ± modificaciones presupuestarias formales
            $table->decimal('monto_vigente', 18, 2)->default(0)->after('monto_aprobado')
                  ->comment('Aprobado +- creditos adicionales y modificaciones formales');
        });

        // Inicializar monto_vigente para partidas existentes recalculando desde movimientos
        DB::statement("
            UPDATE partidas_presupuestarias p
            SET monto_vigente = COALESCE((
                SELECT
                    SUM(CASE WHEN tipo IN ('asignacion','credito_adicional','modificacion_entrada')
                             THEN monto ELSE 0 END)
                  - SUM(CASE WHEN tipo = 'modificacion_salida'
                             THEN monto ELSE 0 END)
                FROM movimientos_partidas mp
                WHERE mp.partida_presupuestaria_id = p.id
                  AND mp.estado = 'confirmado'
                  AND mp.deleted_at IS NULL
            ), p.monto_aprobado)
        ");
    }

    public function down(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            $table->dropColumn('monto_vigente');
        });
    }
};
