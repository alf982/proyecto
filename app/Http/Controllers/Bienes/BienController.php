<?php
namespace App\Http\Controllers\Bienes;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\CategoriaBien;
use App\Models\MovimientoBien;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Controlador del Inventario de Bienes Nacionales
 * 
 * Gestiona el ciclo de vida de los activos fijos de la institución.
 * Todas las operaciones de cambio de estado (incorporación, traslados, bajas)
 * se realizan mediante transacciones de base de datos que garantizan
 * la creación simultánea del registro de auditoría en MovimientoBien.
 */
class BienController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:bienes.ver', only: ['index', 'show']),
            new Middleware('can:bienes.crear', only: ['create', 'store']),
            new Middleware('can:bienes.editar', only: ['edit', 'update', 'trasladar', 'darDeBaja']),
        ];
    }
    
    /**
     * Catálogo principal de inventario.
     * Soporta filtros combinados por estado, categoría y texto libre (N° Inventario, Serial, Descripción).
     */
    public function index(Request $request)
    {
        $bienes = Bien::with(['categoria', 'unidadEjecutora'])
            ->when($request->estado,     fn($q, $v) => $q->where('estado', $v))
            ->when($request->categoria,  fn($q, $v) => $q->where('categoria_bien_id', $v))
            ->when($request->search,     fn($q, $v) => $q->where(fn($q) => $q->where('numero_inventario', 'like', "%$v%")->orWhere('descripcion', 'like', "%$v%")->orWhere('serial', 'like', "%$v%")))
            ->orderByDesc('id')
            ->paginate(20)->withQueryString();
            
        $categorias = CategoriaBien::activas()->orderBy('nombre')->get();
        return view('bienes.bienes.index', compact('bienes', 'categorias'));
    }

    public function create()
    {
        $categorias = CategoriaBien::activas()->orderBy('nombre')->get();
        $unidades   = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();
        $numero     = Bien::generarNumero();
        return view('bienes.bienes.create', compact('categorias', 'unidades', 'numero'));
    }

    /**
     * Incorpora un nuevo bien al inventario institucional.
     * Crea el activo y registra automáticamente el acta de 'incorporacion' en el historial.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero_inventario'  => 'required|unique:bienes,numero_inventario|max:30',
            'categoria_bien_id'  => 'required|exists:categorias_bien,id',
            'unidad_ejecutora_id'=> 'required|exists:unidades_ejecutoras,id',
            'descripcion'        => 'required|string|max:300',
            'valor_adquisicion'  => 'required|numeric|min:0',
            'fecha_incorporacion'=> 'required|date',
        ]);
        
        DB::transaction(function () use ($request) {
            $bien = Bien::create([
                ...$request->only(['numero_inventario', 'categoria_bien_id', 'unidad_ejecutora_id', 'descripcion', 'marca', 'modelo', 'serial', 'anio_adquisicion', 'valor_adquisicion', 'ubicacion', 'responsable', 'fecha_incorporacion', 'observaciones']),
                'valor_actual' => $request->valor_adquisicion,
                'estado'       => 'activo',
                'creado_por'   => auth()->id(),
            ]);
            
            MovimientoBien::create([
                'bien_id'          => $bien->id,
                'tipo'             => 'incorporacion',
                'unidad_destino_id'=> $request->unidad_ejecutora_id,
                'motivo'           => 'Incorporación inicial al inventario.',
                'fecha'            => $request->fecha_incorporacion,
                'usuario_id'       => auth()->id(),
            ]);
        });
        
        return redirect()->route('bienes.bienes.index')->with('success', 'Bien incorporado al inventario.');
    }

    public function show(Bien $bien)
    {
        // Carga profunda del historial de movimientos para trazabilidad (Auditoría)
        $bien->load(['categoria', 'unidadEjecutora', 'movimientos.usuario', 'movimientos.unidadOrigen', 'movimientos.unidadDestino']);
        return view('bienes.bienes.show', compact('bien'));
    }

    public function edit(Bien $bien)
    {
        $categorias = CategoriaBien::activas()->orderBy('nombre')->get();
        $unidades   = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();
        return view('bienes.bienes.edit', compact('bien', 'categorias', 'unidades'));
    }

    public function update(Request $request, Bien $bien)
    {
        $request->validate([
            'numero_inventario'  => 'required|max:30|unique:bienes,numero_inventario,' . $bien->id,
            'categoria_bien_id'  => 'required|exists:categorias_bien,id',
            'unidad_ejecutora_id'=> 'required|exists:unidades_ejecutoras,id',
            'descripcion'        => 'required|string|max:300',
            'valor_actual'       => 'required|numeric|min:0',
        ]);
        
        $bien->update($request->only(['numero_inventario', 'categoria_bien_id', 'unidad_ejecutora_id', 'descripcion', 'marca', 'modelo', 'serial', 'anio_adquisicion', 'valor_adquisicion', 'valor_actual', 'ubicacion', 'responsable', 'estado', 'observaciones']));
        
        return redirect()->route('bienes.bienes.show', $bien)->with('success', 'Bien actualizado correctamente.');
    }

    /**
     * Desincorpora un bien del inventario (por daño, obsolescencia, etc).
     * El estado cambia a 'dado_de_baja' y se levanta un acta de movimiento.
     */
    public function darDeBaja(Request $request, Bien $bien)
    {
        $request->validate(['motivo_baja' => 'required|string|min:10']);
        
        abort_if($bien->estado === 'dado_de_baja', 403, 'El bien ya está dado de baja.');
        
        DB::transaction(function () use ($request, $bien) {
            $bien->update([
                'estado'      => 'dado_de_baja',
                'fecha_baja'  => now()->toDateString(),
                'motivo_baja' => $request->motivo_baja
            ]);
            
            MovimientoBien::create([
                'bien_id'         => $bien->id,
                'tipo'            => 'baja',
                'unidad_origen_id'=> $bien->unidad_ejecutora_id,
                'motivo'          => $request->motivo_baja,
                'fecha'           => now()->toDateString(),
                'usuario_id'      => auth()->id(),
            ]);
        });
        
        return back()->with('success', 'Bien dado de baja del inventario.');
    }

    /**
     * Reasigna la custodia del bien a otra unidad ejecutora.
     * Genera un acta de traslado que traza de dónde a dónde fue movido.
     */
    public function trasladar(Request $request, Bien $bien)
    {
        $request->validate([
            'unidad_destino_id' => 'required|exists:unidades_ejecutoras,id',
            'motivo'            => 'required|string|min:5',
        ]);

        abort_if(
            $request->unidad_destino_id == $bien->unidad_ejecutora_id,
            422,
            'La unidad destino debe ser diferente a la unidad actual del bien.'
        );
        
        DB::transaction(function () use ($request, $bien) {
            MovimientoBien::create([
                'bien_id'          => $bien->id,
                'tipo'             => 'traslado',
                'unidad_origen_id' => $bien->unidad_ejecutora_id,
                'unidad_destino_id'=> $request->unidad_destino_id,
                'motivo'           => $request->motivo,
                'fecha'            => now()->toDateString(),
                'usuario_id'       => auth()->id(),
            ]);
            
            $bien->update(['unidad_ejecutora_id' => $request->unidad_destino_id]);
        });
        
        return back()->with('success', 'Bien trasladado correctamente.');
    }
}
