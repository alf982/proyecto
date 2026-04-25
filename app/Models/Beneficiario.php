<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Beneficiario extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'beneficiarios';

    protected $fillable = [
        'rif', 'razon_social', 'nombre_comercial', 'tipo',
        'telefono', 'email', 'direccion',
        'banco_nombre', 'banco_cuenta', 'banco_tipo_cuenta',
        'activo', 'observaciones',
    ];

    protected $casts = ['activo' => 'boolean'];

    // Relaciones
    public function compromisos() { return $this->hasMany(Compromiso::class); }
    public function causaciones() { return $this->hasMany(Causacion::class); }
    public function pagos()       { return $this->hasMany(Pago::class); }

    // Scopes
    public function scopeActivos($q) { return $q->where('activo', true); }
    public function scopeTipo($q, $tipo) { return $q->where('tipo', $tipo); }

    // Helpers
    public function getNombreCompletoAttribute(): string
    {
        return "[{$this->rif}] {$this->razon_social}";
    }

    public function getTipoLabel(): string
    {
        return match($this->tipo) {
            'proveedor'   => 'Proveedor',
            'contratista' => 'Contratista',
            'funcionario' => 'Funcionario',
            default       => 'Otro',
        };
    }
}
