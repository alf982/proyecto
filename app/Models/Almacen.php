<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Almacén (Compras e Inventario)
 * 
 * Representa un espacio físico o virtual donde se resguardan
 * los artículos, suministros y materiales adquiridos por la institución.
 * Sirve como contenedor primario para el control de stock (Kardex).
 */
class Almacen extends Model {
    protected $table = 'almacenes';
    protected $fillable = ['codigo','nombre','ubicacion','responsable','activo','creado_por'];
    protected $casts = ['activo' => 'boolean'];

    // ── Relaciones ────────────────────────────────────────────────

    /** Catálogo de artículos asociados físicamente a este almacén */
    public function articulos()  { return $this->hasMany(Articulo::class); }
    public function creadoPor()  { return $this->belongsTo(User::class, 'creado_por'); }
    
    // ── Scopes ───────────────────────────────────────────────────
    public function scopeActivos($q) { return $q->where('activo', true); }
}
