<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Artículo (Catálogo de Bienes y Servicios)
 * 
 * Representa la unidad mínima de inventario o catálogo de compras.
 * Puede ser un Bien (tangible), un Servicio (intangible), Material o Equipo.
 * Mantiene el control de `stock_actual` y `stock_minimo` para alertas de reposición.
 */
class Articulo extends Model {
    use SoftDeletes;

    protected $table = 'articulos';
    protected $fillable = [
        'codigo','nombre','descripcion','tipo','unidad_medida','precio_referencia',
        'stock_actual','stock_minimo','categoria','almacen_id','activo','creado_por',
    ];
    protected $casts = ['activo'=>'boolean','precio_referencia'=>'decimal:2','stock_actual'=>'decimal:2','stock_minimo'=>'decimal:2'];

    // ── Relaciones ────────────────────────────────────────────────

    /** Almacén físico donde suele ubicarse este artículo */
    public function almacen()           { return $this->belongsTo(Almacen::class); }
    
    /** Historial de entradas, salidas y ajustes de inventario (Kardex) */
    public function movimientos()       { return $this->hasMany(InventarioMovimiento::class); }
    public function creadoPor()         { return $this->belongsTo(User::class, 'creado_por'); }

    // ── Scopes ───────────────────────────────────────────────────
    public function scopeActivos($q)    { return $q->where('activo', true); }
    public function scopeBajoStock($q)  { return $q->whereColumn('stock_actual','<=','stock_minimo'); }

    // ── Helpers ──────────────────────────────────────────────────
    public function getTipoLabel(): string {
        $labels = ['bien'=>'Bien','servicio'=>'Servicio','material'=>'Material','equipo'=>'Equipo'];
        return $labels[$this->tipo] ?? 'N/D';
    }
    
    public function getBajoStock(): bool {
        return $this->stock_minimo > 0 && $this->stock_actual <= $this->stock_minimo;
    }
}
