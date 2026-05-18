<?php
namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Articulo;
use App\Models\InventarioMovimiento;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Artículos (Catálogo de Compras e Inventario)
 * 
 * Gestiona el catálogo principal de bienes, materiales y servicios.
 * Permite la creación y modificación de fichas de artículos, y provee
 * la funcionalidad de "Ajuste Manual" para correcciones de inventario (Kardex).
 */
class ArticuloController extends Controller implements HasMiddleware {
    
    public static function middleware(): array
    {
        return [
            new Middleware('can:compras.articulos.ver', only: ['index', 'show']),
            new Middleware('can:compras.articulos.crear', only: ['create', 'store']),
            new Middleware('can:compras.articulos.editar', only: ['edit', 'update', 'ajustar']),
        ];
    }
    
    /**
     * Listado general del catálogo.
     * Soporta filtrado dinámico por búsqueda de texto, tipo y bajo stock.
     */
    public function index(Request $request) {
        $q = Articulo::with('almacen')
            ->when($request->search, fn($q, $v) => $q->where(fn($q) => $q->where('codigo','like',"%$v%")->orWhere('nombre','like',"%$v%")))
            ->when($request->tipo,   fn($q, $v) => $q->where('tipo', $v))
            ->when($request->bajo_stock, fn($q) => $q->whereColumn('stock_actual','<=','stock_minimo'))
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();
            
        $resumen = [
            'total'      => Articulo::count(),
            'bajo_stock' => Articulo::whereColumn('stock_actual','<=','stock_minimo')->where('stock_minimo','>',0)->count(),
            'sin_stock'  => Articulo::where('stock_actual', 0)->count(),
        ];
        
        return view('compras.articulos.index', compact('q','resumen'));
    }
    
    public function create() {
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        return view('compras.articulos.create', compact('almacenes'));
    }
    
    public function store(Request $request) {
        $request->validate([
            'codigo' => 'required|unique:articulos,codigo',
            'nombre' => 'required',
            'tipo'   => 'required|in:bien,servicio,material,equipo'
        ]);
        
        Articulo::create([
            ...$request->only(['codigo','nombre','descripcion','tipo','unidad_medida','precio_referencia','stock_minimo','categoria','almacen_id']),
            'stock_actual' => $request->stock_inicial ?? 0,
            'activo'       => true,
            'creado_por'   => auth()->id()
        ]);
        
        return redirect()->route('compras.articulos.index')
            ->with('success','Artículo creado correctamente.');
    }
    
    /**
     * Muestra la ficha técnica del artículo y su Kardex (movimientos de inventario).
     */
    public function show(Articulo $articulo) {
        $movimientos = $articulo->movimientos()->latest('fecha')->latest('id')->paginate(20);
        return view('compras.articulos.show', compact('articulo','movimientos'));
    }
    
    public function edit(Articulo $articulo) {
        $almacenes = Almacen::activos()->orderBy('nombre')->get();
        return view('compras.articulos.edit', compact('articulo','almacenes'));
    }
    
    public function update(Request $request, Articulo $articulo) {
        $request->validate([
            'codigo' => 'required|unique:articulos,codigo,'.$articulo->id,
            'nombre' => 'required'
        ]);
        
        $articulo->update($request->only([
            'codigo','nombre','descripcion','tipo','unidad_medida',
            'precio_referencia','stock_minimo','categoria','almacen_id'
        ]));
        
        return redirect()->route('compras.articulos.show',$articulo)
            ->with('success','Artículo actualizado.');
    }
    
    /**
     * Ajuste de inventario manual (Kardex).
     * Registra una entrada o salida manual y recalcula el stock actual
     * asegurando integridad mediante transacciones de base de datos.
     */
    public function ajustar(Request $request, Articulo $articulo) {
        $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:0.01',
            'concepto' => 'required|string'
        ]);
        
        DB::transaction(function () use($request, $articulo) {
            $antes  = $articulo->stock_actual;
            $delta  = $request->tipo === 'salida' ? -$request->cantidad : $request->cantidad;
            $nuevo  = max(0, $antes + $delta);
            
            $articulo->update(['stock_actual' => $nuevo]);
            
            InventarioMovimiento::create([
                'articulo_id'    => $articulo->id,
                'almacen_id'     => $articulo->almacen_id,
                'tipo'           => $request->tipo,
                'origen_tipo'    => 'manual',
                'cantidad'       => $request->cantidad,
                'precio_unitario'=> $request->precio_unitario ?? 0,
                'stock_anterior' => $antes,
                'stock_nuevo'    => $nuevo,
                'concepto'       => $request->concepto,
                'fecha'          => now()->toDateString(),
                'creado_por'     => auth()->id(),
            ]);
        });
        
        return back()->with('success','Inventario ajustado correctamente.');
    }
}
