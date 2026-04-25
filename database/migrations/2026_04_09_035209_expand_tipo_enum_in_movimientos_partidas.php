<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Amplía el ENUM 'tipo' en movimientos_partidas añadiendo
     * 'compromiso', 'causacion' y 'pago', necesarios para el
     * flujo presupuestario integrado (sin Créditos Presupuestarios).
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE movimientos_partidas
            MODIFY COLUMN tipo ENUM(
                'asignacion',
                'credito_adicional',
                'modificacion_entrada',
                'modificacion_salida',
                'ejecucion',
                'reintegro',
                'nota_credito',
                'nota_debito',
                'compromiso',
                'causacion',
                'pago'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE movimientos_partidas
            MODIFY COLUMN tipo ENUM(
                'asignacion',
                'credito_adicional',
                'modificacion_entrada',
                'modificacion_salida',
                'ejecucion',
                'reintegro',
                'nota_credito',
                'nota_debito'
            ) NOT NULL
        ");
    }
};
