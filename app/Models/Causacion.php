<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Causacion extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'causaciones';

    protected $fillable = [
        'compromiso_id',
        'numero', 'ejercicio_fiscal_id', 'unidad_ejecutora_id',
        'credito_presupuestario_id', 'partida_presupuestaria_id', 'proyecto_id',
        'beneficiario', 'rif_beneficiario',
        'tipo_documento', 'numero_documento', 'fecha_documento', 'descripcion_documento',
        'monto_causado', 'monto_sin_iva', 'alicuota_iva', 'monto_retencion',
        'concepto', 'estado',
        'fecha_causacion', 'fecha_aprobacion', 'fecha_pago',
        'observaciones', 'motivo_anulacion',
        'created_by', 'aprobado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_causacion'  => 'date',
            'fecha_aprobacion' => 'date',
            'fecha_pago'       => 'date',
            'fecha_documento'  => 'date',
            'monto_causado'    => 'decimal:2',
            'monto_sin_iva'    => 'decimal:2',
            'alicuota_iva'     => 'decimal:2',
            'monto_retencion'  => 'decimal:2',
        ];
    }

    /** Monto del IVA cobrado por el proveedor */
    public function montoIva(): float
    {
        if (!$this->monto_sin_iva || !$this->alicuota_iva) return 0.0;
        return round((float)$this->monto_sin_iva * ((float)$this->alicuota_iva / 100), 2);
    }

    /** Monto neto a transferir al proveedor (total factura - retenciones) */
    public function montoNetoProveedor(): float
    {
        return max(0, (float)$this->monto_causado - (float)$this->monto_retencion);
    }

    public function ejercicioFiscal()   { return $this->belongsTo(EjercicioFiscal::class); }
    public function unidadEjecutora()   { return $this->belongsTo(UnidadEjecutora::class); }
    public function compromiso()        { return $this->belongsTo(Compromiso::class); }
    public function credito()           { return $this->belongsTo(CreditoPresupuestario::class, 'credito_presupuestario_id'); }
    public function partida()           { return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id'); }
    public function proyecto()          { return $this->belongsTo(ProyectoSia::class, 'proyecto_id'); }
    public function creadoPor()         { return $this->belongsTo(User::class, 'created_by'); }
    public function aprobadoPor()       { return $this->belongsTo(User::class, 'aprobado_por'); }
    public function pagos()             { return $this->hasMany(Pago::class); }
    public function retenciones()       { return $this->morphMany(RetencionAplicada::class, 'retencionable'); }

    /** Suma total de retenciones aplicadas a esta causación */
    public function totalRetenciones(): float
    {
        return (float) $this->retenciones->sum('monto_retenido');
    }

    // Helpers
    public function esBorrador(): bool  { return $this->estado === 'borrador'; }
    public function esAprobada(): bool  { return $this->estado === 'aprobada'; }
    public function esPagada(): bool    { return $this->estado === 'pagada'; }
    public function esAnulada(): bool   { return $this->estado === 'anulada'; }

    public function getEstadoBadgeClass(): string
    {
        return match($this->estado) {
            'aprobada' => 'badge-active',
            'pagada'   => 'badge-blue',
            'anulada'  => 'badge-danger',
            default    => 'badge-warn',
        };
    }

    public function getEstadoLabel(): string
    {
        return match($this->estado) {
            'borrador' => 'Borrador',
            'aprobada' => 'Aprobada',
            'pagada'   => 'Pagada',
            'anulada'  => 'Anulada',
        };
    }

    // Genera el número correlativo: CAU-2026-0001
    public static function generarNumero(int $anio): string
    {
        do {
            $ultimo = static::withTrashed()
                ->whereYear('created_at', $anio)
                ->orderByDesc('id')
                ->value('numero');
            $seq = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
            $numero = 'CAU-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('numero', $numero)->exists());
        return $numero;
    }
}
