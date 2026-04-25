<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrdenDetalle extends Model {
    protected $table = 'ordenes_compra_detalle';
    protected $fillable = ['orden_compra_id','articulo_id','descripcion','unidad_medida','cantidad','precio_unitario','subtotal','cantidad_recibida','orden'];
    protected $casts = ['cantidad'=>'decimal:2','precio_unitario'=>'decimal:2','subtotal'=>'decimal:2','cantidad_recibida'=>'decimal:2'];
    public function orden()    { return $this->belongsTo(OrdenCompra::class, 'orden_compra_id'); }
    public function articulo() { return $this->belongsTo(Articulo::class); }
    public function getPendienteRecibir(): float { return max(0, $this->cantidad - $this->cantidad_recibida); }
}
