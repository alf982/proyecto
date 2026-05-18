<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFiscal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Ejercicios Fiscales
 * 
 * Gestiona los años fiscales en los que opera el presupuesto de la institución.
 * Controla el ciclo de vida de un ejercicio:
 * - Borrador: Recién creado, no se pueden registrar transacciones.
 * - Activo: Ejercicio actual en curso. SOLO PUEDE HABER UNO ACTIVO a la vez.
 * - Cerrado: Finalizado, histórico, inmutable.
 */
class EjercicioFiscalController extends Controller implements HasMiddleware
{
    /**
     * Define los permisos requeridos para cada acción.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:ejercicios.ver', only: ['index', 'show']),
            new Middleware('can:ejercicios.crear', only: ['create', 'store']),
            new Middleware('can:ejercicios.editar', only: ['edit', 'update', 'activar', 'destroy']),
            new Middleware('can:ejercicios.cerrar', only: ['cerrar']),
        ];
    }

    /**
     * Lista los ejercicios fiscales registrados.
     */
    public function index()
    {
        // Se usa carga ambiciosa (with) para la relación 'creadoPor' evitando N+1
        $ejercicios = EjercicioFiscal::with('creadoPor')
            ->orderByDesc('anio')->paginate(15);
        return view('presupuesto.ejercicios.index', compact('ejercicios'));
    }

    /**
     * Muestra el formulario para registrar un nuevo ejercicio fiscal.
     */
    public function create()
    {
        return view('presupuesto.ejercicios.create');
    }

    /**
     * Almacena un nuevo ejercicio fiscal en la BD.
     * Por defecto se crea en estado 'borrador'.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'anio'          => 'required|integer|min:2000|max:2100|unique:ejercicios_fiscales,anio',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        EjercicioFiscal::create($data + [
            'estado'     => 'borrador',
            'creado_por' => auth()->id(),
        ]);

        return redirect()->route('presupuesto.ejercicios.index')
            ->with('success', "Ejercicio fiscal {$data['anio']} creado correctamente.");
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(EjercicioFiscal $ejercicio)
    {
        return view('presupuesto.ejercicios.edit', compact('ejercicio'));
    }

    /**
     * Actualiza los datos de un ejercicio fiscal.
     * Regla de negocio: No se puede editar un ejercicio cerrado.
     */
    public function update(Request $request, EjercicioFiscal $ejercicio)
    {
        if ($ejercicio->estado === 'cerrado') {
            return back()->with('error', 'No se puede modificar un ejercicio cerrado.');
        }

        $data = $request->validate([
            'anio'          => "required|integer|min:2000|max:2100|unique:ejercicios_fiscales,anio,{$ejercicio->id}",
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        $ejercicio->update($data);

        return redirect()->route('presupuesto.ejercicios.index')
            ->with('success', "Ejercicio {$ejercicio->anio} actualizado.");
    }

    /**
     * Activa un ejercicio fiscal.
     * Regla de negocio: Asegura de forma atómica (DB::transaction) que
     * solo exista un ejercicio activo en todo el sistema.
     */
    public function activar(EjercicioFiscal $ejercicio)
    {
        DB::transaction(function () use ($ejercicio) {
            // Pasar cualquier ejercicio activo actual a borrador
            EjercicioFiscal::where('estado', 'activo')->update(['estado' => 'borrador']);
            
            // Activar el seleccionado
            $ejercicio->update(['estado' => 'activo']);
        });

        return back()->with('success', "Ejercicio {$ejercicio->anio} activado como ejercicio fiscal vigente.");
    }

    /**
     * Cierra definitivamente un ejercicio fiscal (Fin de año).
     * Registra la fecha exacta y el usuario que ejecutó el cierre.
     */
    public function cerrar(EjercicioFiscal $ejercicio)
    {
        $ejercicio->update([
            'estado'      => 'cerrado',
            'cerrado_por' => auth()->id(),
            'fecha_cierre'=> now(),
        ]);

        return back()->with('success', "Ejercicio {$ejercicio->anio} cerrado definitivamente.");
    }

    /**
     * Elimina un ejercicio fiscal.
     * Regla de negocio: Solo se pueden eliminar si están en borrador y no 
     * tienen movimientos transaccionales asociados.
     */
    public function destroy(EjercicioFiscal $ejercicio)
    {
        if ($ejercicio->estado !== 'borrador') {
            return back()->with('error', 'Solo se pueden eliminar ejercicios en borrador.');
        }
        
        $ejercicio->delete();
        
        return redirect()->route('presupuesto.ejercicios.index')
            ->with('success', "Ejercicio {$ejercicio->anio} eliminado.");
    }
}
