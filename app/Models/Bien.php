<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Bien Nacional (Activo Fijo)
 * 
 * Representa un activo físico propiedad de la institución (ej: computadoras, vehículos, escritorios).
 * Registra su valor de adquisición, depreciación acumulada y ubicación actual.
 * El ciclo de vida del bien es rastreado a través de la relación `movimientos`
 * (incorporación, traslados entre departamentos y desincorporación/baja).
 */
class Bien extends Model
{
    use SoftDeletes;

    protected $table = 'bienes';

    protected $fillable = [
        'numero_inventario', 'categoria_bien_id', 'unidad_ejecutora_id', 'descripcion',
        'marca', 'modelo', 'serial', 'anio_adquisicion', 'valor_adquisicion',
        'valor_actual', 'ubicacion', 'responsable', 'estado',
        'fecha_incorporacion', 'fecha_baja', 'motivo_baja', 'observaciones', 'creado_por',
    ];

    protected $casts = [
        'valor_adquisicion'  => 'decimal:2',
        'valor_actual'       => 'decimal:2',
        'fecha_incorporacion'=> 'date',
        'fecha_baja'         => 'date',
    ];

    // ── Relaciones ──────────────────────────────────────────────────
    
    /** Categoría que dicta la tasa de depreciación */
    public function categoria()       { return $this->belongsTo(CategoriaBien::class, 'categoria_bien_id'); }
    
    /** Departamento que actualmente tiene la custodia del bien */
    public function unidadEjecutora() { return $this->belongsTo(UnidadEjecutora::class); }
    
    public function creadoPor()       { return $this->belongsTo(User::class, 'creado_por'); }
    
    /** Historial de traslados, asignaciones y bajas (Auditoría) */
    public function movimientos()     { return $this->hasMany(MovimientoBien::class); }

    // ── Helpers ───────────────────────────────────────────────────
    
    public function estadoBadge(): string
    {
        return match($this->estado) {
            'activo'        => 'badge-active',
            'en_reparacion' => 'badge-warn',
            'dado_de_baja'  => 'badge-danger',
            'extraviado'    => 'badge-danger',
            default         => 'badge-warn',
        };
    }

    public static function generarNumero(): string
    {
        $ultimo = static::withTrashed()->count();
        return 'BN-' . date('Y') . '-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }
}
