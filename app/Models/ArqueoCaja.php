<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArqueoCaja extends Model
{
    protected $table = 'arqueos_caja';

    protected $fillable = [
        'caja_id', 'fecha', 'monto_apertura', 'total_ingresos', 'monto_cierre',
        'diferencia', 'estado', 'aprobado_por', 'fecha_aprobacion', 'observaciones', 'creado_por',
    ];

    protected $casts = [
        'monto_apertura'  => 'decimal:2',
        'total_ingresos'  => 'decimal:2',
        'monto_cierre'    => 'decimal:2',
        'diferencia'      => 'decimal:2',
        'fecha'           => 'date',
        'fecha_aprobacion'=> 'datetime',
    ];

    public function caja()       { return $this->belongsTo(Caja::class); }
    public function aprobadoPor(){ return $this->belongsTo(User::class, 'aprobado_por'); }
    public function creadoPor()  { return $this->belongsTo(User::class, 'creado_por'); }

    public function estadoBadge(): string
    {
        return match($this->estado) {
            'abierto'  => 'badge-active',
            'cerrado'  => 'badge-warn',
            'aprobado' => 'badge-blue',
            default    => 'badge-warn',
        };
    }
}
