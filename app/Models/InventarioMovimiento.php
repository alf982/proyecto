<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InventarioMovimiento extends Model {
    protected $table = 'inventario_movimientos';
    protected $fillable = ['articulo_id','almacen_id','tipo','origen_tipo','origen_id','cantidad','precio_unitario','stock_anterior','stock_nuevo','concepto','fecha','creado_por'];
    protected $casts = ['fecha'=>'date','cantidad'=>'decimal:2','precio_unitario'=>'decimal:2','stock_anterior'=>'decimal:2','stock_nuevo'=>'decimal:2'];
    public function articulo()  { return $this->belongsTo(Articulo::class); }
    public function almacen()   { return $this->belongsTo(Almacen::class); }
    public function creadoPor() { return $this->belongsTo(User::class, 'creado_por'); }
    public function getTipoBadge(): string {
        return ['entrada'=>'badge-active','salida'=>'badge-danger','ajuste'=>'badge-warn','traslado'=>'badge-blue'][$this->tipo] ?? 'badge-info';
    }
}
