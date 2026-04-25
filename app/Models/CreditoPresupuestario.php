<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CreditoPresupuestario extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'creditos_presupuestarios';

    protected $fillable = [
        'ejercicio_fiscal_id', 'partida_presupuestaria_id', 'unidad_ejecutora_id',
        'fuente_financiamiento_id', 'proyecto_sia_id', 'actividad_id',
        'monto_aprobado', 'monto_modificado', 'monto_comprometido',
        'monto_causado', 'monto_pagado', 'observaciones', 'creado_por',
    ];

    protected $casts = [
        'monto_aprobado'      => 'decimal:2',
        'monto_modificado'    => 'decimal:2',
        'monto_comprometido'  => 'decimal:2',
        'monto_causado'       => 'decimal:2',
        'monto_pagado'        => 'decimal:2',
    ];

    public function ejercicioFiscal()
    {
        return $this->belongsTo(EjercicioFiscal::class);
    }

    public function partida()
    {
        return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id');
    }

    public function unidadEjecutora()
    {
        return $this->belongsTo(UnidadEjecutora::class);
    }

    public function fuenteFinanciamiento()
    {
        return $this->belongsTo(FuenteFinanciamiento::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(ProyectoSia::class, 'proyecto_sia_id');
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Monto vigente: deriva del saldo_actual de la partida vinculada.
     * Si la partida tiene saldo registrado, ese es la fuente de verdad.
     * Fallback: monto_aprobado (para créditos sin partida o sin movimientos).
     */
    public function getMontoVigenteAttribute(): float
    {
        $saldoPartida = $this->partida?->saldo_actual;
        if (! is_null($saldoPartida) && (float) $saldoPartida > 0) {
            return (float) $saldoPartida;
        }
        return (float) $this->monto_aprobado + (float) $this->monto_modificado;
    }

    /**
     * Disponible = saldo real de la partida - monto comprometido.
     */
    public function getMontoDisponibleAttribute(): float
    {
        return $this->monto_vigente - (float) $this->monto_comprometido;
    }

    /**
     * Saldo actual directo de la partida vinculada (sin fallback).
     */
    public function getSaldoPartidaAttribute(): float
    {
        return (float) ($this->partida?->saldo_actual ?? 0);
    }

    /** % de ejecución */
    public function getPorcentajeEjecucionAttribute(): float
    {
        if ($this->monto_vigente <= 0) return 0;
        return round(((float) $this->monto_comprometido / $this->monto_vigente) * 100, 2);
    }
}
