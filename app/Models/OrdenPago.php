<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenPago extends Model
{
    use SoftDeletes;

    protected $table = 'ordenes_pago';

    protected $fillable = [
        'numero', 'ejercicio_fiscal_id', 'unidad_ejecutora_id', 'beneficiario_id',
        'causacion_id', 'concepto', 'monto_total', 'tipo_pago',
        'numero_referencia', 'banco', 'cuenta_bancaria_num', 'fecha_pago',
        'estado', 'observaciones', 'motivo_anulacion',
        'creado_por', 'revisado_por', 'aprobado_por',
        'fecha_revision', 'fecha_aprobacion', 'fecha_envio',
    ];

    protected $casts = [
        'monto_total'      => 'decimal:2',
        'fecha_revision'   => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_envio'      => 'datetime',
        'fecha_pago'       => 'date',
    ];

    public function ejercicioFiscal()   { return $this->belongsTo(EjercicioFiscal::class); }
    public function unidadEjecutora()   { return $this->belongsTo(UnidadEjecutora::class); }
    public function beneficiario()      { return $this->belongsTo(Beneficiario::class); }
    public function causacion()         { return $this->belongsTo(Causacion::class); }
    public function creadoPor()         { return $this->belongsTo(User::class, 'creado_por'); }
    public function revisadoPor()       { return $this->belongsTo(User::class, 'revisado_por'); }
    public function aprobadoPor()       { return $this->belongsTo(User::class, 'aprobado_por'); }
    public function detalles()          { return $this->hasMany(OrdenPagoDetalle::class); }
    public function pago()              { return $this->hasOne(Pago::class); }

    public static function generarNumero(int $anio): string
    {
        $ultimo = static::withTrashed()
            ->whereYear('created_at', $anio)
            ->count();
        return 'OP-' . $anio . '-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }

    public function estadoBadge(): string
    {
        return match($this->estado) {
            'borrador'  => 'badge-warn',
            'revisada'  => 'badge-blue',
            'aprobada'  => 'badge-purple',
            'enviada'   => 'badge-active',
            'pagada'    => 'badge-active',
            'anulada'   => 'badge-danger',
            default     => 'badge-warn',
        };
    }
}
