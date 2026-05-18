<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador para la gestión de Unidades Ejecutoras.
 * 
 * Las Unidades Ejecutoras representan los departamentos, direcciones 
 * o divisiones de la institución (Ej: Recursos Humanos, Administración).
 * Soportan una estructura jerárquica mediante la relación 'parent_id'.
 */
class UnidadEjecutoraController extends Controller implements HasMiddleware
{
    /**
     * Define los middlewares de autorización basados en los permisos de Spatie.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:unidades.ver', only: ['index', 'show']),
            new Middleware('can:unidades.crear', only: ['create', 'store']),
            new Middleware('can:unidades.editar', only: ['edit', 'update']),
            new Middleware('can:unidades.eliminar', only: ['destroy']),
        ];
    }

    /**
     * Muestra el listado de unidades ejecutoras.
     *
     * @param Request $request Contiene el parámetro de búsqueda ('search')
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Carga ambiciosa de la relación 'parent' para mostrar de quién depende cada unidad
        $unidades = UnidadEjecutora::with('parent')
            ->when($request->search, fn($q, $s) => $q->where('nombre', 'like', "%$s%")->orWhere('codigo', 'like', "%$s%"))
            ->orderBy('codigo')
            ->paginate(20)
            ->withQueryString();

        return view('admin.unidades.index', compact('unidades'));
    }

    /**
     * Muestra el formulario para registrar una nueva unidad.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Se obtienen todas las unidades activas para que puedan ser seleccionadas como 'Unidad Padre'
        $padres = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('admin.unidades.create', compact('padres'));
    }

    /**
     * Almacena una nueva unidad ejecutora en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validación asegurando que el código (Ej: 'DIR-RRHH') sea único
        $data = $request->validate([
            'codigo'     => 'required|string|max:20|unique:unidades_ejecutoras,codigo',
            'nombre'     => 'required|string|max:200',
            'descripcion'=> 'nullable|string',
            'parent_id'  => 'nullable|exists:unidades_ejecutoras,id',
            'activo'     => 'boolean',
        ]);

        // Guardado combinando los datos validados y forzando el estado activo por defecto
        UnidadEjecutora::create($data + ['activo' => $request->boolean('activo', true)]);

        return redirect()->route('admin.unidades.index')
            ->with('success', "Unidad ejecutora '{$data['nombre']}' creada correctamente.");
    }

    /**
     * Muestra el formulario para editar una unidad existente.
     *
     * @param UnidadEjecutora $unidad
     * @return \Illuminate\View\View
     */
    public function edit(UnidadEjecutora $unidad)
    {
        // Excluimos la unidad actual de la lista de padres para evitar referencias circulares (depender de sí misma)
        $padres = UnidadEjecutora::activas()->where('id', '!=', $unidad->id)->orderBy('nombre')->get();
        return view('admin.unidades.edit', compact('unidad', 'padres'));
    }

    /**
     * Actualiza los datos de la unidad en la base de datos.
     *
     * @param Request $request
     * @param UnidadEjecutora $unidad
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, UnidadEjecutora $unidad)
    {
        // El código debe seguir siendo único, pero ignorando el registro actual
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

    /**
     * Elimina una unidad ejecutora.
     * PRECAUCIÓN: Si la BD usa SoftDeletes, solo se oculta. Si no, se borra físicamente.
     * Puede fallar por restricción de clave foránea si hay usuarios o causaciones asociadas.
     *
     * @param UnidadEjecutora $unidad
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(UnidadEjecutora $unidad)
    {
        $unidad->delete();
        return redirect()->route('admin.unidades.index')
            ->with('success', "Unidad '{$unidad->nombre}' eliminada.");
    }
}
