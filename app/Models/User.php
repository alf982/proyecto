<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasRoles;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name', 'email', 'password',
        'cedula', 'telefono', 'unidad_ejecutora_id', 'activo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    public function unidadEjecutora()
    {
        return $this->belongsTo(UnidadEjecutora::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->name;
    }
}
