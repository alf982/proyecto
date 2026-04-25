<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class UnidadEjecutora extends Model implements Auditable
{
    protected $table = 'unidades_ejecutoras';

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'parent_id', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(UnidadEjecutora::class, 'parent_id');
    }

    public function hijos()
    {
        return $this->hasMany(UnidadEjecutora::class, 'parent_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function proyectos()
    {
        return $this->hasMany(ProyectoSia::class);
    }

    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function getNombreCompletoAttribute(): string
    {
        return "[{$this->codigo}] {$this->nombre}";
    }
}
