<?php

namespace App\Http\Requests\Presupuesto;

use Illuminate\Foundation\Http\FormRequest;

class ProcesarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pagos.procesar');
    }

    public function rules(): array
    {
        return [
            'numero_referencia' => ['nullable', 'string', 'max:60'],
            'banco'             => ['nullable', 'string', 'max:100'],
            'cuenta_bancaria'   => ['nullable', 'string', 'max:30'],
            'fecha_pago'        => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_pago.required' => 'La fecha de pago es obligatoria para procesar.',
        ];
    }
}
