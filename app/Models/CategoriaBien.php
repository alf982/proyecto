<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaBien extends Model
{
    protected $table = 'categorias_bien';

    protected $fillable = ['codigo', 'nombre', 'vida_util_anios', 'tasa_depreciacion', 'descripcion', 'activo'];

    protected $casts = ['tasa_depreciacion' => 'decimal:4', 'activo' => 'boolean'];

    public function bienes() { return $this->hasMany(Bien::class); }

    public function scopeActivas($q) { return $q->where('activo', true); }
}
