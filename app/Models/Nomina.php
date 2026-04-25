<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nomina extends Model
{
    use SoftDeletes;

    protected $table = 'nominas';

    protected $fillable = [
        'numero', 'ejercicio_fiscal_id', 'partida_presupuestaria_id', 'tipo_nomina', 'periodo_inicio', 'periodo_fin',
        'total_asignaciones', 'total_deducciones', 'total_neto', 'estado',
        'observaciones', 'creado_por', 'aprobado_por', 'fecha_aprobacion',
    ];

    protected $casts = [
        'total_asignaciones' => 'decimal:2',
        'total_deducciones'  => 'decimal:2',
        'total_neto'         => 'decimal:2',
        'periodo_inicio'     => 'date',
        'periodo_fin'        => 'date',
        'fecha_aprobacion'   => 'datetime',
    ];

    public function ejercicioFiscal() { return $this->belongsTo(EjercicioFiscal::class); }
    public function creadoPor()       { return $this->belongsTo(User::class, 'creado_por'); }
    public function aprobadoPor()     { return $this->belongsTo(User::class, 'aprobado_por'); }
    public function partida()         { return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id'); }
    public function detalles()        { return $this->hasMany(NominaDetalle::class); }
    public function retenciones()     { return $this->morphMany(RetencionAplicada::class, 'retencionable'); }

    public function totalRetenciones(): float
    {
        return (float) $this->retenciones->sum('monto_retenido');
    }

    public static function generarNumero(int $anio): string
    {
        $ultimo = static::withTrashed()->whereYear('created_at', $anio)->count();
        return 'NOM-' . $anio . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }

    public function estadoBadge(): string
    {
        return match($this->estado) {
            'borrador'  => 'badge-warn',
            'calculada' => 'badge-blue',
            'aprobada'  => 'badge-purple',
            'pagada'    => 'badge-active',
            'anulada'   => 'badge-danger',
            default     => 'badge-warn',
        };
    }
}
