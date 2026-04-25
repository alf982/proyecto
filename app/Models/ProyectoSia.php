<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ProyectoSia extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'proyectos_sia';

    protected $fillable = [
        'ejercicio_fiscal_id', 'unidad_ejecutora_id', 'codigo', 'nombre',
        'descripcion', 'objetivo', 'fecha_inicio', 'fecha_fin', 'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function ejercicioFiscal()
    {
        return $this->belongsTo(EjercicioFiscal::class);
    }

    public function unidadEjecutora()
    {
        return $this->belongsTo(UnidadEjecutora::class);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }
}
