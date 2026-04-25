<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class EjercicioFiscal extends Model implements Auditable
{
    protected $table = 'ejercicios_fiscales';

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'anio', 'fecha_inicio', 'fecha_fin', 'estado',
        'observaciones', 'creado_por', 'cerrado_por', 'fecha_cierre',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'fecha_cierre'  => 'datetime',
        'anio'          => 'integer',
    ];

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function cerradoPor()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function proyectos()
    {
        return $this->hasMany(ProyectoSia::class);
    }

    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function puedeModificarse(): bool
    {
        return in_array($this->estado, ['borrador', 'activo']);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'borrador' => 'warn',
            'activo'   => 'active',
            'cerrado'  => 'danger',
            default    => 'warn',
        };
    }
}
