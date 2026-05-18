<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

use App\Traits\FiltraPorEjercicio;

class ConciliacionBancaria extends Model implements Auditable
{
    use SoftDeletes, FiltraPorEjercicio;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'conciliaciones_bancarias';

    protected $fillable = [
        'numero', 'cuenta_bancaria_id', 'ejercicio_fiscal_id',
        'anio', 'mes', 'fecha_desde', 'fecha_hasta',
        'saldo_segun_banco', 'saldo_segun_libros', 'diferencia',
        'estado', 'fecha_aprobacion', 'aprobado_por',
        'observaciones', 'creado_por',
    ];

    protected $casts = [
        'fecha_desde'       => 'date',
        'fecha_hasta'       => 'date',
        'fecha_aprobacion'  => 'date',
        'saldo_segun_banco' => 'decimal:2',
        'saldo_segun_libros'=> 'decimal:2',
        'diferencia'        => 'decimal:2',
    ];

    // Relaciones
    public function cuenta()          { return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id'); }
    public function ejercicioFiscal() { return $this->belongsTo(EjercicioFiscal::class); }
    public function creadoPor()       { return $this->belongsTo(User::class, 'creado_por'); }
    public function aprobadoPor()     { return $this->belongsTo(User::class, 'aprobado_por'); }
    public function detalles()        { return $this->hasMany(ConciliacionDetalle::class, 'conciliacion_bancaria_id'); }

    // Helpers
    public function esBorrador(): bool { return $this->estado === 'borrador'; }
    public function esAprobada(): bool { return $this->estado === 'aprobada'; }

    public function getMesLabel(): string {
        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return $meses[$this->mes] ?? '';
    }

    public function getEstadoBadgeClass(): string {
        return match($this->estado) {
            'aprobada' => 'badge-active',
            'anulada'  => 'badge-danger',
            default    => 'badge-warn',
        };
    }

    public static function generarNumero(int $anio, int $mes): string {
        return 'CON-' . $anio . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);
    }
}

// Modelo auxiliar inline para detalles
