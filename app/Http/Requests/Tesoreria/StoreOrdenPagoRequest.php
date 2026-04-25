<?php

namespace App\Http\Requests\Tesoreria;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenPagoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'causacion_id'         => 'required|exists:causaciones,id',
            'beneficiario_id'      => 'nullable|exists:beneficiarios,id',
            'tipo_pago'            => 'required|in:transferencia,cheque,efectivo',
            'concepto'             => 'required|string|max:500',
            'observaciones'        => 'nullable|string|max:1000',
            'lineas'               => 'required|array|min:1',
            'lineas.*.descripcion' => 'required|string|max:300',
            'lineas.*.monto'       => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'causacion_id.required'        => 'Debe seleccionar una causación presupuestaria.',
            'causacion_id.exists'          => 'La causación seleccionada no existe.',
            'tipo_pago.required'           => 'El tipo de pago es obligatorio.',
            'concepto.required'            => 'El concepto es obligatorio.',
            'lineas.required'              => 'Debe agregar al menos una línea de detalle.',
            'lineas.*.descripcion.required'=> 'La descripción de cada línea es obligatoria.',
            'lineas.*.monto.min'           => 'El monto debe ser mayor a cero.',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if ($this->causacion_id) {
                    $causacion = \App\Models\Causacion::find($this->causacion_id);
                    if (!$causacion) return;
                    if ($causacion->estado !== 'aprobada') {
                        $validator->errors()->add('causacion_id',
                            'Solo se pueden generar órdenes de pago para causaciones en estado Aprobada. Esta está en estado: ' . $causacion->estado
                        );
                    }
                    // Verificar que no haya otra orden activa para la misma causación
                    $ordenActiva = \App\Models\OrdenPago::where('causacion_id', $this->causacion_id)
                        ->whereNotIn('estado', ['anulada'])
                        ->first();
                    if ($ordenActiva) {
                        $validator->errors()->add('causacion_id',
                            "Ya existe la Orden {$ordenActiva->numero} ({$ordenActiva->estado}) vinculada a esta causación."
                        );
                    }
                }
            },
        ];
    }
}
