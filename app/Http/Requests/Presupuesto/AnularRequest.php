<?php

namespace App\Http\Requests\Presupuesto;

use Illuminate\Foundation\Http\FormRequest;

class AnularRequest extends FormRequest
{
    /**
     * Autorización genérica: el permiso específico se controla
     * en el middleware del controlador según el recurso (compromiso, causacion, pago).
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo_anulacion' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
            'motivo_anulacion.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}
