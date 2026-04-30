<?php

namespace App\Http\Requests\Presupuesto;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompromisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('compromisos.crear');
    }

    public function rules(): array
    {
        return [
            'partida_presupuestaria_id' => ['required', 'exists:partidas_presupuestarias,id'],
            'unidad_ejecutora_id'       => ['required', 'exists:unidades_ejecutoras,id'],
            'beneficiario_id'           => ['nullable', 'exists:beneficiarios,id'],
            'beneficiario'              => ['nullable', 'string', 'max:200'],
            'rif_beneficiario'          => ['nullable', 'string', 'max:20'],
            'concepto'                  => ['required', 'string', 'max:500'],
            'monto_sin_iva'             => ['nullable', 'numeric', 'min:0'],
            'alicuota_iva'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monto'                     => ['required', 'numeric', 'min:0.01'],
            'fecha_compromiso'          => ['required', 'date'],
            'fecha_vencimiento'         => ['nullable', 'date', 'after_or_equal:fecha_compromiso'],
            'tipo_documento'            => ['nullable', 'in:factura,contrato,recibo,planilla,otro'],
            'numero_documento'          => ['nullable', 'string', 'max:60'],
            'fecha_documento'           => ['nullable', 'date'],
            'descripcion_documento'     => ['nullable', 'string', 'max:300'],
            'observaciones'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'partida_presupuestaria_id.required' => 'Debe seleccionar una partida presupuestaria.',
            'unidad_ejecutora_id.required'       => 'Debe seleccionar una unidad ejecutora.',
            'monto.required'                     => 'El monto del compromiso es obligatorio.',
            'monto.min'                          => 'El monto debe ser mayor a cero.',
            'fecha_compromiso.required'          => 'La fecha del compromiso es obligatoria.',
            'fecha_vencimiento.after_or_equal'   => 'La fecha de vencimiento debe ser igual o posterior a la fecha del compromiso.',
        ];
    }

    /**
     * Normaliza el nombre del beneficiario libre antes de validar.
     */
    protected function prepareForValidation(): void
    {
        if ($this->beneficiario) {
            $this->merge(['beneficiario' => ucwords(strtolower($this->beneficiario))]);
        }
    }
}
