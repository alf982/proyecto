<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

use App\Traits\FiltraPorEjercicio;

/**
 * Modelo de Movimiento de Partida (Libro Mayor Presupuestario)
 * 
 * Actúa como un registro inmutable de partida doble para el presupuesto.
 * Todas las inyecciones de liquidez (asignaciones, créditos adicionales)
 * y los recortes o traspasos quedan registrados aquí. 
 * Utiliza el trait `FiltraPorEjercicio` para autolimitarse al año fiscal activo.
 */
class MovimientoPartida extends Model implements Auditable
{
    use SoftDeletes, FiltraPorEjercicio;
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
    
    /**
     * Partida principal afectada (la que recibe el ingreso o el egreso directo).
     */
    public function partida()
    {
        return $this->belongsTo(PartidaPresupuestaria::class, 'partida_presupuestaria_id');
    }

    /**
     * En caso de traspasos, es la partida que cede el dinero (si este mov. es de entrada).
     */
    public function contrapartida()
    {
        return $this->belongsTo(PartidaPresupuestaria::class, 'partida_contrapartida_id');
    }

    /**
     * Movimiento espejo. En los traspasos, vincula la "entrada" con la "salida" 
     * para que si uno se anula, se anule automáticamente el otro.
     */
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

    /** 
     * Tipos de movimiento que INCREMENTAN el saldo disponible de la partida.
     */
    public const TIPOS_INGRESO = [
        'asignacion', 'credito_adicional', 'modificacion_entrada', 'reintegro', 'nota_credito',
    ];

    /** 
     * Tipos de movimiento que DISMINUYEN el saldo disponible de la partida.
     */
    public const TIPOS_EGRESO = [
        'modificacion_salida', 'ejecucion', 'nota_debito',
        'compromiso', 'causacion', 'pago',
    ];

    /** Verifica lógicamente si el movimiento suma saldo */
    public function esIngreso(): bool
    {
        return in_array($this->tipo, self::TIPOS_INGRESO);
    }

    /** Verifica lógicamente si el movimiento resta saldo */
    public function esEgreso(): bool
    {
        return in_array($this->tipo, self::TIPOS_EGRESO);
    }

    /** Etiquetas legibles para renderizar en la interfaz de usuario */
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

    /** Helper para estilizar dinámicamente si el badge es verde (ingreso) o rojo (egreso) */
    public function getBadgeTipoClass(): string
    {
        return $this->esIngreso() ? 'badge-active' : 'badge-danger';
    }

    /** Helper para estilizar si el movimiento es válido o fue anulado */
    public function getBadgeEstadoClass(): string
    {
        return match($this->estado) {
            'confirmado' => 'badge-active',
            'anulado'    => 'badge-danger',
            default      => 'badge-warn',
        };
    }

    /** Genera automáticamente un número correlativo secuencial anual (Ej: MP-2026-0001) */
    public static function generarNumero(int $anio): string
    {
        do {
            $ultimo = static::withTrashed()
                ->where(function($query) use ($anio) {
                    $query->whereYear('created_at', $anio)
                          ->orWhere('numero', 'like', "MP-{$anio}-%");
                })
                ->orderByDesc('id')
                ->value('numero');

            $seq = 1;
            if ($ultimo) {
                $partes = explode('-', $ultimo);
                $secuenciaTexto = end($partes);
                if (is_numeric($secuenciaTexto)) {
                    $seq = ((int) $secuenciaTexto) + 1;
                }
            }

            $numero = 'MP-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('numero', $numero)->exists());

        return $numero;
    }
}
