<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoHistorialCargo extends Model
{
    protected $table = 'empleado_historial_cargos';

    protected $fillable = [
        'empleado_id', 'cargo_id', 'cargo_texto', 'institucion',
        'unidad_ejecutora_id', 'fecha_inicio', 'fecha_fin',
        'motivo_cambio', 'registrado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function empleado()       { return $this->belongsTo(Empleado::class); }
    public function cargo()          { return $this->belongsTo(Cargo::class); }
    public function unidadEjecutora(){ return $this->belongsTo(UnidadEjecutora::class); }
    public function registradoPor()  { return $this->belongsTo(User::class, 'registrado_por'); }

    /** Nombre del cargo: del sistema o texto libre */
    public function getCargoNombreAttribute(): string
    {
        return $this->cargo?->nombre ?? $this->cargo_texto ?? '—';
    }

    /** Institución: actual o texto libre */
    public function getInstitucionNombreAttribute(): string
    {
        return $this->institucion ?? 'Esta institución';
    }

    /** Duración en texto: "2 años 3 meses" o "En curso" */
    public function getDuracionAttribute(): string
    {
        $fin   = $this->fecha_fin ?? now();
        $anios = (int) $this->fecha_inicio->diffInYears($fin);
        $meses = (int) $this->fecha_inicio->diffInMonths($fin) % 12;

        $texto = '';
        if ($anios > 0) $texto .= $anios . ' ' . ($anios === 1 ? 'año' : 'años');
        if ($meses > 0) $texto .= ($texto ? ' ' : '') . $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');

        return $texto ?: 'Menos de 1 mes';
    }
}
