<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = ['codigo', 'nombre', 'responsable_id', 'unidad_ejecutora_id', 'estado', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function responsable()     { return $this->belongsTo(User::class, 'responsable_id'); }
    public function unidadEjecutora() { return $this->belongsTo(UnidadEjecutora::class); }
    public function ingresos()        { return $this->hasMany(Ingreso::class); }
    public function arqueos()         { return $this->hasMany(ArqueoCaja::class); }

    public function scopeActivas($q)  { return $q->where('activo', true); }
    public function arqueoAbierto()   { return $this->arqueos()->where('estado', 'abierto')->first(); }
}
