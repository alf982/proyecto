<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFiscal;
use App\Models\ProyectoSia;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProyectoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:proyectos.ver', only: ['index', 'show']),
            new Middleware('can:proyectos.crear', only: ['create', 'store']),
            new Middleware('can:proyectos.editar', only: ['edit', 'update']),
            new Middleware('can:proyectos.eliminar', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $proyectos = ProyectoSia::with(['ejercicioFiscal', 'unidadEjecutora'])
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%$s%")->orWhere('codigo', 'like', "%$s%"))
            ->when($request->ejercicio_id, fn($q, $e) => $q->where('ejercicio_fiscal_id', $e))
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        return view('presupuesto.proyectos.index', compact('proyectos', 'ejercicios'));
    }

    public function create()
    {
        $ejercicios = EjercicioFiscal::whereIn('estado', ['borrador', 'activo'])->orderByDesc('anio')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('presupuesto.proyectos.create', compact('ejercicios', 'unidades'));
    }

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

    public function edit(ProyectoSia $proyecto)
    {
        $ejercicios = EjercicioFiscal::whereIn('estado', ['borrador', 'activo'])->orderByDesc('anio')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('presupuesto.proyectos.edit', compact('proyecto', 'ejercicios', 'unidades'));
    }

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

    public function destroy(ProyectoSia $proyecto)
    {
        $proyecto->delete();
        return redirect()->route('presupuesto.proyectos.index')
            ->with('success', "Proyecto '{$proyecto->nombre}' eliminado.");
    }
}
