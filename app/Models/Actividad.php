<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Actividad extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'proyecto_sia_id', 'codigo', 'nombre', 'descripcion',
        'meta', 'unidad_medida', 'cantidad_meta', 'estado',
    ];

    protected $casts = ['cantidad_meta' => 'decimal:2'];

    public function proyecto()
    {
        return $this->belongsTo(ProyectoSia::class, 'proyecto_sia_id');
    }

    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }
}
