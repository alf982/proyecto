<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\UnidadEjecutoraController;
use App\Http\Controllers\Admin\BeneficiarioController;
use App\Http\Controllers\Presupuesto\EjercicioFiscalController;
use App\Http\Controllers\Presupuesto\PartidaController;
use App\Http\Controllers\Presupuesto\ProyectoController;

use App\Http\Controllers\Presupuesto\CausacionController;
use App\Http\Controllers\Presupuesto\CompromisosController;
use App\Http\Controllers\Presupuesto\PagoController;
use App\Http\Controllers\Presupuesto\ReporteController;
use App\Http\Controllers\Presupuesto\MovimientoPartidaController;
use App\Http\Controllers\Tesoreria\CuentaBancariaController;
use App\Http\Controllers\Compras\AlmacenController;
use App\Http\Controllers\Compras\ArticuloController;
use App\Http\Controllers\Compras\SolicitudController;
use App\Http\Controllers\Compras\OrdenCompraController;
use App\Http\Controllers\Compras\RecepcionController;
use App\Http\Controllers\Tesoreria\OrdenPagoController;
use App\Http\Controllers\Almacen\SolicitudDespachoController;
use App\Http\Controllers\Bienes\CategoriaBienController;
use App\Http\Controllers\Bienes\BienController;
use App\Http\Controllers\Nomina\CargoController;
use App\Http\Controllers\Nomina\EmpleadoController;
use App\Http\Controllers\Nomina\ConceptoNominaController;
use App\Http\Controllers\Nomina\NominaController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ServerStatsController;
use App\Http\Controllers\Retenciones\RetencionController;
use App\Http\Controllers\Retenciones\RetencionCalculoController;
use Illuminate\Support\Facades\Route;

// Raíz → login
Route::get('/', fn() => redirect()->route('login'));

