<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PartidaPresupuestaria extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'partidas_presupuestarias';

    protected $fillable = [
        'codigo', 'generica', 'especifica', 'subespecifica',
        'descripcion', 'tipo', 'cuenta_bancaria_id',
        'monto_aprobado', 'monto_vigente', 'saldo_actual', 'activo', 'observaciones',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'monto_aprobado'  => 'decimal:2',
        'monto_vigente'   => 'decimal:2',
        'saldo_actual'    => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────
    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoPartida::class, 'partida_presupuestaria_id');
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancaria::class);
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
