<?php

namespace App\Http\Requests\Presupuesto;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pagos.crear');
    }

    public function rules(): array
    {
        return [
            'causacion_id'      => ['required', 'exists:causaciones,id'],
            'tipo_pago'         => ['required', 'in:cheque,transferencia,efectivo,otro'],
            'numero_referencia' => ['nullable', 'string', 'max:60'],
            'banco'             => ['nullable', 'string', 'max:100'],
            'cuenta_bancaria'   => ['nullable', 'string', 'max:30'],
            'monto_pagado'      => ['required', 'numeric', 'min:0.01'],
            'monto_sin_iva'     => ['nullable', 'numeric', 'min:0'],
            'fecha_pago'        => ['required', 'date'],
            'concepto'          => ['required', 'string', 'max:500'],
            'observaciones'     => ['nullable', 'string'],
            'retenciones'       => ['nullable', 'array'],
            'retenciones.*'     => ['integer', 'exists:retenciones,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'causacion_id.required' => 'Debe seleccionar la causación a pagar.',
            'causacion_id.exists'   => 'La causación seleccionada no existe.',
            'tipo_pago.required'    => 'El tipo de pago es obligatorio.',
            'tipo_pago.in'          => 'El tipo de pago seleccionado no es válido.',
            'monto_pagado.required' => 'El monto a pagar es obligatorio.',
            'monto_pagado.min'      => 'El monto debe ser mayor a cero.',
            'fecha_pago.required'   => 'La fecha de pago es obligatoria.',
            'concepto.required'     => 'El concepto del pago es obligatorio.',
        ];
    }
}
