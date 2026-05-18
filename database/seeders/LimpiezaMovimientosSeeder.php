<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LimpiezaMovimientosSeeder extends Seeder
{
    /**
     * Elimina todos los movimientos transaccionales, partidas presupuestarias
     * y ejercicios fiscales. Preserva: usuarios, roles, permisos, catálogos
     * de artículos, proveedores, empleados, cuentas bancarias y configuración.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ── Contabilidad ───────────────────────────────────────────────────
        DB::table('asientos_detalle')->truncate();
        DB::table('asientos_contables')->truncate();
        DB::table('conciliacion_detalles')->truncate();
        DB::table('conciliaciones_bancarias')->truncate();
        DB::table('arqueos_caja')->truncate();

        // ── Nómina ─────────────────────────────────────────────────────────
        DB::table('nominas_detalle')->truncate();
        DB::table('nominas')->truncate();

        // ── Retenciones ────────────────────────────────────────────────────
        DB::table('retenciones_aplicadas')->truncate();
        DB::table('retenciones')->truncate();

        // ── Pagos y órdenes de pago ────────────────────────────────────────
        DB::table('ordenes_pago_detalle')->truncate();
        DB::table('ordenes_pago')->truncate();
        DB::table('pagos')->truncate();

        // ── Causaciones y compromisos ──────────────────────────────────────
        DB::table('causaciones')->truncate();
        DB::table('compromisos')->truncate();

        // ── Órdenes de compra ──────────────────────────────────────────────
        DB::table('ordenes_compra_detalle')->truncate();
        DB::table('ordenes_compra')->truncate();

        // ── Solicitudes ────────────────────────────────────────────────────
        DB::table('solicitudes_compra_detalle')->truncate();
        DB::table('solicitudes_compra')->truncate();
        DB::table('solicitudes_despacho_detalle')->truncate();
        DB::table('solicitudes_despacho')->truncate();

        // ── Recepciones de bienes ──────────────────────────────────────────
        DB::table('recepciones_detalle')->truncate();
        DB::table('recepciones_bienes')->truncate();

        // ── Ingresos ───────────────────────────────────────────────────────
        DB::table('ingresos')->truncate();

        // ── Inventario ─────────────────────────────────────────────────────
        DB::table('inventario_movimientos')->truncate();
        DB::table('movimientos_bien')->truncate();

        // ── Presupuesto ────────────────────────────────────────────────────
        DB::table('movimientos_partidas')->truncate();
        DB::table('modificaciones_presupuestarias')->truncate();
        DB::table('creditos_presupuestarios')->truncate();
        DB::table('periodos_contables')->truncate();
        DB::table('partidas_presupuestarias')->truncate();

        // ── Banco ──────────────────────────────────────────────────────────
        DB::table('movimientos_bancarios')->truncate();

        // ── Proyectos ──────────────────────────────────────────────────────
        DB::table('proyectos_sia')->truncate();

        // ── Auditoría ──────────────────────────────────────────────────────
        DB::table('audits')->truncate();

        // ── Ejercicios fiscales (padre de todo, al final) ──────────────────
        DB::table('ejercicios_fiscales')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ Movimientos, partidas y ejercicios fiscales eliminados.');
        $this->command->info('✓ Preservados: usuarios, roles, permisos, catálogos, empleados y configuración.');
    }
}
