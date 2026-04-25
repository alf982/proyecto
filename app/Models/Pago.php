<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Pago extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'pagos';

    protected $fillable = [
        'numero', 'causacion_id', 'orden_pago_id', 'ejercicio_fiscal_id', 'unidad_ejecutora_id',
        'beneficiario', 'rif_beneficiario', 'tipo_pago',
        'numero_referencia', 'banco', 'cuenta_bancaria',
        'monto_pagado', 'fecha_pago', 'concepto', 'estado',
        'observaciones', 'motivo_anulacion', 'created_by', 'generado_automatico',
    ];

    protected function casts(): array {
        return [
            'fecha_pago'  => 'date',
            'monto_pagado' => 'decimal:2',
        ];
    }

    public function causacion()       { return $this->belongsTo(Causacion::class); }
    public function ordenPago()       { return $this->belongsTo(OrdenPago::class); }
    public function ejercicioFiscal() { return $this->belongsTo(EjercicioFiscal::class); }
    public function unidadEjecutora() { return $this->belongsTo(UnidadEjecutora::class); }
    public function creadoPor()       { return $this->belongsTo(User::class, 'created_by'); }
    public function retenciones()     { return $this->morphMany(RetencionAplicada::class, 'retencionable'); }

    public function totalRetenciones(): float
    {
        return (float) $this->retenciones->sum('monto_retenido');
    }

    public function esPendiente(): bool  { return $this->estado === 'pendiente'; }
    public function esProcesado(): bool  { return $this->estado === 'procesado'; }
    public function esAnulado(): bool    { return $this->estado === 'anulado'; }

    public function getEstadoBadgeClass(): string {
        return match($this->estado) {
            'procesado' => 'badge-active',
            'anulado'   => 'badge-danger',
            default     => 'badge-warn',
        };
    }

    public function getEstadoLabel(): string {
        return match($this->estado) {
            'pendiente'  => 'Pendiente',
            'procesado'  => 'Procesado',
            'anulado'    => 'Anulado',
            default      => ucfirst($this->estado),
        };
    }

    public function getTipoPagoLabel(): string {
        return match($this->tipo_pago) {
            'cheque'        => 'Cheque',
            'transferencia' => 'Transferencia Bancaria',
            'efectivo'      => 'Efectivo',
            default         => 'Otro',
        };
    }

    public static function generarNumero(int $anio): string {
        do {
            $ultimo = static::whereYear('created_at', $anio)
                ->orderByDesc('id')
                ->value('numero');
            $seq = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
            $numero = 'PAG-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        } while (static::where('numero', $numero)->exists());
        return $numero;
    }
}
