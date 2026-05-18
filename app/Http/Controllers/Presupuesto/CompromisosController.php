<?php
namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Http\Requests\Presupuesto\AnularRequest;
use App\Http\Requests\Presupuesto\StoreCompromisoRequest;
use App\Http\Requests\Presupuesto\UpdateCompromisoRequest;
use App\Models\Beneficiario;
use App\Models\Compromiso;
use App\Models\EjercicioFiscal;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\UnidadEjecutora;
use App\Services\CatalogoCache;
use App\Services\Presupuesto\CompromisoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador de Compromisos
 * 
 * Gestiona el ciclo de vida de un compromiso presupuestario.
 * Delega la lógica de negocio compleja (validación de saldos, transacciones, generación de causación)
 * al `CompromisoService` para mantener el controlador limpio y enfocado en HTTP.
 * 
 * Ciclo de Estado:
 * 1. Borrador -> 2. Aprobado -> 3. Causado (Automático por el servicio)
 *               -> Anulado
 */
class CompromisosController extends Controller implements HasMiddleware
{
    /**
     * Inyección de dependencia del servicio de negocio.
     */
    public function __construct(private readonly CompromisoService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:compromisos.ver',    only: ['index', 'show']),
            new Middleware('can:compromisos.crear',  only: ['create', 'store', 'edit', 'update']),
            new Middleware('can:compromisos.aprobar',only: ['aprobar']),
            new Middleware('can:compromisos.anular', only: ['anular']),
        ];
    }

    // ── LISTADO ──────────────────────────────────────────────────────
    
    /**
     * Muestra el listado paginado de compromisos.
     * Incluye una vista separada para los compromisos pendientes de aprobación ("Bandeja de entrada").
     */
    public function index(Request $request)
    {
        $q = Compromiso::with(['ejercicioFiscal', 'unidadEjecutora'])
            // 'partida' ya se carga via $with en el modelo
            ->when($request->ejercicio, fn($q, $v) => $q->where('ejercicio_fiscal_id', $v))
            ->when($request->estado,    fn($q, $v) => $q->where('estado', $v))
            ->when($request->search,    fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('numero', 'like', "%$v%")->orWhere('beneficiario', 'like', "%$v%");
            }))
            ->orderByDesc('numero')
            ->paginate(15)->withQueryString();

        $ejercicios = CatalogoCache::ejercicios();
        $unidades   = CatalogoCache::unidades();

        // Compromisos en borrador pendientes de aprobación
        $compromisosPendientes = Compromiso::where('estado', 'borrador')
            ->with(['unidadEjecutora:id,nombre', 'partida:id,codigo,descripcion'])
            ->select(['id', 'numero', 'beneficiario', 'monto', 'fecha_compromiso', 'unidad_ejecutora_id', 'partida_presupuestaria_id'])
            ->orderByDesc('id')
            ->get();

        return view('presupuesto.compromisos.index', compact('q', 'ejercicios', 'unidades', 'compromisosPendientes'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    
    /**
     * Muestra el formulario para crear un compromiso.
     * Utiliza CatalogoCache para cargar los combos sin sobrecargar la DB.
     */
    public function create()
    {
        $ejercicio     = CatalogoCache::ejercicioActivo();
        $unidades      = CatalogoCache::unidades();
        $partidas      = CatalogoCache::partidas();
        $proyectos     = CatalogoCache::proyectos();
        $beneficiarios = CatalogoCache::beneficiarios();

        return view('presupuesto.compromisos.create', compact('ejercicio', 'unidades', 'partidas', 'proyectos', 'beneficiarios'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    
    /**
     * Procesa la creación de un compromiso.
     * Resuelve el proveedor (del catálogo o manual) y delega al Service.
     */
    public function store(StoreCompromisoRequest $request)
    {
        // Resolver nombre del beneficiario
        if ($request->beneficiario_id) {
            $benModel  = Beneficiario::findOrFail($request->beneficiario_id);
            $nombreBen = $benModel->razon_social;
            $rifBen    = $benModel->rif;
        } else {
            if (empty(trim($request->beneficiario ?? ''))) {
                return back()->withErrors(['beneficiario' => 'Seleccione un beneficiario del catálogo o escriba el nombre.'])->withInput();
            }
            $nombreBen = ucwords(strtolower($request->beneficiario));
            $rifBen    = $request->rif_beneficiario;
        }

        try {
            $this->service->crear($request->all(), $nombreBen, $rifBen);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['monto' => $e->getMessage()])->withInput();
        }

        return redirect()->route('presupuesto.compromisos.index')
            ->with('success', 'Compromiso registrado correctamente. Apruébalo para generar la causación.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    
    /**
     * Muestra la vista detallada ("Show") del compromiso.
     */
    public function show(Compromiso $compromiso)
    {
        $compromiso->load(['ejercicioFiscal', 'unidadEjecutora', 'proyecto', 'creadoPor', 'aprobadoPor', 'causaciones']);
        return view('presupuesto.compromisos.show', compact('compromiso'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    
    /**
     * Muestra el formulario de edición. Solo permitido si el estado es 'borrador'.
     */
    public function edit(Compromiso $compromiso)
    {
        if (!$compromiso->esBorrador()) {
            return redirect()->route('presupuesto.compromisos.show', $compromiso)
                ->with('error', 'Solo se pueden editar compromisos en Borrador.');
        }
        $proyectos = CatalogoCache::proyectos();
        return view('presupuesto.compromisos.edit', compact('compromiso', 'proyectos'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────
    
    /**
     * Procesa la actualización. Delega al Service para verificar si hay impacto financiero.
     */
    public function update(UpdateCompromisoRequest $request, Compromiso $compromiso)
    {
        try {
            $this->service->actualizar($compromiso, $request->all());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['monto' => $e->getMessage()])->withInput();
        }

        return redirect()->route('presupuesto.compromisos.show', $compromiso)
            ->with('success', 'Compromiso actualizado.');
    }

    // ── APROBAR ───────────────────────────────────────────────────────
    
    /**
     * Cambia el estado a 'Aprobado'.
     * Delega al servicio que además se encargará de crear la Causación inicial.
     */
    public function aprobar(Compromiso $compromiso)
    {
        try {
            $this->service->aprobar($compromiso);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presupuesto.compromisos.show', $compromiso->fresh())
            ->with('success', 'Compromiso aprobado. La causación fue generada en borrador — complétala desde el módulo de Causaciones.');
    }

    // ── ANULAR ────────────────────────────────────────────────────────
    
    /**
     * Anula el compromiso y libera el saldo retenido en la partida.
     */
    public function anular(AnularRequest $request, Compromiso $compromiso)
    {

        try {
            $this->service->anular($compromiso, $request->motivo_anulacion);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presupuesto.compromisos.index')
            ->with('success', 'Compromiso y su causación anulados. Saldo liberado en la partida.');
    }
}
