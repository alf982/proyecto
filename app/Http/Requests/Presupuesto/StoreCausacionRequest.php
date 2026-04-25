<?php

namespace App\Http\Requests\Presupuesto;

use Illuminate\Foundation\Http\FormRequest;

class StoreCausacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ejercicio_fiscal_id'       => 'required|exists:ejercicios_fiscales,id',
            'unidad_ejecutora_id'        => 'required|exists:unidades_ejecutoras,id',
            'credito_presupuestario_id'  => 'required|exists:creditos_presupuestarios,id',
            'partida_presupuestaria_id'  => 'required|exists:partidas_presupuestarias,id',
            'beneficiario'              => 'required|string|max:200',
            'rif_beneficiario'          => 'nullable|string|max:20',
            'tipo_documento'            => 'required|in:factura,contrato,nomina,orden_compra,otro',
            'numero_documento'          => 'required|string|max:50',
            'fecha_documento'           => 'required|date|before_or_equal:today',
            'descripcion_documento'     => 'nullable|string|max:500',
            'concepto'                  => 'required|string|max:500',
            'monto_causado'             => 'required|numeric|min:0.01',
            'monto_retencion'           => 'nullable|numeric|min:0',
            'fecha_causacion'           => 'required|date',
            'observaciones'             => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'ejercicio_fiscal_id.required'      => 'Debe seleccionar un ejercicio fiscal.',
            'credito_presupuestario_id.required' => 'Debe seleccionar un crédito presupuestario.',
            'monto_causado.min'                 => 'El monto causado debe ser mayor a cero.',
            'monto_causado.required'            => 'El monto causado es obligatorio.',
            'fecha_documento.before_or_equal'   => 'La fecha del documento no puede ser futura.',
            'tipo_documento.in'                 => 'El tipo de documento debe ser: factura, contrato, nómina, orden de compra u otro.',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $retencion = (float) $this->monto_retencion;
                $causado   = (float) $this->monto_causado;
                if ($retencion > $causado) {
                    $validator->errors()->add('monto_retencion', 'La retención no puede ser mayor al monto causado.');
                }
            },
        ];
    }
}
