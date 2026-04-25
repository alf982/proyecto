<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CuentaBancaria extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'codigo', 'nombre', 'banco', 'numero_cuenta', 'tipo', 'moneda',
        'saldo_inicial', 'saldo_actual', 'fecha_apertura', 'estado',
        'firmante_1', 'firmante_2', 'ejercicio_fiscal_id', 'observaciones', 'creado_por',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'saldo_actual'  => 'decimal:2',
        'fecha_apertura'=> 'date',
    ];

    // Relaciones
    public function ejercicioFiscal()   { return $this->belongsTo(EjercicioFiscal::class); }
    public function creadoPor()         { return $this->belongsTo(User::class, 'creado_por'); }
    public function movimientos()              { return $this->hasMany(MovimientoBancario::class); }
    public function conciliaciones()           { return $this->hasMany(ConciliacionBancaria::class); }
    /** Partidas presupuestarias cuyos fondos ingresan a esta cuenta */
    public function partidasPresupuestarias()  { return $this->hasMany(PartidaPresupuestaria::class); }

    // Scopes
    public function scopeActivas($q) { return $q->where('estado', 'activa'); }

    // Helpers
    public function estaActiva(): bool  { return $this->estado === 'activa'; }

    /**
     * Recalcula el saldo_actual de esta cuenta sumando los saldo_actual
     * de todas las partidas presupuestarias vinculadas (activas y no eliminadas).
     * Se llama automáticamente al registrar o anular un MovimientoPartida.
     */
    public function recalcularSaldo(): void
    {
        $nuevo = $this->partidasPresupuestarias()
                      ->sum('saldo_actual');
        $this->updateQuietly(['saldo_actual' => $nuevo]);
    }

    /**
     * Devuelve la suma calculada sin persistir (útil para vistas).
     */
    public function getSaldoPartidasAttribute(): float
    {
        return (float) $this->partidasPresupuestarias()->sum('saldo_actual');
    }

    /**
     * Cantidad de partidas presupuestarias vinculadas.
     */
    public function getTotalPartidasAttribute(): int
    {
        return $this->partidasPresupuestarias()->count();
    }

    public function getEstadoBadgeClass(): string {
        return match($this->estado) {
            'activa'    => 'badge-active',
            'bloqueada' => 'badge-warn',
            default     => 'badge-danger',
        };
    }

    public static function generarCodigo(): string {
        $ultimo = static::max('id') ?? 0;
        return 'CTA-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
