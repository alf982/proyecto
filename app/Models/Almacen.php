<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model {
    protected $table = 'almacenes';
    protected $fillable = ['codigo','nombre','ubicacion','responsable','activo','creado_por'];
    protected $casts = ['activo' => 'boolean'];

    public function articulos()  { return $this->hasMany(Articulo::class); }
    public function creadoPor()  { return $this->belongsTo(User::class, 'creado_por'); }
    public function scopeActivos($q) { return $q->where('activo', true); }
}
