<?php
namespace App\Http\Controllers\Compras;
use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\EjercicioFiscal;
use App\Models\SolicitudCompra;
use App\Models\SolicitudDetalle;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class SolicitudController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:compras.solicitudes.ver',     only: ['index', 'show']),
            new Middleware('can:compras.solicitudes.crear',   only: ['create', 'store']),
            new Middleware('can:compras.solicitudes.aprobar', only: ['aprobar', 'rechazar']),
        ];
    }

    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        $q = SolicitudCompra::with(['solicitadoPor'])
            ->withCount('detalles')
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->estado, fn($q,$v) => $q->where('estado', $v))
            ->when($request->prioridad, fn($q,$v) => $q->where('prioridad', $v))
            ->when($request->search, fn($q,$v) => $q->where(fn($q) =>
                $q->where('numero','like',"%$v%")->orWhere('motivo','like',"%$v%")
            ))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Artículos agotados o bajo mínimo para el banner informativo
        $bajoStock = Articulo::activos()->bajoStock()->count();
        $sinStock  = Articulo::activos()->where('stock_actual', '<=', 0)->count();

        return view('compras.solicitudes.index', compact('q', 'bajoStock', 'sinStock'));
    }

    public function create()
    {
        // Todos los artículos activos — priorizamos los agotados
        $articulos = Articulo::activos()
            ->with('almacen')
            ->orderByRaw('stock_actual <= stock_minimo DESC')
            ->orderBy('nombre')
            ->get();

        // Los agotados o bajo mínimo para preseleccionar
        $agotados = $articulos->filter(fn($a) => $a->getBajoStock() || $a->stock_actual <= 0);

        $articulosJson = $articulos->map(fn($a) => [
            'id'     => $a->id,
            'codigo' => $a->codigo,
            'nombre' => $a->nombre,
            'unidad' => $a->unidad_medida,
            'stock'  => (float) $a->stock_actual,
            'minimo' => (float) $a->stock_minimo,
        ])->values()->toJson();

        $agotadosJson = $agotados->map(fn($a) => [
            'id'     => $a->id,
            'codigo' => $a->codigo,
            'nombre' => $a->nombre,
            'unidad' => $a->unidad_medida,
            'stock'  => (float) $a->stock_actual,
            'minimo' => (float) $a->stock_minimo,
        ])->values()->toJson();

        return view('compras.solicitudes.create', compact('articulos', 'agotados', 'articulosJson', 'agotadosJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'motivo'               => 'required|string|max:500',
            'prioridad'            => 'required|in:baja,media,alta,urgente',
            'lineas'               => 'required|array|min:1',
            'lineas.*.articulo_id' => 'required|exists:articulos,id',
            'lineas.*.descripcion' => 'required|string',
            'lineas.*.cantidad'    => 'required|numeric|min:0.01',
        ]);

        $ejercicio = EjercicioFiscal::where('estado','activo')->first();

        DB::transaction(function () use ($request, $ejercicio) {
            $sol = SolicitudCompra::create([
                'numero'              => SolicitudCompra::generarNumero(now()->year),
                'ejercicio_fiscal_id' => $ejercicio?->id,
                'unidad_ejecutora_id' => null,      // ya no aplica por oficina
                'motivo'              => $request->motivo,
                'tipo'                => 'bienes',   // siempre bienes para reabastecimiento
                'prioridad'           => $request->prioridad,
                'fecha_requerida'     => $request->fecha_requerida ?: null,
                'estado'              => 'enviada',
                'observaciones'       => $request->observaciones,
                'solicitado_por'      => auth()->id(),
            ]);

            foreach ($request->lineas as $i => $linea) {
                if (!($linea['descripcion'] ?? null)) continue;
                SolicitudDetalle::create([
                    'solicitud_compra_id' => $sol->id,
                    'articulo_id'         => $linea['articulo_id'],
                    'descripcion'         => $linea['descripcion'],
                    'unidad_medida'       => $linea['unidad_medida'] ?? 'unidad',
                    'cantidad'            => $linea['cantidad'],
                    'precio_estimado'     => $linea['precio_estimado'] ?? 0,
                    'especificaciones'    => $linea['especificaciones'] ?? null,
                    'orden'               => $i + 1,
                ]);
            }
        });

        return redirect()->route('compras.solicitudes.index')
            ->with('success', 'Solicitud de reabastecimiento enviada correctamente.');
    }

    public function show(SolicitudCompra $solicitud)
    {
        $solicitud->load(['detalles.articulo.almacen', 'solicitadoPor', 'aprobadoPor']);
        return view('compras.solicitudes.show', compact('solicitud'));
    }

    public function aprobar(SolicitudCompra $solicitud)
    {
        $solicitud->update([
            'estado'           => 'aprobada',
            'aprobado_por'     => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);
        return back()->with('success', 'Solicitud aprobada. Puede proceder a crear la Orden de Compra.');
    }

    public function rechazar(Request $request, SolicitudCompra $solicitud)
    {
        $request->validate(['motivo_rechazo' => 'required|string|min:10']);
        $solicitud->update([
            'estado'         => 'rechazada',
            'motivo_rechazo' => $request->motivo_rechazo,
        ]);
        return back()->with('success', 'Solicitud rechazada.');
    }
}
