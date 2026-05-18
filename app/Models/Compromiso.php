<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

use App\Traits\FiltraPorEjercicio;

/**
 * Modelo de Compromiso (Afectación Preventiva)
 * 
 * El compromiso representa la primera etapa de ejecución presupuestaria.
 * Cuantitativamente es el acto formal donde la institución reserva (aparta) 
 * una porción del saldo de la partida para un gasto específico (orden de compra, contrato, etc).
 * Esta reserva evita que se disponga del saldo para otro propósito, aunque el pago real 
 * se realice después (en la fase de pago).
 */
class Compromiso extends Model implements Auditable
{
    use SoftDeletes, FiltraPorEjercicio;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'compromisos';

    /**
     * Relaciones siempre necesarias. Cargadas automáticamente
     * para evitar lazy loading (N+1) en show(), aprobar() y anular(),
     * ya que la partida es vital para validaciones de saldo.
     */
    protected $with = ['partida'];

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

    /** 
     * Monto del IVA cobrado por el proveedor.
     * Utilizado para separar el gasto de los impuestos cuando sea necesario.
     */
    public function montoIva(): float
    {
        if (!$this->monto_sin_iva || !$this->alicuota_iva) return 0.0;
        return round((float)$this->monto_sin_iva * ((float)$this->alicuota_iva / 100), 2);
    }

    // ── Relaciones ────────────────────────────────────────────────

    public function ejercicioFiscal()   { return $this->belongsTo(EjercicioFiscal::class); }
    public function unidadEjecutora()   { return $this->belongsTo(UnidadEjecutora::class); }
    public function credito()           { return $this->belongsTo(CreditoPresupuestario::class, 'credito_presupuestario_id'); }
    
    /** Partida presupuestaria afectada por el compromiso */
    public function partida()           { return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id'); }
    public function proyecto()          { return $this->belongsTo(ProyectoSia::class, 'proyecto_id'); }
    public function creadoPor()         { return $this->belongsTo(User::class, 'created_by'); }
    public function aprobadoPor()       { return $this->belongsTo(User::class, 'aprobado_por'); }
    
    /** Un compromiso genera posteriormente una o múltiples causaciones */
    public function causaciones()       { return $this->hasMany(Causacion::class); }
    
    /** Opcional: Beneficiario extraído de nuestro catálogo de proveedores */
    public function beneficiarioModel() { return $this->belongsTo(Beneficiario::class, 'beneficiario_id'); }

    // ── Helpers de Estado ────────────────────────────────────────

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

    /** 
     * Genera un número de compromiso correlativo. Ej: COM-2026-0001
     * Optimizado para usar la db local evitando loops innecesarios.
     */
    public static function generarNumero(int $anio): string {
        $prefijo = 'COM-' . $anio . '-';
        // MAX en una sola query — evita el loop do/while con 2 queries cada iteración
        $ultimo = static::whereYear('created_at', $anio)
            ->max('id');
        $seq = $ultimo
            ? ((int) substr(static::where('id', $ultimo)->value('numero') ?? '0', -4)) + 1
            : 1;
        $numero = $prefijo . str_pad($seq, 4, '0', STR_PAD_LEFT);
        // Colisión extremadamente rara pero la verificamos igual
        while (static::where('numero', $numero)->exists()) {
            $numero = $prefijo . str_pad(++$seq, 4, '0', STR_PAD_LEFT);
        }
        return $numero;
    }
}
