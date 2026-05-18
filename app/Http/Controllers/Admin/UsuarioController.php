<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadEjecutora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;

/**
 * Controlador para la gestión de Usuarios del sistema.
 * 
 * Permite listar, crear, editar y desactivar usuarios, así como 
 * asignarles roles (Spatie Permission) y asociarlos a una Unidad Ejecutora.
 */
class UsuarioController extends Controller implements HasMiddleware
{
    /**
     * Define los middlewares de autorización basados en permisos.
     * Utiliza la nueva sintaxis de Laravel 11 (HasMiddleware).
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:usuarios.ver', only: ['index', 'show']),
            new Middleware('can:usuarios.crear', only: ['create', 'store']),
            new Middleware('can:usuarios.editar', only: ['edit', 'update']),
            new Middleware('can:usuarios.eliminar', only: ['destroy']), // 'eliminar' actúa como desactivar (Soft Delete lógico)
        ];
    }

    /**
     * Muestra el listado de usuarios con filtros de búsqueda.
     *
     * @param Request $request Contiene los parámetros de búsqueda ('search' y 'rol')
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Carga ambiciosa (Eager Loading) de roles y unidades para evitar el problema N+1
        $q = User::with(['roles', 'unidadEjecutora'])
            // Filtro por nombre o correo electrónico
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
            // Filtro dinámico por Rol (usando relationship de Spatie)
            ->when($request->rol, fn($q, $r) => $q->whereHas('roles', fn($q) => $q->where('name', $r)))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString(); // Mantiene los filtros en los enlaces de paginación

        // Obtenemos los roles para llenar el <select> del filtro
        $roles = Role::orderBy('name')->get();
        return view('admin.usuarios.index', compact('q', 'roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles    = Role::orderBy('name')->get();
        // Solo mostramos unidades activas para asignación
        $unidades = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('admin.usuarios.create', compact('roles', 'unidades'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validación de datos básicos
        $request->validate([
            'name'               => 'required|string|max:200',
            'email'              => 'required|email|unique:users,email',
            'cedula_numero'      => 'nullable|digits_between:6,9',
            'telefono'           => 'nullable|string|max:20',
            'unidad_ejecutora_id'=> 'nullable|exists:unidades_ejecutoras,id',
            'password'           => 'required|string|min:8|confirmed',
            'rol'                => 'required|exists:roles,name',
        ]);

        // Lógica de negocio: Ensambla la cédula concatenando el prefijo (V/E) y el número
        $cedula = null;
        if ($request->filled('cedula_numero')) {
            $cedula = strtoupper($request->cedula_prefijo ?? 'V') . '-' . $request->cedula_numero;
            // Verificar unicidad de cédula manualmente, ya que en el form viene separada
            if (User::where('cedula', $cedula)->exists()) {
                return back()->withErrors(['cedula_numero' => 'La cédula ' . $cedula . ' ya está registrada.'])->withInput();
            }
        }

        // Creación del usuario (El password se hashea automáticamente en el Modelo User por los 'casts')
        $user = User::create([
            'name'               => ucwords(strtolower($request->name)), // Normalización de mayúsculas (Ej: Juan Perez)
            'email'              => $request->email,
            'cedula'             => $cedula,
            'telefono'           => $request->telefono,
            'unidad_ejecutora_id'=> $request->unidad_ejecutora_id,
            'password'           => $request->password,
            'activo'             => $request->boolean('activo', true),
            'email_verified_at'  => now(), // Marcado auto-verificado para sistemas cerrados institucionales
        ]);

        // Asignación del rol usando el paquete Spatie Permission
        $user->assignRole($request->rol);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$user->name}' creado correctamente.");
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     *
     * @param User $usuario (Inyección automática de modelo / Route Model Binding)
     * @return \Illuminate\View\View
     */
    public function edit(User $usuario)
    {
        $roles    = Role::orderBy('name')->get();
        $unidades = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'unidades'));
    }

    /**
     * Actualiza los datos de un usuario en la base de datos.
     *
     * @param Request $request
     * @param User $usuario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $usuario)
    {
        // Validación asegurando que el email sea único, ignorando el del propio usuario
        $request->validate([
            'name'               => 'required|string|max:200',
            'email'              => "required|email|unique:users,email,{$usuario->id}",
            'cedula_numero'      => 'nullable|digits_between:6,9',
            'telefono'           => 'nullable|string|max:20',
            'unidad_ejecutora_id'=> 'nullable|exists:unidades_ejecutoras,id',
            'password'           => 'nullable|string|min:8|confirmed', // Nullable: Solo si quiere cambiarla
            'rol'                => 'required|exists:roles,name',
        ]);

        // Ensamblaje de cédula actualizado
        $cedula = null;
        if ($request->filled('cedula_numero')) {
            $cedula = strtoupper($request->cedula_prefijo ?? 'V') . '-' . $request->cedula_numero;
            // Validar unicidad excluyendo al usuario actual
            if (User::where('cedula', $cedula)->where('id', '!=', $usuario->id)->exists()) {
                return back()->withErrors(['cedula_numero' => 'La cédula ' . $cedula . ' ya está en uso por otro usuario.'])->withInput();
            }
        }

        // Actualización de datos básicos
        $usuario->update([
            'name'               => ucwords(strtolower($request->name)),
            'email'              => $request->email,
            'cedula'             => $cedula,
            'telefono'           => $request->telefono,
            'unidad_ejecutora_id'=> $request->unidad_ejecutora_id ?: null,
            'activo'             => $request->boolean('activo'),
        ]);

        // Si se envió una nueva contraseña, se actualiza (El Modelo User se encarga del Hash)
        if ($request->filled('password')) {
            $usuario->update(['password' => $request->password]);
        }

        // Sincroniza los roles: Elimina los roles anteriores y asigna solo el nuevo
        $usuario->syncRoles([$request->rol]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado correctamente.");
    }

    /**
     * "Elimina" un usuario.
     * En este sistema no se borran físicamente (Hard Delete) por motivos de auditoría,
     * en su lugar, se alternan entre estado 'activo' e 'inactivo' (Soft Delete Lógico manual).
     *
     * @param User $usuario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $usuario)
    {
        // Regla de seguridad vital: Un usuario no puede desactivar su propia cuenta en sesión
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta mientras estás logueado.');
        }

        $nuevoEstado = !$usuario->activo;
        $usuario->update(['activo' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'activado' : 'desactivado';
        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' {$accion} correctamente.");
    }
}
