<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MovimientoPartida extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'movimientos_partidas';

    protected $fillable = [
        'numero', 'partida_presupuestaria_id', 'partida_contrapartida_id',
        'movimiento_relacionado_id', 'cuenta_bancaria_id', 'ejercicio_fiscal_id',
        'tipo', 'concepto', 'monto', 'fecha_movimiento', 'referencia',
        'saldo_anterior', 'saldo_posterior',
        'estado', 'motivo_anulacion', 'observaciones', 'creado_por',
    ];

    protected $casts = [
        'monto'           => 'decimal:2',
        'saldo_anterior'  => 'decimal:2',
        'saldo_posterior' => 'decimal:2',
        'fecha_movimiento'=> 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────
    public function partida()
    {
        return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id');
    }

    public function contrapartida()
    {
        return $this->belongsTo(PartidaPresupuestaria::class, 'partida_contrapartida_id');
    }

    public function movimientoRelacionado()
    {
        return $this->belongsTo(MovimientoPartida::class, 'movimiento_relacionado_id');
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancaria::class);
    }

    public function ejercicioFiscal()
    {
        return $this->belongsTo(EjercicioFiscal::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Tipos que SUMAN al saldo de la partida */
    public const TIPOS_INGRESO = [
        'asignacion', 'credito_adicional', 'modificacion_entrada', 'reintegro', 'nota_credito',
    ];

    /** Tipos que RESTAN del saldo de la partida */
    public const TIPOS_EGRESO = [
        'modificacion_salida', 'ejecucion', 'nota_debito',
        'compromiso', 'causacion', 'pago',
    ];

    public function esIngreso(): bool
    {
        return in_array($this->tipo, self::TIPOS_INGRESO);
    }

    public function esEgreso(): bool
    {
        return in_array($this->tipo, self::TIPOS_EGRESO);
    }

    /** Etiquetas legibles para cada tipo */
    public static function etiquetaTipo(string $tipo): string
    {
        return match($tipo) {
            'asignacion'           => 'Asignación Inicial',
            'credito_adicional'    => 'Crédito Adicional',
            'modificacion_entrada' => 'Modificación (Entrada)',
            'modificacion_salida'  => 'Modificación (Salida)',
            'ejecucion'            => 'Ejecución',
            'reintegro'            => 'Reintegro',
            'nota_credito'         => 'Nota de Crédito',
            'nota_debito'          => 'Nota de Débito',
            'compromiso'           => 'Compromiso',
            'causacion'            => 'Causación',
            'pago'                 => 'Pago',
            default                => ucfirst($tipo),
        };
    }

    /** Clase CSS de badge según tipo */
    public function getBadgeTipoClass(): string
    {
        return $this->esIngreso() ? 'badge-active' : 'badge-danger';
    }

    /** Clase CSS de badge según estado */
    public function getBadgeEstadoClass(): string
    {
        return match($this->estado) {
            'confirmado' => 'badge-active',
            'anulado'    => 'badge-danger',
            default      => 'badge-warn',
        };
    }

    /** Generar número correlativo: MP-2026-0001 */
    public static function generarNumero(int $anio): string
    {
        $ultimo = static::whereYear('created_at', $anio)->max('id') ?? 0;
        return 'MP-' . $anio . '-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
