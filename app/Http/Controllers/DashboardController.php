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

class DashboardController extends Controller
{
    public function __invoke()
    {
        $ejercicioActivo = EjercicioFiscal::where('estado', 'activo')->first();

        return view('dashboard', [
            // Básicos
            'totalUsuarios'         => User::where('activo', true)->count(),
            'totalEjercicios'       => EjercicioFiscal::count(),
            'ejercicioActivo'       => $ejercicioActivo,
            'totalPartidas'         => PartidaPresupuestaria::where('activo', true)->count(),
            'totalProyectos'        => ProyectoSia::count(),

            // KPIs operativos
            'causacionesPendientes' => Causacion::where('estado', 'borrador')->count(),
            'causacionesAprobadas'  => Causacion::where('estado', 'aprobada')->count(),
            'pagosPendientes'       => Pago::where('estado', 'pendiente')->count(),
            'ordenesPendientes'     => OrdenCompra::whereIn('estado', ['emitida', 'confirmada', 'en_transito'])->count(),
            'saldoBancario'         => CuentaBancaria::where('estado', 'activa')->sum('saldo_actual'),
            'empleadosActivos'      => Empleado::where('estado', 'activo')->count(),
            'nominasPendientes'     => Nomina::whereIn('estado', ['borrador', 'calculada'])->count(),
            'ingresosHoy'           => Ingreso::where('estado', 'registrado')
                                               ->whereDate('fecha', today())->sum('monto'),

            // Actividad reciente
            'ultimasCausaciones'    => Causacion::with(['unidadEjecutora'])
                                                ->whereIn('estado', ['borrador', 'aprobada'])
                                                ->latest()->limit(5)->get(),
        ]);
    }
}
