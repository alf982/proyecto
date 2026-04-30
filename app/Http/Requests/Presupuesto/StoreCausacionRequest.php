<?php

namespace App\Http\Requests\Presupuesto;

use Illuminate\Foundation\Http\FormRequest;

class StoreCausacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('causaciones.crear');
    }

    public function rules(): array
    {
        return [
            'compromiso_id'         => ['required', 'exists:compromisos,id'],
            'tipo_documento'        => ['required', 'in:factura,contrato,recibo,planilla,otro'],
            'numero_documento'      => ['nullable', 'string', 'max:60'],
            'fecha_documento'       => ['nullable', 'date'],
            'descripcion_documento' => ['nullable', 'string', 'max:300'],
            'concepto'              => ['required', 'string', 'max:500'],
            'fecha_causacion'       => ['required', 'date'],
            'monto_sin_iva'         => ['nullable', 'numeric', 'min:0'],
            'alicuota_iva'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto_causado'         => ['required', 'numeric', 'min:0.01'],
            'monto_retencion'       => ['nullable', 'numeric', 'min:0'],
            'observaciones'         => ['nullable', 'string'],
            'retenciones'           => ['nullable', 'array'],
            'retenciones.*'         => ['integer', 'exists:retenciones,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'compromiso_id.required'   => 'Debe seleccionar el compromiso asociado.',
            'compromiso_id.exists'     => 'El compromiso seleccionado no existe.',
            'tipo_documento.required'  => 'El tipo de documento es obligatorio.',
            'tipo_documento.in'        => 'El tipo de documento no es válido.',
            'concepto.required'        => 'El concepto es obligatorio.',
            'fecha_causacion.required' => 'La fecha de causación es obligatoria.',
            'monto_causado.required'   => 'El monto causado es obligatorio.',
            'monto_causado.min'        => 'El monto causado debe ser mayor a cero.',
        ];
    }
}
