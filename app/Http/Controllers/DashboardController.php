<?php

namespace App\Http\Controllers;

use App\Models\Causacion;
use App\Models\CuentaBancaria;
use App\Models\EjercicioFiscal;
use App\Models\Empleado;
use App\Models\Ingreso;
use App\Models\Nomina;
use App\Models\OrdenCompra;
use App\Models\Pago;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\User;
use App\Services\CatalogoCache;

/**
 * Clase DashboardController
 * 
 * Controlador de invocación única (Single Action Controller).
 * Se encarga de recopilar y enviar todas las métricas principales (KPIs) 
 * que se muestran en la pantalla de inicio del sistema.
 */
class DashboardController extends Controller
{
    /**
     * Reúne los datos estadísticos y renderiza la vista del Dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function __invoke()
    {
        // Optimización: Usamos el servicio de caché en lugar de consultar la BD directamente.
        // Esto evita peticiones repetitivas para un dato que cambia una vez al año.
        $ejercicioActivo = CatalogoCache::ejercicioActivo();

        return view('dashboard', [
            // --- MÉTRICAS BÁSICAS Y ESTRUCTURALES ---
            'totalUsuarios'         => User::where('activo', true)->count(), // Usuarios activos que pueden acceder al sistema
            'totalEjercicios'       => EjercicioFiscal::count(),             // Historial de años fiscales registrados
            'ejercicioActivo'       => $ejercicioActivo,                     // Año fiscal actualmente en curso
            'totalPartidas'         => PartidaPresupuestaria::where('activo', true)->count(), // Códigos presupuestarios habilitados
            'totalProyectos'        => ProyectoSia::count(),                 // Proyectos activos para asignación de presupuesto

            // --- KPIs OPERATIVOS (Indicadores Clave de Rendimiento) ---
            // Presupuesto y Finanzas
            'causacionesPendientes' => Causacion::where('estado', 'borrador')->count(), // Deudas reconocidas pendientes de aprobación
            'causacionesAprobadas'  => Causacion::where('estado', 'aprobada')->count(), // Deudas listas para ser pagadas (Orden de Pago)
            'pagosPendientes'       => Pago::where('estado', 'pendiente')->count(),     // Transferencias o cheques emitidos sin confirmar por el banco
            'saldoBancario'         => CuentaBancaria::where('estado', 'activa')->sum('saldo_actual'), // Liquidez total disponible

            // Compras
            'ordenesPendientes'     => OrdenCompra::whereIn('estado', ['emitida', 'confirmada', 'en_transito'])->count(), // Compras en proceso de entrega

            // Recursos Humanos y Nómina
            'empleadosActivos'      => Empleado::where('estado', 'activo')->count(), // Personal activo laborando
            'nominasPendientes'     => Nomina::whereIn('estado', ['borrador', 'calculada'])->count(), // Nóminas en proceso que aún no se han pagado

            // Recaudación
            'ingresosHoy'           => Ingreso::where('estado', 'registrado')
                                               ->whereDate('fecha', today())->sum('monto'), // Dinero que ha ingresado a la institución el día de hoy

            // --- DATOS PARA TABLAS Y LISTADOS RECIENTES ---
            // Carga ambiciosa (with) de la Unidad Ejecutora para evitar N+1 al mostrar el listado en la pantalla
            'ultimasCausaciones'    => Causacion::with(['unidadEjecutora'])
                                                ->whereIn('estado', ['borrador', 'aprobada'])
                                                ->latest() // Ordena por fecha de creación descendente
                                                ->limit(5) // Solo trae los últimos 5 registros para no saturar la vista
                                                ->get(),
        ]);
    }
}
