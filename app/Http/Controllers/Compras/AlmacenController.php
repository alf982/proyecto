<?php
namespace App\Http\Controllers\Compras;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AlmacenController extends Controller implements HasMiddleware {
    public static function middleware(): array
    {
        return [
            new Middleware('can:compras.almacenes.ver', only: ['index', 'show']),
            new Middleware('can:compras.almacenes.crear', only: ['create', 'store']),
            new Middleware('can:compras.almacenes.editar', only: ['edit', 'update']),
        ];
    }
    public function index() {
        $almacenes = Almacen::withCount('articulos')->orderBy('codigo')->get();
        return view('compras.almacenes.index', compact('almacenes'));
    }
    public function create() { return view('compras.almacenes.create'); }
    public function store(Request $request) {
        $request->validate(['codigo'=>'required|unique:almacenes,codigo','nombre'=>'required']);
        Almacen::create([...$request->only(['codigo','nombre','ubicacion','responsable']),'activo'=>true,'creado_por'=>auth()->id()]);
        return redirect()->route('compras.almacenes.index')->with('success','Almacén creado correctamente.');
    }
    public function show(Almacen $almacen) {
        $almacen->load('articulos');
        $articulos = $almacen->articulos()->with('creadoPor')->orderBy('nombre')->paginate(25);
        $stats = [
            'total'      => $almacen->articulos()->count(),
            'bajo_stock' => $almacen->articulos()->whereColumn('stock_actual','<=','stock_minimo')->where('stock_minimo','>',0)->count(),
            'sin_stock'  => $almacen->articulos()->where('stock_actual','<=',0)->count(),
            'valor_total'=> $almacen->articulos()->selectRaw('SUM(stock_actual * precio_referencia) as v')->value('v') ?? 0,
        ];
        return view('compras.almacenes.show', compact('almacen','articulos','stats'));
    }
    public function edit(Almacen $almacen) { return view('compras.almacenes.edit', compact('almacen')); }
    public function update(Request $request, Almacen $almacen) {
        $request->validate(['codigo'=>'required|unique:almacenes,codigo,'.$almacen->id,'nombre'=>'required']);
        $almacen->update([...$request->only(['codigo','nombre','ubicacion','responsable']),'activo'=>(bool)$request->activo]);
        return redirect()->route('compras.almacenes.index')->with('success','Almacén actualizado.');
    }
}
