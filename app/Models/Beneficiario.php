<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo Beneficiario
 * 
 * Representa a la entidad jurídica o persona natural a la cual se le 
 * generan compromisos presupuestarios y se le realizan pagos.
 * Centraliza la información fiscal (RIF) y bancaria.
 */
class Beneficiario extends Model implements Auditable
{
    // Habilita el borrado lógico
    use SoftDeletes;
    
    // Habilita el registro de auditoría (quién y cuándo modificó los datos)
    use \OwenIt\Auditing\Auditable;

    protected $table = 'beneficiarios';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'rif', 'razon_social', 'nombre_comercial', 'tipo',
        'telefono', 'email', 'direccion',
        'banco_nombre', 'banco_cuenta', 'banco_tipo_cuenta',
        'activo', 'observaciones',
    ];

    /**
     * Casteo automático de atributos
     */
    protected $casts = ['activo' => 'boolean'];

    /**
     * RELACIÓN: Compromisos (HasMany)
     * Órdenes de compra o servicios asignadas a este beneficiario.
     */
    public function compromisos() { return $this->hasMany(Compromiso::class); }

    /**
     * RELACIÓN: Causaciones (HasMany)
     * Facturas o recibos registrados a nombre de este beneficiario (Gasto causado).
     */
    public function causaciones() { return $this->hasMany(Causacion::class); }

    /**
     * RELACIÓN: Pagos (HasMany)
     * Pagos efectivos realizados a las cuentas bancarias de este beneficiario.
     */
    public function pagos()       { return $this->hasMany(Pago::class); }

    /**
     * SCOPE: Beneficiarios Activos
     */
    public function scopeActivos($q) { return $q->where('activo', true); }

    /**
     * SCOPE: Filtrar por Tipo
     * @param string $tipo Ej: 'proveedor', 'funcionario'
     */
    public function scopeTipo($q, $tipo) { return $q->where('tipo', $tipo); }

    /**
     * ACCESSOR: Nombre Completo
     * Utilizado comúnmente en selects y listas.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "[{$this->rif}] {$this->razon_social}";
    }

    /**
     * HELPER: Etiqueta del Tipo
     * Retorna el tipo de beneficiario en formato legible para el humano.
     */
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
