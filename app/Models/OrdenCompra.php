<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenCompra extends Model {
    use SoftDeletes;
    protected $table = 'ordenes_compra';
    protected $fillable = [
        'numero','solicitud_compra_id','ejercicio_fiscal_id','partida_presupuestaria_id',
        'beneficiario_id','proveedor_nombre','proveedor_rif','concepto',
        'fecha_emision','fecha_entrega_estimada',
        'subtotal','iva_porcentaje','iva_monto','total','monto_retencion','monto_neto',
        'estado','modalidad','numero_contrato','motivo_anulacion','condiciones','creado_por',
    ];
    protected $casts = [
        'fecha_emision'=>'date','fecha_entrega_estimada'=>'date',
        'subtotal'=>'decimal:2','iva_monto'=>'decimal:2','total'=>'decimal:2',
        'monto_retencion'=>'decimal:2','monto_neto'=>'decimal:2',
    ];

    public function solicitud()      { return $this->belongsTo(SolicitudCompra::class, 'solicitud_compra_id'); }
    public function ejercicioFiscal(){ return $this->belongsTo(EjercicioFiscal::class); }
    public function beneficiario()   { return $this->belongsTo(Beneficiario::class); }
    public function partida()        { return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id'); }
    public function detalles()       { return $this->hasMany(OrdenDetalle::class, 'orden_compra_id')->orderBy('orden'); }
    public function recepciones()    { return $this->hasMany(RecepcionBienes::class, 'orden_compra_id'); }
    public function creadoPor()      { return $this->belongsTo(User::class, 'creado_por'); }
    public function retenciones()    { return $this->morphMany(RetencionAplicada::class, 'retencionable'); }

    public function totalRetenciones(): float
    {
        return (float) $this->retenciones->sum('monto_retenido');
    }

    public function getEstadoBadge(): string {
        $badges = ['emitida'=>'badge-warn','confirmada'=>'badge-blue','en_transito'=>'badge-info',
                   'completada'=>'badge-active','anulada'=>'badge-danger'];
        return $badges[$this->estado] ?? 'badge-info';
    }
    public function getEstadoLabel(): string {
        $labels = ['emitida'=>'Emitida','confirmada'=>'Confirmada','en_transito'=>'En Tránsito',
                   'completada'=>'Completada','anulada'=>'Anulada'];
        return $labels[$this->estado] ?? 'N/D';
    }
    public function estaActiva(): bool { return !in_array($this->estado, ['completada','anulada']); }

    public static function generarNumero(int $anio): string {
        $ultimo = static::whereYear('fecha_emision', $anio)->max('numero');
        $seq    = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
        return 'OC-'.$anio.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
