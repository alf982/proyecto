<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

use App\Traits\FiltraPorEjercicio;

class MovimientoBancario extends Model implements Auditable
{
    use SoftDeletes, FiltraPorEjercicio;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'movimientos_bancarios';

    protected $fillable = [
        'numero', 'cuenta_bancaria_id', 'tipo', 'concepto', 'monto',
        'fecha_movimiento', 'fecha_valor', 'referencia',
        'banco_origen', 'cuenta_origen',
        'beneficiario_nombre', 'beneficiario_rif',
        'origen', 'origen_modelo_type', 'origen_modelo_id',
        'estado', 'observaciones', 'motivo_anulacion',
        'ejercicio_fiscal_id', 'creado_por',
    ];

    protected $casts = [
        'fecha_movimiento' => 'date',
        'fecha_valor'      => 'date',
        'monto'            => 'decimal:2',
    ];

    // Relaciones
    public function cuenta()         { return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id'); }
    public function ejercicioFiscal(){ return $this->belongsTo(EjercicioFiscal::class); }
    public function creadoPor()      { return $this->belongsTo(User::class, 'creado_por'); }
    public function origenModelo()   { return $this->morphTo('origen_modelo'); }

    // Scopes
    public function scopePendientes($q) { return $q->where('estado', 'pendiente'); }
    public function scopeConciliados($q){ return $q->where('estado', 'conciliado'); }

    // Helpers
    public function esDebito(): bool      { return in_array($this->tipo, ['debito', 'transferencia_salida', 'nota_debito']); }
    public function esCredito(): bool     { return in_array($this->tipo, ['credito', 'transferencia_entrada', 'nota_credito']); }
    public function esPendiente(): bool   { return $this->estado === 'pendiente'; }
    public function esConciliado(): bool  { return $this->estado === 'conciliado'; }

    public function getSignoMonto(): float {
        return $this->esDebito() ? -(float)$this->monto : (float)$this->monto;
    }

    public function getTipoLabel(): string {
        return match($this->tipo) {
            'debito'               => 'Débito',
            'credito'              => 'Crédito',
            'transferencia_entrada'=> 'Transferencia Entrada',
            'transferencia_salida' => 'Transferencia Salida',
            'nota_debito'          => 'Nota de Débito',
            'nota_credito'         => 'Nota de Crédito',
            default                => $this->tipo,
        };
    }

    public function getEstadoBadgeClass(): string {
        return match($this->estado) {
            'conciliado' => 'badge-active',
            'anulado'    => 'badge-danger',
            default      => 'badge-warn',
        };
    }

    public static function generarNumero(int $anio): string {
        $ultimo = static::whereYear('created_at', $anio)->max('numero');
        $seq    = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
        return 'MOV-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
