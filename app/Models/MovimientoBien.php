<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoBien extends Model
{
    protected $table = 'movimientos_bien';

    protected $fillable = [
        'bien_id', 'tipo', 'unidad_origen_id', 'unidad_destino_id', 'motivo', 'fecha', 'usuario_id',
    ];

    protected $casts = ['fecha' => 'date'];

    public function bien()          { return $this->belongsTo(Bien::class); }
    public function unidadOrigen()  { return $this->belongsTo(UnidadEjecutora::class, 'unidad_origen_id'); }
    public function unidadDestino() { return $this->belongsTo(UnidadEjecutora::class, 'unidad_destino_id'); }
    public function usuario()       { return $this->belongsTo(User::class, 'usuario_id'); }
}
