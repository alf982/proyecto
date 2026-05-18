<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

use App\Traits\FiltraPorEjercicio;

/**
 * Modelo de Proyecto / Acción Centralizada (SIA)
 * 
 * Es el nivel más alto de la categoría programática en el presupuesto público.
 * Agrupa metas, objetivos, y posteriormente se subdivide en 'Actividades'.
 * Los Créditos Presupuestarios se asignan financieramente dentro de un Proyecto.
 * 
 * Utiliza el trait `FiltraPorEjercicio` para que las consultas por defecto
 * devuelvan solo los proyectos del Ejercicio Fiscal actualmente seleccionado.
 */
class ProyectoSia extends Model implements Auditable
{
    use SoftDeletes, FiltraPorEjercicio;
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

    // ── Relaciones ────────────────────────────────────────────────
    
    /**
     * Ejercicio fiscal (Año presupuestario) al que pertenece el proyecto.
     */
    public function ejercicioFiscal()
    {
        return $this->belongsTo(EjercicioFiscal::class);
    }

    /**
     * Unidad administrativa responsable de la ejecución física y financiera.
     */
    public function unidadEjecutora()
    {
        return $this->belongsTo(UnidadEjecutora::class);
    }

    /**
     * Subdivisión programática del proyecto (Las metas físicas).
     */
    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    /**
     * Todos los créditos presupuestarios asociados a este proyecto.
     */
    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }
}
