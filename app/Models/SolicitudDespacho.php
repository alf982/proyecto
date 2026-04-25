<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudDespacho extends Model
{
    use SoftDeletes;

    protected $table = 'solicitudes_despacho';

    protected $fillable = [
        'numero', 'ejercicio_fiscal_id', 'unidad_ejecutora_id', 'motivo', 'prioridad',
        'fecha_requerida', 'estado', 'observaciones', 'motivo_rechazo',
        'solicitado_por', 'aprobado_por', 'fecha_aprobacion', 'fecha_despacho',
        // Campos de entrega real
        'recibido_por', 'fecha_entrega', 'observaciones_entrega',
    ];

    protected $casts = [
        'fecha_requerida'      => 'date',
        'fecha_aprobacion'     => 'datetime',
        'fecha_despacho'       => 'datetime',
        'fecha_entrega'        => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────────────────────
    public function unidadEjecutora() { return $this->belongsTo(UnidadEjecutora::class); }
    public function solicitadoPor()   { return $this->belongsTo(User::class, 'solicitado_por'); }
    public function aprobadoPor()     { return $this->belongsTo(User::class, 'aprobado_por'); }
    public function detalles()        { return $this->hasMany(SolicitudDespachoDetalle::class)->orderBy('orden'); }

    // ── Scopes ───────────────────────────────────────────────────────
    public function scopePendientes($q) { return $q->whereIn('estado', ['enviada', 'borrador']); }
    public function scopeAprobadas($q)  { return $q->where('estado', 'aprobada'); }

    // ── Helpers de estado ────────────────────────────────────────────
    public function esBorrador(): bool  { return $this->estado === 'borrador'; }
    public function esEnviada(): bool   { return $this->estado === 'enviada'; }
    public function esAprobada(): bool  { return $this->estado === 'aprobada'; }
    public function esEntregada(): bool { return $this->estado === 'entregada'; }
    public function esRechazada(): bool { return $this->estado === 'rechazada'; }

    /** ¿Puede rechazarse? Cualquier estado anterior a entregada. */
    public function puedeRechazarse(): bool { return !in_array($this->estado, ['entregada', 'rechazada']); }

    public function getEstadoBadge(): string {
        return match($this->estado) {
            'borrador'  => 'badge-warn',
            'enviada'   => 'badge-blue',
            'aprobada'  => 'badge-purple',
            'entregada' => 'badge-active',
            'rechazada' => 'badge-danger',
            default     => 'badge-info',
        };
    }

    public function getEstadoLabel(): string {
        return match($this->estado) {
            'borrador'  => 'Borrador',
            'enviada'   => 'Enviada',
            'aprobada'  => 'Aprobada — Pendiente Entrega',
            'entregada' => 'Entregada',
            'rechazada' => 'Rechazada',
            default     => 'N/D',
        };
    }

    public function getPrioridadBadge(): string {
        return match($this->prioridad) {
            'baja'    => 'badge-info',
            'media'   => 'badge-warn',
            'alta'    => 'badge-danger',
            'urgente' => 'badge-danger',
            default   => 'badge-info',
        };
    }

    public static function generarNumero(int $anio): string {
        $ultimo = static::whereYear('created_at', $anio)->max('numero');
        $seq    = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
        return 'SD-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
