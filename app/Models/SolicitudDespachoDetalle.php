<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudDespachoDetalle extends Model
{
    protected $table = 'solicitudes_despacho_detalle';

    protected $fillable = [
        'solicitud_despacho_id', 'articulo_id',
        'cantidad_solicitada', 'cantidad_despachada',
        'observacion', 'orden',
    ];

    protected $casts = [
        'cantidad_solicitada'  => 'decimal:2',
        'cantidad_despachada'  => 'decimal:2',
    ];

    public function solicitud() { return $this->belongsTo(SolicitudDespacho::class, 'solicitud_despacho_id'); }
    public function articulo()  { return $this->belongsTo(Articulo::class); }
}
