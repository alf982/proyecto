<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConceptoIngreso extends Model
{
    protected $table = 'conceptos_ingreso';

    protected $fillable = ['codigo', 'nombre', 'tipo', 'activo', 'descripcion'];

    protected $casts = ['activo' => 'boolean'];

    public function ingresos()      { return $this->hasMany(Ingreso::class); }
    public function scopeActivos($q){ return $q->where('activo', true); }
}
