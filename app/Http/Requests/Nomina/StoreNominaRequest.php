<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;

class StoreNominaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tipo_nomina'               => 'required|in:ordinaria,vacacional,utilidades,bono,liquidacion',
            'periodo_inicio'            => 'required|date',
            'periodo_fin'               => 'required|date|after_or_equal:periodo_inicio',
            'partida_presupuestaria_id' => 'nullable|exists:partidas_presupuestarias,id',
            'observaciones'             => 'nullable|string|max:500',
            'retenciones'               => 'nullable|array',
            'retenciones.*'             => 'integer|exists:retenciones,id',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_nomina.required'       => 'Debe seleccionar el tipo de nómina.',
            'tipo_nomina.in'             => 'El tipo debe ser: ordinaria, vacacional, utilidades, bono o liquidación.',
            'periodo_inicio.required'    => 'La fecha de inicio del período es obligatoria.',
            'periodo_fin.required'       => 'La fecha de fin del período es obligatoria.',
            'periodo_fin.after_or_equal' => 'El período fin debe ser igual o posterior al inicio.',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $ejercicio = \App\Models\EjercicioFiscal::where('estado', 'activo')->first();
                if (!$ejercicio) {
                    $validator->errors()->add('tipo_nomina', 'No hay un ejercicio fiscal activo. No se puede crear una nómina.');
                }
                $empleados = \App\Models\Empleado::activos()->count();
                if ($empleados === 0) {
                    $validator->errors()->add('tipo_nomina', 'No hay empleados activos registrados para procesar la nómina.');
                }
            },
        ];
    }
}
