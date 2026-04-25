<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UnidadEjecutoraController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:unidades.ver', only: ['index', 'show']),
            new Middleware('can:unidades.crear', only: ['create', 'store']),
            new Middleware('can:unidades.editar', only: ['edit', 'update']),
            new Middleware('can:unidades.eliminar', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $unidades = UnidadEjecutora::with('parent')
            ->when($request->search, fn($q, $s) => $q->where('nombre', 'like', "%$s%")->orWhere('codigo', 'like', "%$s%"))
            ->orderBy('codigo')
            ->paginate(20)->withQueryString();

        return view('admin.unidades.index', compact('unidades'));
    }

    public function create()
    {
        $padres = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('admin.unidades.create', compact('padres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'     => 'required|string|max:20|unique:unidades_ejecutoras,codigo',
            'nombre'     => 'required|string|max:200',
            'descripcion'=> 'nullable|string',
            'parent_id'  => 'nullable|exists:unidades_ejecutoras,id',
            'activo'     => 'boolean',
        ]);

        UnidadEjecutora::create($data + ['activo' => $request->boolean('activo', true)]);

        return redirect()->route('admin.unidades.index')
            ->with('success', "Unidad ejecutora '{$data['nombre']}' creada correctamente.");
    }

    public function edit(UnidadEjecutora $unidad)
    {
        $padres = UnidadEjecutora::activas()->where('id', '!=', $unidad->id)->orderBy('nombre')->get();
        return view('admin.unidades.edit', compact('unidad', 'padres'));
    }

    public function update(Request $request, UnidadEjecutora $unidad)
    {
        $data = $request->validate([
            'codigo'     => "required|string|max:20|unique:unidades_ejecutoras,codigo,{$unidad->id}",
            'nombre'     => 'required|string|max:200',
            'descripcion'=> 'nullable|string',
            'parent_id'  => 'nullable|exists:unidades_ejecutoras,id',
            'activo'     => 'boolean',
        ]);

        $unidad->update($data + ['activo' => $request->boolean('activo', true)]);

        return redirect()->route('admin.unidades.index')
            ->with('success', "Unidad '{$unidad->nombre}' actualizada correctamente.");
    }

    public function destroy(UnidadEjecutora $unidad)
    {
        $unidad->delete();
        return redirect()->route('admin.unidades.index')
            ->with('success', "Unidad '{$unidad->nombre}' eliminada.");
    }
}
