<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SolicitudDetalle extends Model {
    protected $table = 'solicitudes_compra_detalle';
    protected $fillable = ['solicitud_compra_id','articulo_id','descripcion','unidad_medida','cantidad','precio_estimado','especificaciones','orden'];
    protected $casts = ['cantidad'=>'decimal:2','precio_estimado'=>'decimal:2'];
    public function solicitud() { return $this->belongsTo(SolicitudCompra::class, 'solicitud_compra_id'); }
    public function articulo()  { return $this->belongsTo(Articulo::class); }
    public function getSubtotal(): float { return $this->cantidad * $this->precio_estimado; }
}
