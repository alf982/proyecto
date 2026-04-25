<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoFormacion extends Model
{
    protected $table = 'empleado_formaciones';

    protected $fillable = [
        'empleado_id', 'tipo', 'nombre', 'institucion', 'pais',
        'fecha_inicio', 'fecha_fin', 'en_curso', 'duracion_horas',
        'nivel_idioma', 'numero_registro', 'descripcion',
        'documento_path', 'registrado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'en_curso'     => 'boolean',
    ];

    public function empleado()     { return $this->belongsTo(Empleado::class); }
    public function registradoPor(){ return $this->belongsTo(User::class, 'registrado_por'); }

    // ── Etiquetas ─────────────────────────────────────────────────
    public static function tipos(): array
    {
        return [
            'curso'         => 'Curso',
            'certificacion' => 'Certificación',
            'diplomado'     => 'Diplomado',
            'postgrado'     => 'Postgrado / Especialización',
            'maestria'      => 'Maestría',
            'doctorado'     => 'Doctorado',
            'idioma'        => 'Idioma',
            'otro'          => 'Otro',
        ];
    }

    public static function nivelesIdioma(): array
    {
        return [
            'basico'      => 'Básico',
            'intermedio'  => 'Intermedio',
            'avanzado'    => 'Avanzado',
            'nativo'      => 'Nativo',
        ];
    }

    public function getTipoLabelAttribute(): string
    {
        return self::tipos()[$this->tipo] ?? ucfirst($this->tipo);
    }

    /** Período en texto: "2022 – 2024" o "2022 – En curso" */
    public function getPeriodoAttribute(): string
    {
        $inicio = $this->fecha_inicio?->format('Y') ?? '—';
        if ($this->en_curso) return $inicio . ' – En curso';
        $fin = $this->fecha_fin?->format('Y') ?? '—';
        return $inicio === $fin ? $inicio : "$inicio – $fin";
    }

    /** Badge CSS según tipo */
    public function tipoBadgeColor(): string
    {
        return match($this->tipo) {
            'idioma'        => 'badge-blue',
            'certificacion' => 'badge-active',
            'maestria',
            'doctorado',
            'postgrado'     => 'badge-warn',
            default         => 'badge',
        };
    }
}
