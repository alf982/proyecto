<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\FiltraPorEjercicio;

/**
 * Modelo de Recepción de Bienes (Acta de Recepción)
 * 
 * Documenta la entrada física de los bienes al almacén, provenientes de
 * una Orden de Compra adjudicada.
 * Su creación detona dos acciones críticas:
 * 1. Incrementa el stock real en el Kardex (InventarioMovimiento).
 * 2. Causa el gasto presupuestario (MovimientoPartida tipo 'pago'),
 *    descontando el saldo disponible de la partida presupuestaria comprometida.
 */
class RecepcionBienes extends Model {
    use FiltraPorEjercicio;
    
    protected $table = 'recepciones_bienes';
    protected $fillable = [
        'numero','ejercicio_fiscal_id','orden_compra_id','fecha_recepcion',
        'recibido_por','entregado_por','numero_guia','numero_factura',
        'total_recibido','estado','observaciones','creado_por'
    ];
    protected $casts = ['fecha_recepcion'=>'date','total_recibido'=>'decimal:2'];

    // ── Relaciones ────────────────────────────────────────────────
    
    /** Orden de compra que origina esta recepción */
    public function orden()     { return $this->belongsTo(OrdenCompra::class, 'orden_compra_id'); }
    
    /** Líneas de artículos recibidos físicamente */
    public function detalles()  { return $this->hasMany(RecepcionDetalle::class, 'recepcion_id'); }
    
    public function creadoPor() { return $this->belongsTo(User::class, 'creado_por'); }

    // ── Helpers ───────────────────────────────────────────────────
    
    public function getEstadoBadge(): string {
        return ['conforme'=>'badge-active','parcial'=>'badge-warn','no_conforme'=>'badge-danger'][$this->estado] ?? 'badge-info';
    }

    public static function generarNumero(int $anio): string {
        $ultimo = static::whereYear('fecha_recepcion', $anio)->max('numero');
        $seq    = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
        return 'REC-'.$anio.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
