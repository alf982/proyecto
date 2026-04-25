<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingresos';

    protected $fillable = [
        'numero_recibo', 'caja_id', 'concepto_ingreso_id', 'monto', 'forma_pago',
        'referencia_bancaria', 'pagador_nombre', 'pagador_rif', 'fecha',
        'estado', 'motivo_anulacion', 'creado_por',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function caja()           { return $this->belongsTo(Caja::class); }
    public function concepto()       { return $this->belongsTo(ConceptoIngreso::class, 'concepto_ingreso_id'); }
    public function creadoPor()      { return $this->belongsTo(User::class, 'creado_por'); }

    public static function generarNumero(): string
    {
        $ultimo = static::whereYear('created_at', now()->year)->count();
        return 'REC-' . now()->year . '-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }

    public function estadoBadge(): string
    {
        return match($this->estado) {
            'registrado' => 'badge-active',
            'anulado'    => 'badge-danger',
            default      => 'badge-warn',
        };
    }
}
