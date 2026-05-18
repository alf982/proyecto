<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador para la gestión de Beneficiarios.
 * 
 * Los beneficiarios son las entidades o personas a las cuales la institución
 * les realiza pagos (Proveedores, Contratistas, Empleados, etc.).
 * Es un catálogo maestro fundamental para los módulos de Presupuesto y Compras.
 */
class BeneficiarioController extends Controller implements HasMiddleware
{
    /**
     * Define los middlewares de autorización basados en los permisos de Spatie.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:beneficiarios.ver', only: ['index', 'show', 'searchAjax']),
            new Middleware('can:beneficiarios.crear', only: ['create', 'store']),
            new Middleware('can:beneficiarios.editar', only: ['edit', 'update', 'toggleActivo']),
            new Middleware('can:usuarios.eliminar', only: ['destroy']), // FIXME: Posible typo en permisos, evaluar si debería ser 'beneficiarios.eliminar'
        ];
    }

    /**
     * Muestra el listado paginado de beneficiarios.
     * Incluye filtros por búsqueda de texto libre, tipo de beneficiario y estado.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $q = Beneficiario::when($request->search, fn($q, $v) => $q->where(function($q) use ($v) {
                // Búsqueda en múltiples columnas
                $q->where('rif','like',"%$v%")
                  ->orWhere('razon_social','like',"%$v%")
                  ->orWhere('nombre_comercial','like',"%$v%");
            }))
            // Filtro exacto por tipo (proveedor, contratista, etc.)
            ->when($request->tipo, fn($q, $v) => $q->where('tipo', $v))
            // Filtro booleano por estado activo/inactivo (se verifica !== null porque '0' es inactivo)
            ->when($request->estado !== null && $request->estado !== '', fn($q) => $q->where('activo', $request->estado))
            ->orderBy('razon_social')
            ->paginate(20)
            ->withQueryString();

        return view('admin.beneficiarios.index', compact('q'));
    }

    /**
     * Muestra el formulario para crear un nuevo beneficiario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.beneficiarios.create');
    }

    /**
     * Almacena un nuevo beneficiario en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'rif'          => 'required|string|max:15|unique:beneficiarios,rif',
            'razon_social' => 'required|string|max:200',
            'nombre_comercial'  => 'nullable|string|max:200',
            'tipo'         => 'required|in:proveedor,contratista,funcionario,otro',
            'telefono'     => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:100',
            'direccion'    => 'nullable|string|max:300',
            'banco_nombre' => 'nullable|string|max:100',
            'banco_cuenta' => 'nullable|string|max:30',
            'banco_tipo_cuenta' => 'nullable|in:corriente,ahorro,otro',
            'observaciones'=> 'nullable|string',
        ]);

        // Asignación masiva (Mass Assignment) protegiendo variables con $fillable en el Modelo
        Beneficiario::create($request->all());

        return redirect()->route('admin.beneficiarios.index')
            ->with('success', 'Beneficiario registrado correctamente.');
    }

    /**
     * Muestra la ficha detallada de un beneficiario específico.
     *
     * @param Beneficiario $beneficiario
     * @return \Illuminate\View\View
     */
    public function show(Beneficiario $beneficiario)
    {
        return view('admin.beneficiarios.show', compact('beneficiario'));
    }

    /**
     * Muestra el formulario para editar los datos de un beneficiario.
     *
     * @param Beneficiario $beneficiario
     * @return \Illuminate\View\View
     */
    public function edit(Beneficiario $beneficiario)
    {
        return view('admin.beneficiarios.edit', compact('beneficiario'));
    }

    /**
     * Actualiza la información del beneficiario en la base de datos.
     *
     * @param Request $request
     * @param Beneficiario $beneficiario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Beneficiario $beneficiario)
    {
        $request->validate([
            'rif'          => 'required|string|max:15|unique:beneficiarios,rif,'.$beneficiario->id, // Ignora el RIF actual
            'razon_social' => 'required|string|max:200',
            'nombre_comercial'  => 'nullable|string|max:200',
            'tipo'         => 'required|in:proveedor,contratista,funcionario,otro',
            'telefono'     => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:100',
            'direccion'    => 'nullable|string|max:300',
            'banco_nombre' => 'nullable|string|max:100',
            'banco_cuenta' => 'nullable|string|max:30',
            'banco_tipo_cuenta' => 'nullable|in:corriente,ahorro,otro',
            'observaciones'=> 'nullable|string',
        ]);

        $beneficiario->update($request->all());

        return redirect()->route('admin.beneficiarios.index')
            ->with('success', 'Beneficiario actualizado correctamente.');
    }

    /**
     * Alterna el estado (Activo/Inactivo) del beneficiario.
     * Útil para bloquear pagos sin borrar el registro histórico.
     *
     * @param Beneficiario $beneficiario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActivo(Beneficiario $beneficiario)
    {
        $beneficiario->update(['activo' => !$beneficiario->activo]);
        $msg = $beneficiario->activo ? 'Beneficiario activado.' : 'Beneficiario desactivado.';
        return back()->with('success', $msg);
    }

    /**
     * Elimina lógicamente un beneficiario (SoftDeletes en el modelo).
     *
     * @param Beneficiario $beneficiario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Beneficiario $beneficiario)
    {
        $beneficiario->delete();
        return redirect()->route('admin.beneficiarios.index')
            ->with('success', 'Beneficiario eliminado.');
    }

    /**
     * API ENDPOINT: Búsqueda AJAX de Beneficiarios.
     * 
     * Este método es vital. Es consumido por librerías frontend (como Select2)
     * en los formularios de creación de Causaciones, Órdenes de Compra y Pagos
     * para buscar beneficiarios en tiempo real sin cargar todo el catálogo.
     *
     * @param Request $request Espera un parámetro 'q' con el término de búsqueda
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAjax(Request $request)
    {
        $term = $request->get('q');
        
        // Solo buscamos beneficiarios que estén habilitados para operar
        $query = Beneficiario::activos();

        if (!empty($term)) {
            $query->where(function($q) use ($term) {
                $q->where('rif', 'like', "%$term%")
                  ->orWhere('razon_social', 'like', "%$term%")
                  ->orWhere('nombre_comercial', 'like', "%$term%");
            });
        }

        // Limitamos a 20 resultados para optimizar la velocidad de respuesta JSON
        $results = $query->orderBy('razon_social')
            ->limit(20)
            ->get(['id', 'rif', 'razon_social', 'nombre_comercial', 'tipo', 'banco_nombre', 'banco_cuenta']);

        return response()->json($results);
    }
}
