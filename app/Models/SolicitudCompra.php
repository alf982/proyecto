<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\FiltraPorEjercicio;

/**
 * Modelo de Solicitud de Compra (Requisición Formal)
 * 
 * Representa la petición formal interna para la adquisición o reabastecimiento
 * de bienes, materiales o servicios. Es el paso previo a la Orden de Compra.
 * Incluye lógica de negocio para generar números correlativos anuales
 * y está aislada por ejercicio fiscal usando FiltraPorEjercicio.
 */
class SolicitudCompra extends Model {
    use SoftDeletes, FiltraPorEjercicio;
    
    protected $table = 'solicitudes_compra';
    protected $fillable = [
        'numero','ejercicio_fiscal_id','unidad_ejecutora_id','motivo','tipo',
        'prioridad','fecha_requerida','estado','motivo_rechazo','observaciones',
        'solicitado_por','aprobado_por','fecha_aprobacion',
    ];
    protected $casts = ['fecha_requerida'=>'date','fecha_aprobacion'=>'datetime'];

    // ── Relaciones ────────────────────────────────────────────────
    
    public function ejercicioFiscal()    { return $this->belongsTo(EjercicioFiscal::class); }
    public function unidadEjecutora()    { return $this->belongsTo(UnidadEjecutora::class); }
    
    /** Líneas de artículos solicitados */
    public function detalles()           { return $this->hasMany(SolicitudDetalle::class, 'solicitud_compra_id')->orderBy('orden'); }
    
    public function solicitadoPor()      { return $this->belongsTo(User::class, 'solicitado_por'); }
    public function aprobadoPor()        { return $this->belongsTo(User::class, 'aprobado_por'); }
    
    /** Órdenes de compra generadas a partir de esta solicitud */
    public function ordenesCompra()      { return $this->hasMany(OrdenCompra::class, 'solicitud_compra_id'); }

    // ── Scopes ───────────────────────────────────────────────────
    public function scopeActivas($q)     { return $q->whereNotIn('estado',['rechazada','procesada']); }

    // ── Helpers Financieros y de UI ──────────────────────────────
    public function getTotalEstimado(): float {
        return $this->detalles->sum(fn($d) => $d->cantidad * $d->precio_estimado);
    }
    
    public function getPrioridadBadge(): string {
        return ['baja'=>'badge-info','media'=>'badge-warn','alta'=>'badge-danger','urgente'=>'badge-danger'][$this->prioridad] ?? 'badge-info';
    }
    
    public function getEstadoBadge(): string {
        return ['borrador'=>'badge-warn','enviada'=>'badge-blue','revisada'=>'badge-info',
                'aprobada'=>'badge-active','rechazada'=>'badge-danger','procesada'=>'badge-active'][$this->estado] ?? 'badge-info';
    }
    
    /**
     * Genera un número de solicitud correlativo por año.
     * Ejemplo: SOL-2026-0001
     */
    public static function generarNumero(int $anio): string {
        $ultimo = static::whereYear('created_at', $anio)->max('numero');
        $seq    = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
        return 'SOL-'.$anio.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
