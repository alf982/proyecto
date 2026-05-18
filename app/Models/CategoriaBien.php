<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Categoría de Bien (Clasificación de Activos)
 * 
 * Agrupa los bienes nacionales (activos fijos) y define los parámetros 
 * contables estándar para su depreciación. 
 * Ejemplo: Equipos de Computación (3 años, 33.33%), Mobiliario (10 años, 10%).
 * Estos parámetros son heredados por los bienes asignados a la categoría.
 */
class CategoriaBien extends Model
{
    protected $table = 'categorias_bien';

    protected $fillable = ['codigo', 'nombre', 'vida_util_anios', 'tasa_depreciacion', 'descripcion', 'activo'];

    protected $casts = ['tasa_depreciacion' => 'decimal:4', 'activo' => 'boolean'];

    // ── Relaciones ──────────────────────────────────────────────────
    
    /** Todos los bienes físicos asignados a esta clasificación */
    public function bienes() { return $this->hasMany(Bien::class); }

    // ── Scopes ──────────────────────────────────────────────────────
    
    public function scopeActivas($q) { return $q->where('activo', true); }
}
