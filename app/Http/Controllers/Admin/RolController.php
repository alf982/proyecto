<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.ver'),
            new Middleware('can:roles.gestionar', only: [
                'createRol', 'storeRol', 'editRol', 'updateRol', 'destroyRol',
            ]),
        ];
    }

    /** Etiquetas amigables — cubre todos los permisos existentes en la BD */
    public static function etiquetas(): array
    {
        return [
            // Dashboard
            'dashboard.ver'                   => ['Dashboard', 'Ver el panel principal del sistema'],

            // Usuarios
            'usuarios.ver'                    => ['Usuarios', 'Ver listado de usuarios del sistema'],
            'usuarios.crear'                  => ['Usuarios', 'Crear nuevos usuarios'],
            'usuarios.editar'                 => ['Usuarios', 'Editar usuarios existentes'],
            'usuarios.eliminar'               => ['Usuarios', 'Eliminar usuarios del sistema'],

            // Roles y Permisos
            'roles.ver'                       => ['Roles', 'Ver los roles definidos en el sistema'],
            'roles.gestionar'                 => ['Roles', 'Crear, editar y eliminar roles y sus permisos'],

            // Unidades Ejecutoras
            'unidades.ver'                    => ['Unidades Ejecutoras', 'Ver unidades ejecutoras registradas'],
            'unidades.crear'                  => ['Unidades Ejecutoras', 'Registrar nuevas unidades ejecutoras'],
            'unidades.editar'                 => ['Unidades Ejecutoras', 'Editar unidades ejecutoras'],
            'unidades.eliminar'               => ['Unidades Ejecutoras', 'Eliminar unidades ejecutoras'],

            // Beneficiarios
            'beneficiarios.ver'               => ['Beneficiarios', 'Ver directorio de beneficiarios'],
            'beneficiarios.crear'             => ['Beneficiarios', 'Registrar nuevos beneficiarios'],
            'beneficiarios.editar'            => ['Beneficiarios', 'Editar datos de beneficiarios'],

            // Ejercicios Fiscales
            'ejercicios.ver'                  => ['Ejercicios Fiscales', 'Ver ejercicios fiscales registrados'],
            'ejercicios.crear'                => ['Ejercicios Fiscales', 'Crear nuevos ejercicios fiscales'],
            'ejercicios.editar'               => ['Ejercicios Fiscales', 'Editar ejercicios fiscales'],
            'ejercicios.cerrar'               => ['Ejercicios Fiscales', 'Cerrar el ejercicio fiscal activo'],

            // Partidas Presupuestarias
            'partidas.ver'                    => ['Partidas Presupuestarias', 'Ver catálogo de partidas'],
            'partidas.crear'                  => ['Partidas Presupuestarias', 'Registrar partidas presupuestarias'],
            'partidas.editar'                 => ['Partidas Presupuestarias', 'Editar partidas presupuestarias'],
            'partidas.eliminar'               => ['Partidas Presupuestarias', 'Eliminar partidas del catálogo'],

            // Proyectos
            'proyectos.ver'                   => ['Proyectos', 'Ver proyectos y su ejecución'],
            'proyectos.crear'                 => ['Proyectos', 'Crear nuevos proyectos'],
            'proyectos.editar'                => ['Proyectos', 'Editar proyectos existentes'],
            'proyectos.eliminar'              => ['Proyectos', 'Eliminar proyectos'],

            // Créditos Presupuestarios
            'creditos.ver'                    => ['Créditos Presupuestarios', 'Ver créditos asignados por partida'],
            'creditos.crear'                  => ['Créditos Presupuestarios', 'Registrar créditos presupuestarios'],
            'creditos.editar'                 => ['Créditos Presupuestarios', 'Editar montos de créditos'],
            'creditos.eliminar'               => ['Créditos Presupuestarios', 'Eliminar créditos presupuestarios'],

            // Compromisos
            'compromisos.ver'                 => ['Compromisos', 'Ver compromisos presupuestarios'],
            'compromisos.crear'               => ['Compromisos', 'Registrar compromisos'],
            'compromisos.aprobar'             => ['Compromisos', 'Aprobar compromisos'],
            'compromisos.anular'              => ['Compromisos', 'Anular compromisos registrados'],

            // Causaciones
            'causaciones.ver'                 => ['Causaciones', 'Ver causaciones registradas'],
            'causaciones.crear'               => ['Causaciones', 'Registrar nuevas causaciones'],
            'causaciones.aprobar'             => ['Causaciones', 'Aprobar causaciones pendientes'],
            'causaciones.anular'              => ['Causaciones', 'Anular causaciones'],

            // Pagos
            'pagos.ver'                       => ['Pagos', 'Ver pagos registrados'],
            'pagos.crear'                     => ['Pagos', 'Registrar nuevos pagos'],
            'pagos.procesar'                  => ['Pagos', 'Procesar y ejecutar pagos aprobados'],
            'pagos.anular'                    => ['Pagos', 'Anular pagos realizados'],

            // Reportes de Presupuesto
            'presupuesto.reportes'            => ['Reportes', 'Ver reportes de ejecución presupuestaria'],

            // Cuentas Bancarias
            'tesoreria.cuentas.ver'           => ['Cuentas Bancarias', 'Ver cuentas bancarias registradas'],
            'tesoreria.cuentas.crear'         => ['Cuentas Bancarias', 'Registrar nuevas cuentas bancarias'],
            'tesoreria.cuentas.editar'        => ['Cuentas Bancarias', 'Editar y eliminar cuentas bancarias'],

            // Movimientos Bancarios
            'tesoreria.movimientos.ver'       => ['Movimientos Bancarios', 'Ver movimientos bancarios registrados'],
            'tesoreria.movimientos.crear'     => ['Movimientos Bancarios', 'Registrar movimientos bancarios'],
            'tesoreria.movimientos.anular'    => ['Movimientos Bancarios', 'Anular movimientos bancarios'],

            // Conciliación Bancaria
            'tesoreria.conciliacion.ver'      => ['Conciliación Bancaria', 'Ver conciliaciones bancarias'],
            'tesoreria.conciliacion.crear'    => ['Conciliación Bancaria', 'Registrar nuevas conciliaciones'],
            'tesoreria.conciliacion.aprobar'  => ['Conciliación Bancaria', 'Aprobar conciliaciones bancarias'],

            // Órdenes de Pago
            'tesoreria.ordenes.ver'           => ['Órdenes de Pago', 'Ver órdenes de pago emitidas'],
            'tesoreria.ordenes.crear'         => ['Órdenes de Pago', 'Crear órdenes de pago'],
            'tesoreria.ordenes.aprobar'       => ['Órdenes de Pago', 'Revisar y aprobar órdenes de pago'],
            'tesoreria.ordenes.pagar'         => ['Órdenes de Pago', 'Marcar órdenes de pago como pagadas'],

            // Plan de Cuentas Contables
            'contabilidad.cuentas.ver'        => ['Plan de Cuentas', 'Ver el catálogo de cuentas contables'],
            'contabilidad.cuentas.crear'      => ['Plan de Cuentas', 'Crear nuevas cuentas contables'],
            'contabilidad.cuentas.editar'     => ['Plan de Cuentas', 'Editar y eliminar cuentas contables'],

            // Períodos Contables
            'contabilidad.periodos.ver'       => ['Períodos Contables', 'Ver períodos contables registrados'],
            'contabilidad.periodos.crear'     => ['Períodos Contables', 'Generar períodos contables anuales'],
            'contabilidad.periodos.cerrar'    => ['Períodos Contables', 'Cerrar y reabrir períodos contables'],

            // Asientos Contables
            'contabilidad.asientos.ver'       => ['Asientos Contables', 'Ver libro diario de asientos'],
            'contabilidad.asientos.crear'     => ['Asientos Contables', 'Crear asientos contables'],
            'contabilidad.asientos.aprobar'   => ['Asientos Contables', 'Registrar y aprobar asientos en el libro'],
            'contabilidad.asientos.anular'    => ['Asientos Contables', 'Anular asientos contables'],

            // Reportes Contables
            'contabilidad.reportes'           => ['Reportes', 'Ver libro mayor, balance general y estado de resultados'],

            // Almacenes
            'compras.almacenes.ver'           => ['Almacenes', 'Ver almacenes registrados'],
            'compras.almacenes.crear'         => ['Almacenes', 'Registrar nuevos almacenes'],
            'compras.almacenes.editar'        => ['Almacenes', 'Editar información de almacenes'],

            // Artículos e Inventario
            'compras.articulos.ver'           => ['Artículos e Inventario', 'Ver catálogo de artículos y stock disponible'],
            'compras.articulos.crear'         => ['Artículos e Inventario', 'Registrar nuevos artículos en el catálogo'],
            'compras.articulos.editar'        => ['Artículos e Inventario', 'Editar artículos y realizar ajustes de inventario'],

            // Solicitudes de Compra
            'compras.solicitudes.ver'         => ['Solicitudes de Compra', 'Ver solicitudes de compra registradas'],
            'compras.solicitudes.crear'       => ['Solicitudes de Compra', 'Crear solicitudes de compra'],
            'compras.solicitudes.aprobar'     => ['Solicitudes de Compra', 'Aprobar o rechazar solicitudes de compra'],

            // Órdenes de Compra
            'compras.ordenes.ver'             => ['Órdenes de Compra', 'Ver órdenes de compra emitidas'],
            'compras.ordenes.crear'           => ['Órdenes de Compra', 'Emitir órdenes de compra a proveedores'],
            'compras.ordenes.aprobar'         => ['Órdenes de Compra', 'Cambiar estado y gestionar órdenes de compra'],

            // Recepciones
            'compras.recepciones.ver'         => ['Recepciones de Bienes', 'Ver recepciones y entradas de almacén'],
            'compras.recepciones.crear'       => ['Recepciones de Bienes', 'Registrar entrada de bienes comprados'],

            // Bienes Nacionales
            'bienes.ver'                      => ['Inventario de Bienes', 'Ver inventario de bienes nacionales'],
            'bienes.crear'                    => ['Inventario de Bienes', 'Registrar nuevos bienes nacionales'],
            'bienes.editar'                   => ['Inventario de Bienes', 'Editar datos de bienes nacionales'],
            'bienes.baja'                     => ['Inventario de Bienes', 'Dar de baja bienes nacionales'],
            'bienes.trasladar'                => ['Inventario de Bienes', 'Trasladar bienes entre unidades ejecutoras'],
            'bienes.categorias.ver'           => ['Categorías de Bienes', 'Ver categorías de bienes nacionales'],
            'bienes.categorias.crear'         => ['Categorías de Bienes', 'Crear y editar categorías de bienes'],

            // Nóminas
            'nomina.ver'                      => ['Nóminas', 'Ver nóminas generadas y su estado'],
            'nomina.crear'                    => ['Nóminas', 'Crear y calcular nuevas nóminas'],
            'nomina.aprobar'                  => ['Nóminas', 'Aprobar nóminas para su pago'],
            'nomina.pagar'                    => ['Nóminas', 'Marcar nóminas como pagadas'],
            'nomina.anular'                   => ['Nóminas', 'Anular nóminas registradas'],
            'nomina.nominas.ver'              => ['Nóminas', 'Ver nóminas generadas y su estado'],
            'nomina.nominas.crear'            => ['Nóminas', 'Crear y calcular nuevas nóminas'],
            'nomina.nominas.aprobar'          => ['Nóminas', 'Aprobar nóminas para su pago'],
            'nomina.nominas.pagar'            => ['Nóminas', 'Marcar nóminas como pagadas'],

            // Empleados
            'nomina.empleados.ver'            => ['Empleados', 'Ver ficha y datos del personal'],
            'nomina.empleados.crear'          => ['Empleados', 'Registrar nuevos empleados'],
            'nomina.empleados.editar'         => ['Empleados', 'Editar datos personales y laborales de empleados'],

            // Cargos
            'nomina.cargos.ver'               => ['Cargos y Puestos', 'Ver cargos disponibles en la organización'],
            'nomina.cargos.crear'             => ['Cargos y Puestos', 'Crear y editar cargos organizacionales'],

            // Conceptos de Nómina
            'nomina.conceptos.ver'            => ['Conceptos de Nómina', 'Ver conceptos de devengados y deducciones'],
            'nomina.conceptos.crear'          => ['Conceptos de Nómina', 'Crear conceptos de nómina'],
            'nomina.conceptos.editar'         => ['Conceptos de Nómina', 'Editar y eliminar conceptos de nómina'],

            // Retenciones
            'retenciones.ver'                 => ['Retenciones',        'Ver catálogo de retenciones registradas'],
            'retenciones.crear'               => ['Retenciones',        'Registrar nuevas retenciones fiscales'],
            'retenciones.editar'              => ['Retenciones',        'Editar retenciones existentes'],

            // Modificaciones presupuestarias
            'modificaciones.ver'              => ['Modificaciones',     'Ver modificaciones presupuestarias'],
            'modificaciones.crear'            => ['Modificaciones',     'Crear modificaciones presupuestarias'],
            'modificaciones.aprobar'          => ['Modificaciones',     'Aprobar modificaciones presupuestarias'],
            'modificaciones.anular'           => ['Modificaciones',     'Anular modificaciones presupuestarias'],

            // Almacén — entregas a oficinas
            'almacen.solicitudes.ver'         => ['Entregas a Oficinas','Ver solicitudes de entrega de artículos'],
            'almacen.solicitudes.crear'       => ['Entregas a Oficinas','Crear solicitudes de entrega'],
            'almacen.solicitudes.aprobar'     => ['Entregas a Oficinas','Aprobar y registrar entrega de artículos'],

            // Bienes adicionales
            'bienes.baja'                     => ['Inventario de Bienes','Dar de baja bienes nacionales'],
            'bienes.trasladar'                => ['Inventario de Bienes','Trasladar bienes entre unidades ejecutoras'],
            'bienes.categorias.ver'           => ['Categorías de Bienes','Ver categorías de bienes nacionales'],
            'bienes.categorias.crear'         => ['Categorías de Bienes','Crear y editar categorías de bienes'],

            // Nómina adicionales
            'nomina.nominas.ver'              => ['Nóminas',            'Ver nóminas generadas y su estado'],
            'nomina.nominas.crear'            => ['Nóminas',            'Crear y calcular nuevas nóminas'],
            'nomina.nominas.aprobar'          => ['Nóminas',            'Aprobar nóminas para su pago'],
            'nomina.nominas.pagar'            => ['Nóminas',            'Marcar nóminas como pagadas'],
            'nomina.cargos.ver'               => ['Cargos y Puestos',  'Ver cargos disponibles en la organización'],
            'nomina.cargos.crear'             => ['Cargos y Puestos',  'Crear y editar cargos organizacionales'],

            // Ingresos adicionales
            'ingresos.ver'                    => ['Recibos de Ingreso', 'Ver recibos de cobro registrados'],
            'ingresos.caja.ver'               => ['Cajas Recaudadoras', 'Ver cajas recaudadoras y sus arqueos'],
            'ingresos.caja.crear'             => ['Cajas Recaudadoras', 'Registrar nuevas cajas recaudadoras'],
            'ingresos.caja.editar'            => ['Cajas Recaudadoras', 'Abrir, cerrar y editar cajas recaudadoras'],
            'ingresos.conceptos.ver'          => ['Conceptos de Cobro', 'Ver tipos y tarifas de cobro disponibles'],
            'ingresos.conceptos.crear'        => ['Conceptos de Cobro', 'Crear y editar conceptos de ingreso'],
            'ingresos.arqueos.ver'            => ['Arqueos de Caja',    'Ver arqueos de caja realizados'],
            'ingresos.arqueos.aprobar'        => ['Arqueos de Caja',    'Revisar y aprobar arqueos de caja'],

            // Créditos
            'creditos.eliminar'               => ['Créditos Presupuestarios', 'Eliminar créditos presupuestarios'],
        ];
    }

    /** Módulos con ícono y nombre amigable */
    public static function modulos(): array
    {
        return [
            'sistema'      => ['nombre' => 'Administración del Sistema', 'icono' => 'fa-gear',          'color' => '#7c5cfc'],
            'presupuesto'  => ['nombre' => 'Presupuesto',                'icono' => 'fa-chart-bar',     'color' => '#4f8ef7'],
            'tesoreria'    => ['nombre' => 'Tesorería',                  'icono' => 'fa-piggy-bank',    'color' => '#22d3a6'],
            'contabilidad' => ['nombre' => 'Contabilidad',               'icono' => 'fa-calculator',    'color' => '#f7b94f'],
            'compras'      => ['nombre' => 'Compras y Almacén',          'icono' => 'fa-cart-shopping', 'color' => '#f97316'],
            'bienes'       => ['nombre' => 'Bienes Nacionales',          'icono' => 'fa-box-archive',   'color' => '#a78bfa'],
            'nomina'       => ['nombre' => 'Nómina y Personal',          'icono' => 'fa-id-badge',      'color' => '#34d399'],
            'ingresos'     => ['nombre' => 'Control de Ingresos',        'icono' => 'fa-cash-register', 'color' => '#fb7185'],
        ];
    }

    /** Agrupa permisos de la BD por módulo semántico */
    private function agruparPermisos($permisos): array
    {
        $etiquetas = self::etiquetas();

        $mapaModulo = [
            'dashboard'     => 'sistema',  'usuarios'      => 'sistema',
            'roles'         => 'sistema',  'unidades'      => 'sistema',
            'beneficiarios' => 'sistema',
            'ejercicios'    => 'presupuesto', 'partidas'    => 'presupuesto',
            'proyectos'     => 'presupuesto', 'creditos'    => 'presupuesto',
            'compromisos'   => 'presupuesto', 'causaciones' => 'presupuesto',
            'modificaciones'=> 'presupuesto',
            'pagos'         => 'presupuesto',
            'presupuesto'   => 'presupuesto',
            'retenciones'   => 'presupuesto',
            'tesoreria'     => 'tesoreria',
            'contabilidad'  => 'contabilidad',
            'compras'       => 'compras',
            'almacen'       => 'compras',
            'bienes'        => 'bienes',
            'nomina'        => 'nomina',
            'ingresos'      => 'ingresos',
        ];

        $grupos = [];
        foreach ($permisos as $perm) {
            $prefijo = explode('.', $perm->name)[0];
            $modulo  = $mapaModulo[$prefijo] ?? 'sistema';
            $label   = $etiquetas[$perm->name] ?? ['Otros permisos', ucwords(str_replace(['.', '-', '_'], ' ', $perm->name))];
            $grupos[$modulo][] = [
                'id'          => $perm->id,
                'name'        => $perm->name,
                'categoria'   => $label[0],
                'descripcion' => $label[1],
            ];
        }
        return $grupos;
    }

    // ── ROLES ────────────────────────────────────────────────────────

    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function createRol()
    {
        $permisos = Permission::orderBy('name')->get();
        $grupos   = $this->agruparPermisos($permisos);
        $modulos  = self::modulos();
        return view('admin.roles.create', compact('grupos', 'modulos'));
    }

    public function storeRol(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:60|unique:roles,name',
            'icono'       => 'nullable|string|max:60',
            'color'       => 'nullable|string|max:20',
            'descripcion' => 'nullable|string|max:200',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permissions,id',
        ]);

        $rol = Role::create([
            'name'        => $request->name,
            'guard_name'  => 'web',
            'icono'       => $request->icono       ?: 'fa-user-shield',
            'color'       => $request->color       ?: '#4f8ef7',
            'descripcion' => $request->descripcion ?: null,
        ]);
        if ($request->filled('permisos')) {
            $rol->syncPermissions($request->permisos);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol \"{$rol->name}\" creado correctamente.");
    }

    public function editRol(Role $rol)
    {
        $permisos          = Permission::orderBy('name')->get();
        $grupos            = $this->agruparPermisos($permisos);
        $modulos           = self::modulos();
        $permisosAsignados = $rol->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('rol', 'grupos', 'modulos', 'permisosAsignados'));
    }

    public function updateRol(Request $request, Role $rol)
    {
        $request->validate([
            'name'        => "required|string|max:60|unique:roles,name,{$rol->id}",
            'icono'       => 'nullable|string|max:60',
            'color'       => 'nullable|string|max:20',
            'descripcion' => 'nullable|string|max:200',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permissions,id',
        ]);

        if ($rol->name !== 'super-admin') {
            $rol->update([
                'name'        => $request->name,
                'icono'       => $request->icono       ?: $rol->icono,
                'color'       => $request->color       ?: $rol->color,
                'descripcion' => $request->descripcion,
            ]);
        }
        $rol->syncPermissions($request->permisos ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol \"{$rol->name}\" actualizado correctamente.");
    }

    public function destroyRol(Role $rol)
    {
        if ($rol->users()->count() > 0) {
            return back()->with('error', "No se puede eliminar: el rol tiene {$rol->users()->count()} usuario(s) asignado(s).");
        }
        if (in_array($rol->name, ['super-admin', 'administrador'])) {
            return back()->with('error', "El rol \"{$rol->name}\" es un rol del sistema y no puede eliminarse.");
        }
        $rol->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
    }

    // ── PERMISOS ─────────────────────────────────────────────────────

    public function permisoIndex()
    {
        $this->authorize('roles.gestionar');
        $permisos = Permission::withCount('roles')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);
        return view('admin.roles.permisos', compact('permisos'));
    }

    public function permisoCreate()
    {
        $this->authorize('roles.gestionar');
        return view('admin.roles.permiso-create');
    }

    public function permisoStore(Request $request)
    {
        $this->authorize('roles.gestionar');
        $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9._-]+$/', 'unique:permissions,name'],
        ], [
            'name.regex'  => 'Solo letras minúsculas, números, puntos y guiones.',
            'name.unique' => 'Ya existe un permiso con ese nombre.',
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso \"{$request->name}\" creado correctamente.");
    }

    public function permisoEdit(Permission $permiso)
    {
        $this->authorize('roles.gestionar');
        return view('admin.roles.permiso-edit', compact('permiso'));
    }

    public function permisoUpdate(Request $request, Permission $permiso)
    {
        $this->authorize('roles.gestionar');
        $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9._-]+$/', "unique:permissions,name,{$permiso->id}"],
        ], [
            'name.regex'  => 'Solo letras minúsculas, números, puntos y guiones.',
            'name.unique' => 'Ya existe un permiso con ese nombre.',
        ]);

        $old = $permiso->name;
        $permiso->update(['name' => $request->name]);

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso renombrado de \"{$old}\" a \"{$request->name}\".");
    }

    public function permisoDestroy(Permission $permiso)
    {
        $this->authorize('roles.gestionar');

        if ($permiso->roles()->count() > 0) {
            return back()->with('error', "No se puede eliminar: el permiso está asignado a {$permiso->roles()->count()} rol(es).");
        }

        $name = $permiso->name;
        $permiso->delete();

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso \"{$name}\" eliminado correctamente.");
    }
}
