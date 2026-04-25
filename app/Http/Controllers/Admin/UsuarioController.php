<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadEjecutora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:usuarios.ver', only: ['index', 'show']),
            new Middleware('can:usuarios.crear', only: ['create', 'store']),
            new Middleware('can:usuarios.editar', only: ['edit', 'update']),
            new Middleware('can:usuarios.eliminar', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $q = User::with(['roles', 'unidadEjecutora'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
            ->when($request->rol, fn($q, $r) => $q->whereHas('roles', fn($q) => $q->where('name', $r)))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();
        return view('admin.usuarios.index', compact('q', 'roles'));
    }

    public function create()
    {
        $roles    = Role::orderBy('name')->get();
        $unidades = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('admin.usuarios.create', compact('roles', 'unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:200',
            'email'              => 'required|email|unique:users,email',
            'cedula_numero'      => 'nullable|digits_between:6,9',
            'telefono'           => 'nullable|string|max:20',
            'unidad_ejecutora_id'=> 'nullable|exists:unidades_ejecutoras,id',
            'password'           => 'required|string|min:8|confirmed',
            'rol'                => 'required|exists:roles,name',
        ]);

        // Ensambla la cédula desde el prefijo y el número
        $cedula = null;
        if ($request->filled('cedula_numero')) {
            $cedula = strtoupper($request->cedula_prefijo ?? 'V') . '-' . $request->cedula_numero;
            // Verificar unicidad manualmente
            if (User::where('cedula', $cedula)->exists()) {
                return back()->withErrors(['cedula_numero' => 'La cédula ' . $cedula . ' ya está registrada.'])->withInput();
            }
        }

        $user = User::create([
            'name'               => ucwords(strtolower($request->name)),
            'email'              => $request->email,
            'cedula'             => $cedula,
            'telefono'           => $request->telefono,
            'unidad_ejecutora_id'=> $request->unidad_ejecutora_id,
            'password'           => $request->password,
            'activo'             => $request->boolean('activo', true),
            'email_verified_at'  => now(),
        ]);

        $user->assignRole($request->rol);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$user->name}' creado correctamente.");
    }

    public function edit(User $usuario)
    {
        $roles    = Role::orderBy('name')->get();
        $unidades = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'unidades'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'               => 'required|string|max:200',
            'email'              => "required|email|unique:users,email,{$usuario->id}",
            'cedula_numero'      => 'nullable|digits_between:6,9',
            'telefono'           => 'nullable|string|max:20',
            'unidad_ejecutora_id'=> 'nullable|exists:unidades_ejecutoras,id',
            'password'           => 'nullable|string|min:8|confirmed',
            'rol'                => 'required|exists:roles,name',
        ]);

        // Ensambla la cédula desde el prefijo y el número
        $cedula = null;
        if ($request->filled('cedula_numero')) {
            $cedula = strtoupper($request->cedula_prefijo ?? 'V') . '-' . $request->cedula_numero;
            // Verificar unicidad excluyendo el usuario actual
            if (User::where('cedula', $cedula)->where('id', '!=', $usuario->id)->exists()) {
                return back()->withErrors(['cedula_numero' => 'La cédula ' . $cedula . ' ya está en uso por otro usuario.'])->withInput();
            }
        }

        $usuario->update([
            'name'               => ucwords(strtolower($request->name)),
            'email'              => $request->email,
            'cedula'             => $cedula,
            'telefono'           => $request->telefono,
            'unidad_ejecutora_id'=> $request->unidad_ejecutora_id ?: null,
            'activo'             => $request->boolean('activo'),
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => $request->password]);
        }

        $usuario->syncRoles([$request->rol]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado correctamente.");
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $nuevoEstado = !$usuario->activo;
        $usuario->update(['activo' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'activado' : 'desactivado';
        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' {$accion} correctamente.");
    }
}
