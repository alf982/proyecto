<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega ejercicio_fiscal_id (nullable) a las tablas transaccionales
     * que aún no la tienen. No toca catálogos, empleados, beneficiarios
     * ni partidas_presupuestarias (son datos globales permanentes).
     */
    public function up(): void
    {
        // ── Solicitudes de Despacho (almacén interno) ────────────────────
        Schema::table('solicitudes_despacho', function (Blueprint $table) {
            $table->foreignId('ejercicio_fiscal_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('ejercicios_fiscales')
                  ->nullOnDelete();
        });

        // ── Recepciones de Bienes ────────────────────────────────────────
        Schema::table('recepciones_bienes', function (Blueprint $table) {
            $table->foreignId('ejercicio_fiscal_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('ejercicios_fiscales')
                  ->nullOnDelete();
        });

        // ── Inventario Movimientos ───────────────────────────────────────
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->foreignId('ejercicio_fiscal_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('ejercicios_fiscales')
                  ->nullOnDelete();
        });

        // ── Ingresos ─────────────────────────────────────────────────────
        if (Schema::hasTable('ingresos') && !Schema::hasColumn('ingresos', 'ejercicio_fiscal_id')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->foreignId('ejercicio_fiscal_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('ejercicios_fiscales')
                      ->nullOnDelete();
            });
        }

        // ── Bienes Nacionales ────────────────────────────────────────────
        if (Schema::hasTable('bienes') && !Schema::hasColumn('bienes', 'ejercicio_fiscal_id')) {
            Schema::table('bienes', function (Blueprint $table) {
                $table->foreignId('ejercicio_fiscal_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('ejercicios_fiscales')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'solicitudes_despacho'  => 'ejercicio_fiscal_id',
            'recepciones_bienes'    => 'ejercicio_fiscal_id',
            'inventario_movimientos'=> 'ejercicio_fiscal_id',
            'ingresos'              => 'ejercicio_fiscal_id',
            'bienes'                => 'ejercicio_fiscal_id',
        ];

        foreach ($drops as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $t) use ($column, $table) {
                    $t->dropForeignIdFor(\App\Models\EjercicioFiscal::class);
                    $t->dropColumn($column);
                });
            }
        }
    }
};
