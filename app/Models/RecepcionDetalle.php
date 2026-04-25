<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RecepcionDetalle extends Model {
    protected $table = 'recepciones_detalle';
    protected $fillable = ['recepcion_id','orden_detalle_id','articulo_id','cantidad_recibida','precio_unitario','condicion','observacion'];
    protected $casts = ['cantidad_recibida'=>'decimal:2','precio_unitario'=>'decimal:2'];
    public function recepcion()     { return $this->belongsTo(RecepcionBienes::class, 'recepcion_id'); }
    public function ordenDetalle()  { return $this->belongsTo(OrdenDetalle::class, 'orden_detalle_id'); }
    public function articulo()      { return $this->belongsTo(Articulo::class); }
    public function getSubtotal(): float { return $this->cantidad_recibida * $this->precio_unitario; }
}
