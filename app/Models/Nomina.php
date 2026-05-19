<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\FiltraPorEjercicio;

/**
 * Modelo de Nómina (Corrida Salarial)
 * 
 * Agrupa el cálculo de sueldos y salarios para un periodo determinado.
 * Al 'pagar', el `total_neto` se debita directamente de la Partida Presupuestaria 
 * asignada (ej: Partida de Gastos de Personal).
 * Implementa el trait `FiltraPorEjercicio` para segregar los datos por Año Fiscal.
 */
class Nomina extends Model
{
    use SoftDeletes, FiltraPorEjercicio;

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

    // ── Relaciones ──────────────────────────────────────────────────
    
    public function ejercicioFiscal() { return $this->belongsTo(EjercicioFiscal::class); }
    public function creadoPor()       { return $this->belongsTo(User::class, 'creado_por'); }
    public function aprobadoPor()     { return $this->belongsTo(User::class, 'aprobado_por'); }
    
    /** Partida de la cual se debitan los fondos al pagar la nómina */
    public function partida()         { return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id'); }
    
    /** Recibos de pago individuales de cada empleado */
    public function detalles()        { return $this->hasMany(NominaDetalle::class); }
    
    /** Retenciones legales aplicadas a la corrida */
    public function retenciones()     { return $this->morphMany(RetencionAplicada::class, 'retencionable'); }

    // ── Helpers ───────────────────────────────────────────────────
    
    public function totalRetenciones(): float
    {
        return (float) $this->retenciones->sum('monto_retenido');
    }

    public static function generarNumero(int $anio): string
    {
        $seq = 1;
        do {
            $numero = 'NOM-' . $anio . '-' . str_pad($seq++, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('numero', $numero)->exists());
        return $numero;
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
