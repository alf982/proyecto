<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenPagoDetalle extends Model
{
    protected $table = 'ordenes_pago_detalle';

    protected $fillable = ['orden_pago_id', 'descripcion', 'monto', 'orden'];

    protected $casts = ['monto' => 'decimal:2'];

    public function ordenPago() { return $this->belongsTo(OrdenPago::class); }
}