// Dashboard
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/api/server-stats', ServerStatsController::class)->name('server.stats');

    // ── Selector de Ejercicio Fiscal ────────────────────────────
    Route::post('/ejercicio/seleccionar', function (\Illuminate\Http\Request $request) {
        $id = $request->validate(['ejercicio_id' => 'required|exists:ejercicios_fiscales,id'])['ejercicio_id'];
        session(['ejercicio_id' => (int)$id]);
        return redirect()->back()->with('success', 'Ejercicio fiscal cambiado correctamente.');
    })->name('ejercicio.seleccionar');

    Route::prefix('admin')->name('admin.')->group(function () {

        Route::resource('usuarios', UsuarioController::class)
            ->parameters(['usuarios' => 'usuario']);

        Route::resource('unidades', UnidadEjecutoraController::class)
            ->parameters(['unidades' => 'unidad']);

        // Módulo 3 — Beneficiarios (Ordenamiento de Pago)
        Route::resource('beneficiarios', BeneficiarioController::class)
            ->parameters(['beneficiarios' => 'beneficiario']);
        Route::post('beneficiarios/{beneficiario}/toggle', [BeneficiarioController::class, 'toggleActivo'])
            ->name('beneficiarios.toggle');

        // Auditoría del Sistema
        Route::get('auditoria',         [AuditController::class, 'index'])->name('auditoria.index');
        Route::get('auditoria/{audit}',  [AuditController::class, 'show'])->name('auditoria.show');

        // Roles y Permisos
        Route::get('roles',                       [RolController::class, 'index'])->name('roles.index');
        Route::get('roles/crear',                 [RolController::class, 'createRol'])->name('roles.create');
        Route::post('roles',                      [RolController::class, 'storeRol'])->name('roles.store');
        Route::get('roles/{rol}/editar',          [RolController::class, 'editRol'])->name('roles.edit');
        Route::put('roles/{rol}',                 [RolController::class, 'updateRol'])->name('roles.update');
        Route::delete('roles/{rol}',              [RolController::class, 'destroyRol'])->name('roles.destroy');

        // Permisos del Sistema
        Route::get('permisos',                    [RolController::class, 'permisoIndex'])->name('permisos.index');
        Route::get('permisos/crear',              [RolController::class, 'permisoCreate'])->name('permisos.create');
        Route::post('permisos',                   [RolController::class, 'permisoStore'])->name('permisos.store');
        Route::get('permisos/{permiso}/editar',   [RolController::class, 'permisoEdit'])->name('permisos.edit');
        Route::put('permisos/{permiso}',          [RolController::class, 'permisoUpdate'])->name('permisos.update');
        Route::delete('permisos/{permiso}',       [RolController::class, 'permisoDestroy'])->name('permisos.destroy');
    });


    // ── Presupuesto ────────────────────────────────────────────
    Route::prefix('presupuesto')->name('presupuesto.')->group(function () {

        // Ejercicios Fiscales
        Route::resource('ejercicios', EjercicioFiscalController::class);
        Route::post('ejercicios/{ejercicio}/activar', [EjercicioFiscalController::class, 'activar'])
            ->name('ejercicios.activar');
        Route::post('ejercicios/{ejercicio}/cerrar', [EjercicioFiscalController::class, 'cerrar'])
            ->name('ejercicios.cerrar');

        // Catálogo de Partidas
        Route::resource('partidas', PartidaController::class);

        // Proyectos
        Route::resource('proyectos', ProyectoController::class);


        // Créditos Presupuestarios eliminados — la asignación inicial
        // se realiza directamente desde Movimientos de Partida (tipo: asignacion)


        // Causaciones / Órdenes de Pago
        Route::resource('causaciones', CausacionController::class)
            ->parameters(['causaciones' => 'causacion']);
        Route::post('causaciones/{causacion}/aprobar', [CausacionController::class, 'aprobar'])
            ->name('causaciones.aprobar');
        Route::post('causaciones/{causacion}/pagar', [CausacionController::class, 'pagar'])
            ->name('causaciones.pagar');
        Route::post('causaciones/{causacion}/anular', [CausacionController::class, 'anular'])
            ->name('causaciones.anular');

        // Compromisos Presupuestarios
        Route::resource('compromisos', CompromisosController::class)
            ->parameters(['compromisos' => 'compromiso']);
        Route::post('compromisos/{compromiso}/aprobar', [CompromisosController::class, 'aprobar'])
            ->name('compromisos.aprobar');
        Route::post('compromisos/{compromiso}/anular', [CompromisosController::class, 'anular'])
            ->name('compromisos.anular');

        // Pagos
        Route::resource('pagos', PagoController::class)->only(['index','create','store','show','edit','update']);
        Route::post('pagos/{pago}/procesar', [PagoController::class, 'procesar'])
            ->name('pagos.procesar');
        Route::post('pagos/{pago}/anular', [PagoController::class, 'anular'])
            ->name('pagos.anular');

        // Reportes de Ejecución
        Route::get('reportes/ejecucion', [ReporteController::class, 'ejecucion'])
            ->name('reportes.ejecucion');

        // Movimientos de Partidas Presupuestarias
        Route::resource('movimientos-partidas', MovimientoPartidaController::class)
            ->parameters(['movimientos-partidas' => 'movimiento'])
            ->only(['index', 'create', 'store', 'show']);
        Route::post('movimientos-partidas/{movimiento}/anular', [MovimientoPartidaController::class, 'anular'])
            ->name('movimientos-partidas.anular');
    });

    // ── Tesorería (Módulo 4) ───────────────────────────────
    Route::prefix('tesoreria')->name('tesoreria.')->group(function () {

        // Cuentas Bancarias
        Route::resource('cuentas', CuentaBancariaController::class)
            ->parameters(['cuentas' => 'cuenta']);

        // Las Órdenes de Pago fueron eliminadas. El flujo es: Causación → Pago
    });

    // ── Compras, Servicios y Almacén (Módulo 6) ────────────────
    Route::prefix('compras')->name('compras.')->group(function () {

        // Almacenes
        Route::resource('almacenes', AlmacenController::class)
            ->parameters(['almacenes'=>'almacen'])
            ->only(['index','create','store','show','edit','update']);

        // Catálogo de Artículos
        Route::resource('articulos', ArticuloController::class)
            ->parameters(['articulos'=>'articulo'])
            ->only(['index','create','store','show','edit','update']);
        Route::post('articulos/{articulo}/ajustar', [ArticuloController::class,'ajustar'])->name('articulos.ajustar');

        // Solicitudes de Compra
        Route::resource('solicitudes', SolicitudController::class)
            ->parameters(['solicitudes'=>'solicitud'])
            ->only(['index','create','store','show','edit','update']);
        Route::post('solicitudes/{solicitud}/aprobar',  [SolicitudController::class,'aprobar'])->name('solicitudes.aprobar');
        Route::post('solicitudes/{solicitud}/rechazar', [SolicitudController::class,'rechazar'])->name('solicitudes.rechazar');

        // Órdenes de Compra
        Route::resource('ordenes', OrdenCompraController::class)
            ->parameters(['ordenes'=>'orden'])
            ->only(['index','create','store','show','edit','update']);
        Route::post('ordenes/{orden}/cambiar-estado', [OrdenCompraController::class,'cambiarEstado'])->name('ordenes.cambiar-estado');

        // Recepciones de Bienes
        Route::resource('recepciones', RecepcionController::class)
            ->parameters(['recepciones'=>'recepcion'])
            ->only(['index','create','store','show']);
    });

    // ── Almacén ── Solicitudes de Despacho / Entrega a Oficinas ─────────────
    Route::prefix('almacen')->name('almacen.')->group(function () {
        Route::resource('solicitudes', SolicitudDespachoController::class)
            ->parameters(['solicitudes' => 'solicitud'])
            ->only(['index', 'create', 'store', 'show']);
        Route::post('solicitudes/{solicitud}/aprobar',          [SolicitudDespachoController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::post('solicitudes/{solicitud}/rechazar',         [SolicitudDespachoController::class, 'rechazar'])->name('solicitudes.rechazar');
        Route::post('solicitudes/{solicitud}/registrar-entrega',[SolicitudDespachoController::class, 'registrarEntrega'])->name('solicitudes.registrar-entrega');
    });

    // ── Ordenamiento de Pago (Módulo 6) ────────────────────────
    Route::prefix('tesoreria')->name('tesoreria.')->group(function () {
        Route::resource('ordenes', OrdenPagoController::class)
            ->parameters(['ordenes'=>'orden'])
            ->only(['index','create','store','show']);
        Route::post('ordenes/{orden}/revisar',  [OrdenPagoController::class,'revisar'])->name('ordenes.revisar');
        Route::post('ordenes/{orden}/aprobar',  [OrdenPagoController::class,'aprobar'])->name('ordenes.aprobar');
        Route::post('ordenes/{orden}/enviar',   [OrdenPagoController::class,'enviar'])->name('ordenes.enviar');
        Route::post('ordenes/{orden}/pagar',    [OrdenPagoController::class,'pagar'])->name('ordenes.pagar');
        Route::post('ordenes/{orden}/anular',   [OrdenPagoController::class,'anular'])->name('ordenes.anular');
    });

    // ── Bienes Nacionales (Módulo 7) ────────────────────────────
    Route::prefix('bienes')->name('bienes.')->group(function () {
        Route::resource('categorias', CategoriaBienController::class)
            ->parameters(['categorias'=>'categoria'])
            ->only(['index','create','store','edit','update']);
        Route::resource('bienes', BienController::class)
            ->parameters(['bienes'=>'bien'])
            ->only(['index','create','store','show','edit','update']);
        Route::post('bienes/{bien}/baja',     [BienController::class,'darDeBaja'])->name('bienes.baja');
        Route::post('bienes/{bien}/trasladar',[BienController::class,'trasladar'])->name('bienes.trasladar');
    });

    // ── Nómina y Personal (Módulo 8) ────────────────────────────
    Route::prefix('nomina')->name('nomina.')->group(function () {
        Route::resource('cargos',    CargoController::class)->parameters(['cargos'=>'cargo'])->only(['index','create','store','edit','update']);
        Route::resource('empleados', EmpleadoController::class)->parameters(['empleados'=>'empleado']);
        // Curriculum
        Route::get('empleados/{empleado}/curriculum',          [EmpleadoController::class,'downloadCV'])->name('empleados.curriculum');
        // Familiares
        Route::post('empleados/{empleado}/familiares',         [EmpleadoController::class,'addFamiliar'])->name('empleados.familiares.store');
        Route::delete('empleados/{empleado}/familiares/{familiar}', [EmpleadoController::class,'deleteFamiliar'])->name('empleados.familiares.destroy');
        // Historial de cargos
        Route::post('empleados/{empleado}/historial',          [EmpleadoController::class,'addHistorial'])->name('empleados.historial.store');
        Route::delete('empleados/{empleado}/historial/{historial}', [EmpleadoController::class,'deleteHistorial'])->name('empleados.historial.destroy');
        // Formación adicional
        Route::post('empleados/{empleado}/formaciones',              [EmpleadoController::class,'addFormacion'])->name('empleados.formaciones.store');
        Route::delete('empleados/{empleado}/formaciones/{formacion}',[EmpleadoController::class,'deleteFormacion'])->name('empleados.formaciones.destroy');
        Route::get('empleados/{empleado}/formaciones/{formacion}/documento',[EmpleadoController::class,'downloadDocFormacion'])->name('empleados.formaciones.documento');

        Route::resource('conceptos', ConceptoNominaController::class)->parameters(['conceptos'=>'concepto'])->only(['index','create','store','edit','update']);
        Route::resource('nominas',   NominaController::class)->parameters(['nominas'=>'nomina'])->only(['index','create','store','show']);
        Route::post('nominas/{nomina}/aprobar', [NominaController::class,'aprobar'])->name('nominas.aprobar');
        Route::post('nominas/{nomina}/pagar',   [NominaController::class,'pagar'])->name('nominas.pagar');
        Route::post('nominas/{nomina}/anular',  [NominaController::class,'anular'])->name('nominas.anular');
    });

    // ── PDFs ──────────────────────────────────────────────────
    Route::prefix('pdf')->name('pdf.')->group(function () {
        // Presupuesto
        Route::get('causacion/{causacion}',         [PdfController::class, 'causacion'])->name('causacion');
        // Tesorería
        Route::get('orden-pago/{orden}',            [PdfController::class, 'ordenPago'])->name('orden-pago');
        // Nómina
        Route::get('nomina/{nomina}',               [PdfController::class, 'nomina'])->name('nomina');
        // Compras
        Route::get('orden-compra/{orden}',          [PdfController::class, 'ordenCompra'])->name('orden-compra');
        Route::get('recepcion-bienes/{recepcion}',  [PdfController::class, 'recepcionBienes'])->name('recepcion-bienes');
        // Bienes
        Route::get('inventario-bienes',             [PdfController::class, 'inventarioBienes'])->name('inventario-bienes');
    });

    // ── Configuración Fiscal ───────────────────────────────────
    Route::prefix('retenciones')->name('retenciones.')->group(function () {
        Route::get('/',          [RetencionController::class, 'index'])->name('index');
        Route::get('/crear',     [RetencionController::class, 'create'])->name('create');
        Route::post('/',         [RetencionController::class, 'store'])->name('store');
        Route::get('/{retencion}/editar', [RetencionController::class, 'edit'])->name('edit');
        Route::put('/{retencion}',        [RetencionController::class, 'update'])->name('update');
        Route::post('/{retencion}/toggle',[RetencionController::class, 'toggle'])->name('toggle');
        Route::delete('/{retencion}',     [RetencionController::class, 'destroy'])->name('destroy');
    });

    // Endpoint AJAX para cálculo de retenciones
    Route::post('api/retenciones/calcular', [RetencionCalculoController::class, 'calcular'])
        ->name('retenciones.calcular');
});

require __DIR__.'/auth.php';
