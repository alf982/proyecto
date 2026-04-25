<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        // Datos laborales
        'cedula', 'nombre', 'apellido', 'cargo_id', 'unidad_ejecutora_id',
        'fecha_ingreso', 'tipo', 'banco', 'numero_cuenta', 'telefono', 'email',
        'estado', 'fecha_egreso', 'observaciones', 'creado_por',
        // Datos civiles
        'estado_civil', 'nacionalidad', 'fecha_nacimiento', 'lugar_nacimiento',
        // Grado académico
        'nivel_instruccion', 'titulo', 'institucion_educativa',
        // Dirección
        'estado_residencia', 'municipio', 'parroquia', 'direccion_completa',
        // Curriculum
        'curriculum_path',
        // Salud
        'tipo_sangre', 'tiene_discapacidad', 'tipo_discapacidad', 'condicion_medica',
        // Contacto de emergencia
        'contacto_emergencia_nombre', 'contacto_emergencia_parentesco', 'contacto_emergencia_telefono',
    ];

    protected $casts = [
        'fecha_ingreso'      => 'date',
        'fecha_egreso'       => 'date',
        'fecha_nacimiento'   => 'date',
        'tiene_discapacidad' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────
    public function cargo()           { return $this->belongsTo(Cargo::class); }
    public function unidadEjecutora() { return $this->belongsTo(UnidadEjecutora::class); }
    public function creadoPor()       { return $this->belongsTo(User::class, 'creado_por'); }
    public function nominasDetalle()  { return $this->hasMany(NominaDetalle::class); }
    public function familiares()      { return $this->hasMany(EmpleadoFamiliar::class); }
    public function historialCargos() { return $this->hasMany(EmpleadoHistorialCargo::class)->orderByDesc('fecha_inicio'); }
    public function formaciones()     { return $this->hasMany(EmpleadoFormacion::class)->orderByDesc('fecha_inicio'); }

    // ── Accessors ─────────────────────────────────────────────────
    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    /**
     * Antigüedad en texto: "3 años 2 meses" / "8 meses"
     * Usa fecha_egreso si el empleado ya no está activo.
     */
    public function getAntiguedadAttribute(): string
    {
        $inicio = $this->fecha_ingreso;
        $fin    = $this->fecha_egreso ?? now();

        $anios  = (int) $inicio->diffInYears($fin);
        $meses  = (int) $inicio->diffInMonths($fin) % 12;

        if ($anios === 0) {
            return $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');
        }

        $textoAnios = $anios . ' ' . ($anios === 1 ? 'año' : 'años');
        $textoMeses = $meses > 0 ? ' ' . $meses . ' ' . ($meses === 1 ? 'mes' : 'meses') : '';

        return $textoAnios . $textoMeses;
    }

    /** Número de cargas familiares registradas */
    public function getCargasFamiliaresCountAttribute(): int
    {
        return $this->familiares()->where('es_carga_familiar', true)->count();
    }

    // ── Helpers estáticos ─────────────────────────────────────────
    public static function nivelesInstruccion(): array
    {
        return [
            'sin_instruccion' => 'Sin instrucción',
            'primaria'        => 'Primaria',
            'secundaria'      => 'Secundaria / Bachillerato',
            'tsu'             => 'TSU',
            'universitario'   => 'Universitario',
            'postgrado'       => 'Postgrado / Especialización',
            'doctorado'       => 'Doctorado',
        ];
    }

    public static function estadosCiviles(): array
    {
        return [
            'soltero'     => 'Soltero/a',
            'casado'      => 'Casado/a',
            'divorciado'  => 'Divorciado/a',
            'viudo'       => 'Viudo/a',
            'concubinato' => 'Unión concubinaria',
        ];
    }

    public static function tiposSangre(): array
    {
        return ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActivos($q) { return $q->where('estado', 'activo'); }

    // ── Badge helpers ─────────────────────────────────────────────
    public function estadoBadge(): string
    {
        return match($this->estado) {
            'activo'   => 'badge-active',
            'inactivo' => 'badge-warn',
            'jubilado' => 'badge-blue',
            'retirado' => 'badge-danger',
            default    => 'badge-warn',
        };
    }
}
