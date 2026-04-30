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

class CompromisosController extends Controller implements HasMiddleware
{
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

        return view('presupuesto.compromisos.index', compact('q', 'ejercicios', 'unidades'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    public function create()
    {
        $ejercicio     = CatalogoCache::ejercicioActivo();
        $unidades      = CatalogoCache::unidades();
        $partidas      = CatalogoCache::partidas();
        $proyectos     = ProyectoSia::whereIn('estado', ['activo', 'formulacion'])->orderBy('nombre')->get();
        $beneficiarios = Beneficiario::activos()->orderBy('razon_social')->get();

        return view('presupuesto.compromisos.create', compact('ejercicio', 'unidades', 'partidas', 'proyectos', 'beneficiarios'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
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
    public function show(Compromiso $compromiso)
    {
        $compromiso->load(['ejercicioFiscal', 'unidadEjecutora', 'proyecto', 'creadoPor', 'aprobadoPor', 'causaciones']);
        return view('presupuesto.compromisos.show', compact('compromiso'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    public function edit(Compromiso $compromiso)
    {
        if (!$compromiso->esBorrador()) {
            return redirect()->route('presupuesto.compromisos.show', $compromiso)
                ->with('error', 'Solo se pueden editar compromisos en Borrador.');
        }
        $proyectos = ProyectoSia::whereIn('estado', ['activo', 'formulacion'])->orderBy('nombre')->get();
        return view('presupuesto.compromisos.edit', compact('compromiso', 'proyectos'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────
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
