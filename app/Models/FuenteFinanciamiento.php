<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FuenteFinanciamiento extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'fuentes_financiamiento';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'tipo', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
