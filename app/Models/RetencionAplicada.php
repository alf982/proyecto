<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetencionAplicada extends Model
{
    protected $table = 'retenciones_aplicadas';

    protected $fillable = [
        'retencionable_type',
        'retencionable_id',
        'retencion_id',
        'monto_base',
        'monto_retenido',
        'porcentaje_aplicado',
    ];

    protected function casts(): array
    {
        return [
            'monto_base'          => 'decimal:2',
            'monto_retenido'      => 'decimal:2',
            'porcentaje_aplicado' => 'decimal:4',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────

    /** Relación polimórfica: Causacion | Pago | OrdenCompra | Nomina */
    public function retencionable()
    {
        return $this->morphTo();
    }

    /** El tipo de retención aplicada */
    public function retencion()
    {
        return $this->belongsTo(Retencion::class);
    }
}
