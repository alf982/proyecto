<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BeneficiarioController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:beneficiarios.ver', only: ['index', 'show']),
            new Middleware('can:beneficiarios.crear', only: ['create', 'store']),
            new Middleware('can:beneficiarios.editar', only: ['edit', 'update', 'toggleActivo']),
            new Middleware('can:usuarios.eliminar', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $q = Beneficiario::when($request->search, fn($q, $v) => $q->where(function($q) use ($v) {
                $q->where('rif','like',"%$v%")
                  ->orWhere('razon_social','like',"%$v%")
                  ->orWhere('nombre_comercial','like',"%$v%");
            }))
            ->when($request->tipo, fn($q, $v) => $q->where('tipo', $v))
            ->when($request->estado !== null && $request->estado !== '', fn($q) => $q->where('activo', $request->estado))
            ->orderBy('razon_social')
            ->paginate(20)->withQueryString();

        return view('admin.beneficiarios.index', compact('q'));
    }

    public function create()
    {
        return view('admin.beneficiarios.create');
    }

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

        Beneficiario::create($request->all());

        return redirect()->route('admin.beneficiarios.index')
            ->with('success', 'Beneficiario registrado correctamente.');
    }

    public function show(Beneficiario $beneficiario)
    {
        return view('admin.beneficiarios.show', compact('beneficiario'));
    }

    public function edit(Beneficiario $beneficiario)
    {
        return view('admin.beneficiarios.edit', compact('beneficiario'));
    }

    public function update(Request $request, Beneficiario $beneficiario)
    {
        $request->validate([
            'rif'          => 'required|string|max:15|unique:beneficiarios,rif,'.$beneficiario->id,
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

    public function toggleActivo(Beneficiario $beneficiario)
    {
        $beneficiario->update(['activo' => !$beneficiario->activo]);
        $msg = $beneficiario->activo ? 'Beneficiario activado.' : 'Beneficiario desactivado.';
        return back()->with('success', $msg);
    }

    public function destroy(Beneficiario $beneficiario)
    {
        $beneficiario->delete();
        return redirect()->route('admin.beneficiarios.index')
            ->with('success', 'Beneficiario eliminado.');
    }
}
