<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo User (Usuario del Sistema)
 * 
 * Representa a una persona con acceso al sistema ERP (SIA).
 * Implementa la interfaz de autenticación base de Laravel, el control de roles
 * mediante Spatie y la trazabilidad de acciones mediante OwenIt (Auditoría).
 */
class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable;
    
    // Trait para manejar roles y permisos (ej: $user->hasRole('admin'))
    use HasRoles;
    
    // Trait que registra automáticamente en la BD quién, qué y cuándo modificó este registro
    use \OwenIt\Auditing\Auditable;

    /**
     * Campos que pueden ser asignados masivamente (Mass Assignment)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password',
        'cedula', 'telefono', 'unidad_ejecutora_id', 'activo',
    ];

    /**
     * Campos que deben ocultarse al serializar el modelo (JSON/Arrays)
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mutadores automáticos de tipos de datos.
     * En Laravel 11 se recomienda usar el método casts().
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'password'          => 'hashed', // Hashea el password automáticamente al guardar
            'activo'            => 'boolean',
        ];
    }

    /**
     * RELACIÓN: Unidad Ejecutora (BelongsTo)
     * 
     * Un usuario puede pertenecer u operar bajo una Unidad Ejecutora (Departamento) específica.
     * Si es null, tiene alcance global o no está asignado.
     */
    public function unidadEjecutora()
    {
        return $this->belongsTo(UnidadEjecutora::class);
    }

    /**
     * SCOPE: Usuarios Activos
     * 
     * Permite filtrar rápidamente: User::activos()->get()
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * ACCESSOR: Nombre Completo
     * 
     * Retorna el nombre formateado. Útil en las vistas: {{ $user->nombre_completo }}
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->name;
    }
}
