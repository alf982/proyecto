<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1 — Optimización de BD: añade índices en columnas de filtrado frecuente.
 *
 * Las FK (ejercicio_fiscal_id, unidad_ejecutora_id, partida_presupuestaria_id)
 * ya cuentan con índice implícito por constraintId; aquí añadimos los compuestos
 * más usados en los WHERE / ORDER BY de los listados y reportes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── compromisos ──────────────────────────────────────────────────
        Schema::table('compromisos', function (Blueprint $table) {
            // Filtro por ejercicio + estado (index más usado en index() y reportes)
            $table->index(['ejercicio_fiscal_id', 'estado'], 'compromisos_ejercicio_estado_idx');
            // Filtro por partida + estado (reporte por partida)
            $table->index(['partida_presupuestaria_id', 'estado'], 'compromisos_partida_estado_idx');
            // Filtro por unidad + estado
            $table->index(['unidad_ejecutora_id', 'estado'], 'compromisos_unidad_estado_idx');
            // Búsqueda por número (ya tiene unique pero añadimos para like con prefix)
            // numero ya tiene unique index — no duplicar
        });

        // ── causaciones ──────────────────────────────────────────────────
        Schema::table('causaciones', function (Blueprint $table) {
            $table->index(['ejercicio_fiscal_id', 'estado'], 'causaciones_ejercicio_estado_idx');
            $table->index(['partida_presupuestaria_id', 'estado'], 'causaciones_partida_estado_idx');
            $table->index(['unidad_ejecutora_id', 'estado'], 'causaciones_unidad_estado_idx');
            $table->index('compromiso_id', 'causaciones_compromiso_idx');
        });

        // ── pagos ────────────────────────────────────────────────────────
        Schema::table('pagos', function (Blueprint $table) {
            $table->index(['ejercicio_fiscal_id', 'estado'], 'pagos_ejercicio_estado_idx');
            $table->index(['causacion_id', 'estado'], 'pagos_causacion_estado_idx');
            $table->index('estado', 'pagos_estado_idx');
        });

        // ── movimientos_partidas ─────────────────────────────────────────
        Schema::table('movimientos_partidas', function (Blueprint $table) {
            $table->index(['ejercicio_fiscal_id', 'estado'], 'movimientos_ejercicio_estado_idx');
            $table->index(['partida_presupuestaria_id', 'estado'], 'movimientos_partida_estado_idx');
            $table->index(['fecha_movimiento', 'tipo'], 'movimientos_fecha_tipo_idx');
        });

        // ── retenciones_aplicadas ────────────────────────────────────────
        Schema::table('retenciones_aplicadas', function (Blueprint $table) {
            // Índice compuesto polimórfico para acelerar las relaciones morphMany
            $table->index(['retencionable_type', 'retencionable_id'], 'retenciones_morph_idx');
        });
    }

    public function down(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->dropIndex('compromisos_ejercicio_estado_idx');
            $table->dropIndex('compromisos_partida_estado_idx');
            $table->dropIndex('compromisos_unidad_estado_idx');
        });

        Schema::table('causaciones', function (Blueprint $table) {
            $table->dropIndex('causaciones_ejercicio_estado_idx');
            $table->dropIndex('causaciones_partida_estado_idx');
            $table->dropIndex('causaciones_unidad_estado_idx');
            $table->dropIndex('causaciones_compromiso_idx');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex('pagos_ejercicio_estado_idx');
            $table->dropIndex('pagos_causacion_estado_idx');
            $table->dropIndex('pagos_estado_idx');
        });

        Schema::table('movimientos_partidas', function (Blueprint $table) {
            $table->dropIndex('movimientos_ejercicio_estado_idx');
            $table->dropIndex('movimientos_partida_estado_idx');
            $table->dropIndex('movimientos_fecha_tipo_idx');
        });

        Schema::table('retenciones_aplicadas', function (Blueprint $table) {
            $table->dropIndex('retenciones_morph_idx');
        });
    }
};
