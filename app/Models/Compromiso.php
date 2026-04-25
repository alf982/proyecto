<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Compromiso extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'compromisos';

    protected $fillable = [
        'numero', 'ejercicio_fiscal_id', 'unidad_ejecutora_id',
        'credito_presupuestario_id', 'partida_presupuestaria_id', 'proyecto_id',
        'beneficiario_id', 'beneficiario', 'rif_beneficiario', 'concepto',
        'monto_sin_iva', 'alicuota_iva', 'monto',
        'tipo_documento', 'numero_documento', 'fecha_documento', 'descripcion_documento',
        'fecha_compromiso', 'fecha_vencimiento', 'estado',
        'observaciones', 'motivo_anulacion', 'fecha_aprobacion',
        'created_by', 'aprobado_por',
    ];

    protected function casts(): array {
        return [
            'fecha_compromiso'  => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_aprobacion'  => 'date',
            'fecha_documento'   => 'date',
            'monto'             => 'decimal:2',
            'monto_sin_iva'     => 'decimal:2',
            'alicuota_iva'      => 'decimal:2',
        ];
    }

    /** Monto del IVA cobrado por el proveedor */
    public function montoIva(): float
    {
        if (!$this->monto_sin_iva || !$this->alicuota_iva) return 0.0;
        return round((float)$this->monto_sin_iva * ((float)$this->alicuota_iva / 100), 2);
    }

    public function ejercicioFiscal()   { return $this->belongsTo(EjercicioFiscal::class); }
    public function unidadEjecutora()   { return $this->belongsTo(UnidadEjecutora::class); }
    public function credito()           { return $this->belongsTo(CreditoPresupuestario::class, 'credito_presupuestario_id'); }
    public function partida()           { return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id'); }
    public function proyecto()          { return $this->belongsTo(ProyectoSia::class, 'proyecto_id'); }
    public function creadoPor()         { return $this->belongsTo(User::class, 'created_by'); }
    public function aprobadoPor()       { return $this->belongsTo(User::class, 'aprobado_por'); }
    public function causaciones()       { return $this->hasMany(Causacion::class); }
    public function beneficiarioModel() { return $this->belongsTo(Beneficiario::class, 'beneficiario_id'); }

    public function esBorrador(): bool { return $this->estado === 'borrador'; }
    public function esAprobado(): bool { return $this->estado === 'aprobado'; }
    public function esCausado(): bool  { return $this->estado === 'causado'; }
    public function esAnulado(): bool  { return $this->estado === 'anulado'; }

    public function getEstadoBadgeClass(): string {
        return match($this->estado) {
            'aprobado' => 'badge-active',
            'causado'  => 'badge-blue',
            'anulado'  => 'badge-danger',
            default    => 'badge-warn',
        };
    }

    public static function generarNumero(int $anio): string {
        do {
            $ultimo = static::whereYear('created_at', $anio)
                ->orderByDesc('id')
                ->value('numero');
            $seq = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
            $numero = 'COM-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        } while (static::where('numero', $numero)->exists());
        return $numero;
    }
}
