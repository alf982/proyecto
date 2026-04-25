<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = ['codigo', 'nombre', 'nivel', 'salario_base', 'activo'];

    protected $casts = ['salario_base' => 'decimal:2', 'activo' => 'boolean'];

    public function empleados() { return $this->hasMany(Empleado::class); }

    public function scopeActivos($q) { return $q->where('activo', true); }
}
