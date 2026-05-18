<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFiscal;
use App\Models\ProyectoSia;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador de Proyectos (SIA)
 * 
 * Gestiona los "Proyectos o Acciones Centralizadas" a los cuales se les asigna presupuesto.
 * En la estructura presupuestaria pública, todo Crédito Presupuestario debe pertenecer 
 * obligatoriamente a un Proyecto o Acción Centralizada, el cual a su vez es responsable
 * de una Unidad Ejecutora.
 */
class ProyectoController extends Controller implements HasMiddleware
{
    /**
     * Define los permisos necesarios para acceder a las rutas.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:proyectos.ver', only: ['index', 'show']),
            new Middleware('can:proyectos.crear', only: ['create', 'store']),
            new Middleware('can:proyectos.editar', only: ['edit', 'update']),
            new Middleware('can:proyectos.eliminar', only: ['destroy']),
        ];
    }

    /**
     * Muestra el catálogo de Proyectos.
     * Soporta filtrado por ejercicio fiscal y por término de búsqueda (nombre/código).
     */
    public function index(Request $request)
    {
        // Se asegura el uso de eager loading (with) para prevenir el problema N+1
        $proyectos = ProyectoSia::with(['ejercicioFiscal', 'unidadEjecutora'])
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")->orWhere('codigo', 'like', "%$s%"))
            ->when($request->ejercicio_id, fn($q, $e) => $q->where('ejercicio_fiscal_id', $e))
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        return view('presupuesto.proyectos.index', compact('proyectos', 'ejercicios'));
    }

    /**
     * Muestra el formulario para crear un nuevo Proyecto.
     */
    public function create()
    {
        // Solo permitir asociar a ejercicios que aún admiten formulación
        $ejercicios = EjercicioFiscal::whereIn('estado', ['borrador', 'activo'])->orderByDesc('anio')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        
        return view('presupuesto.proyectos.create', compact('ejercicios', 'unidades'));
    }

    /**
     * Almacena un nuevo proyecto en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ejercicio_fiscal_id'  => 'required|exists:ejercicios_fiscales,id',
            'unidad_ejecutora_id'  => 'required|exists:unidades_ejecutoras,id',
            'codigo'               => 'required|string|max:30',
            'nombre'               => 'required|string|max:300',
            'descripcion'          => 'nullable|string',
            'objetivo'             => 'nullable|string',
            'fecha_inicio'         => 'nullable|date',
            'fecha_fin'            => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'               => 'required|in:formulacion,activo,suspendido,terminado',
        ]);

        ProyectoSia::create($data);

        return redirect()->route('presupuesto.proyectos.index')
            ->with('success', "Proyecto '{$data['nombre']}' creado correctamente.");
    }

    /**
     * Muestra el formulario para editar un proyecto existente.
     */
    public function edit(ProyectoSia $proyecto)
    {
        $ejercicios = EjercicioFiscal::whereIn('estado', ['borrador', 'activo'])->orderByDesc('anio')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        
        return view('presupuesto.proyectos.edit', compact('proyecto', 'ejercicios', 'unidades'));
    }

    /**
     * Actualiza los datos de un proyecto.
     */
    public function update(Request $request, ProyectoSia $proyecto)
    {
        $data = $request->validate([
            'ejercicio_fiscal_id'  => 'required|exists:ejercicios_fiscales,id',
            'unidad_ejecutora_id'  => 'required|exists:unidades_ejecutoras,id',
            'codigo'               => 'required|string|max:30',
            'nombre'               => 'required|string|max:300',
            'descripcion'          => 'nullable|string',
            'objetivo'             => 'nullable|string',
            'fecha_inicio'         => 'nullable|date',
            'fecha_fin'            => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'               => 'required|in:formulacion,activo,suspendido,terminado',
        ]);

        $proyecto->update($data);

        return redirect()->route('presupuesto.proyectos.index')
            ->with('success', "Proyecto '{$proyecto->nombre}' actualizado.");
    }

    /**
     * Elimina lógicamente (SoftDelete) un proyecto.
     */
    public function destroy(ProyectoSia $proyecto)
    {
        // Nota: Solo usa SoftDeletes. No usa forceDelete en cascada como Partidas
        // porque un proyecto no se elimina una vez que ya tiene ejecución.
        $proyecto->delete();
        
        return redirect()->route('presupuesto.proyectos.index')
            ->with('success', "Proyecto '{$proyecto->nombre}' eliminado.");
    }
}
