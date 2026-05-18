<?php

namespace App\Http\Controllers\Retenciones;

use App\Http\Controllers\Controller;
use App\Models\Retencion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controlador del Catálogo de Retenciones Fiscales
 * 
 * Configura los impuestos legales exigidos por el marco normativo
 * (ISLR, IVA, Fiel Cumplimiento, Timbre Fiscal, etc.).
 * La integridad de datos se asegura evitando eliminar retenciones que
 * ya hayan sido usadas en causaciones o pagos históricos (usando soft deletes 
 * o bloqueo de eliminación).
 */
class RetencionController extends Controller
{
    /** Muestra el catálogo de retenciones ordenado por estado y código. */
    public function index()
    {
        $this->authorize('retenciones.ver');

        $retenciones = Retencion::withTrashed(false)
            ->orderBy('activo', 'desc')
            ->orderBy('codigo')
            ->paginate(20);

        return view('retenciones.index', compact('retenciones'));
    }

    public function create()
    {
        $this->authorize('retenciones.crear');
        $modulosDisponibles = Retencion::modulosDisponibles();
        return view('retenciones.create', compact('modulosDisponibles'));
    }

    /** Almacena una nueva retención fiscal, asegurando la consistencia del tipo y alícuota. */
    public function store(Request $request)
    {
        $this->authorize('retenciones.crear');

        $validated = $request->validate([
            'codigo'       => 'required|string|max:20|unique:retenciones,codigo',
            'nombre'       => 'required|string|max:150',
            'tipo'         => ['required', Rule::in(['porcentaje', 'monto_fijo', 'porcentaje_iva'])],
            'porcentaje'   => 'required_if:tipo,porcentaje|required_if:tipo,porcentaje_iva|nullable|numeric|min:0|max:100',
            'alicuota_iva' => 'required_if:tipo,porcentaje_iva|nullable|numeric|min:0|max:100',
            'monto_fijo'   => 'required_if:tipo,monto_fijo|nullable|numeric|min:0',
            'aplica_a'     => 'required|array|min:1',
            'aplica_a.*'   => Rule::in(array_keys(Retencion::modulosDisponibles())),
            'base_calculo' => 'nullable|string',   // No aplica para porcentaje_iva
            'obligatoria'  => 'boolean',
            'descripcion'  => 'nullable|string|max:500',
        ], [
            'codigo.unique'             => 'Este código ya existe.',
            'aplica_a.required'         => 'Seleccione al menos un módulo donde aplica.',
            'porcentaje.required_if'    => 'Debe ingresar el porcentaje de retención.',
            'monto_fijo.required_if'    => 'Debe ingresar el monto fijo.',
            'alicuota_iva.required_if'  => 'Debe ingresar la alícuota del IVA (ej: 16).',
        ]);

        // Para porcentaje_iva el base_calculo es siempre monto_bruto
        if ($validated['tipo'] === 'porcentaje_iva') {
            $validated['base_calculo'] = 'monto_bruto';
        } elseif (empty($validated['base_calculo'])) {
            $validated['base_calculo'] = 'monto_bruto';
        }

        $validated['activo']     = true;
        $validated['obligatoria']= $request->boolean('obligatoria');

        Retencion::create($validated);

        return redirect()->route('retenciones.index')
            ->with('success', 'Retención creada correctamente.');
    }

    public function edit(Retencion $retencion)
    {
        $this->authorize('retenciones.editar');
        $modulosDisponibles = Retencion::modulosDisponibles();
        return view('retenciones.edit', compact('retencion', 'modulosDisponibles'));
    }

    /** 
     * Actualiza la configuración de la retención.
     * Nota: Modificar el valor no alterará las causaciones previas que ya aplicaron
     * el histórico de este porcentaje.
     */
    public function update(Request $request, Retencion $retencion)
    {
        $this->authorize('retenciones.editar');

        $validated = $request->validate([
            'codigo'       => ['required','string','max:20', Rule::unique('retenciones','codigo')->ignore($retencion->id)],
            'nombre'       => 'required|string|max:150',
            'tipo'         => ['required', Rule::in(['porcentaje', 'monto_fijo', 'porcentaje_iva'])],
            'porcentaje'   => 'required_if:tipo,porcentaje|required_if:tipo,porcentaje_iva|nullable|numeric|min:0|max:100',
            'alicuota_iva' => 'required_if:tipo,porcentaje_iva|nullable|numeric|min:0|max:100',
            'monto_fijo'   => 'required_if:tipo,monto_fijo|nullable|numeric|min:0',
            'aplica_a'     => 'required|array|min:1',
            'aplica_a.*'   => Rule::in(array_keys(Retencion::modulosDisponibles())),
            'base_calculo' => 'nullable|string',
            'obligatoria'  => 'boolean',
            'descripcion'  => 'nullable|string|max:500',
        ]);

        if ($validated['tipo'] === 'porcentaje_iva') {
            $validated['base_calculo'] = 'monto_bruto';
        } elseif (empty($validated['base_calculo'])) {
            $validated['base_calculo'] = 'monto_bruto';
        }

        $validated['obligatoria'] = $request->boolean('obligatoria');

        $retencion->update($validated);

        return redirect()->route('retenciones.index')
            ->with('success', 'Retención actualizada correctamente.');
    }

    /** Activa o desactiva una retención */
    public function toggle(Retencion $retencion)
    {
        $this->authorize('retenciones.editar');

        $retencion->update(['activo' => !$retencion->activo]);

        $estado = $retencion->activo ? 'activada' : 'desactivada';
        return back()->with('success', "Retención {$estado} correctamente.");
    }

    /** Elimina una retención si no tiene usos registrados en transacciones (Causación, etc.) */
    public function destroy(Retencion $retencion)
    {
        $this->authorize('retenciones.editar');

        // Verificar que no esté siendo usada en registros existentes
        if ($retencion->aplicaciones()->exists()) {
            return back()->with('error',
                "No se puede eliminar «{$retencion->nombre}» porque ya fue aplicada en transacciones del sistema. Desactívala en su lugar."
            );
        }

        $nombre = $retencion->nombre;
        $retencion->delete();

        return redirect()->route('retenciones.index')
            ->with('success', "Retención «{$nombre}» eliminada correctamente.");
    }
}
