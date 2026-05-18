<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OwenIt\Auditing\Models\Audit;

/**
 * Clase AuditController
 * 
 * Gestiona la visualización de los logs de auditoría del sistema.
 * Permite filtrar por modelo, evento, usuario y rango de fechas.
 */
class AuditController extends Controller implements HasMiddleware
{
    /**
     * Define los middlewares aplicables al controlador.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.gestionar'),
        ];
    }

    /** 
     * Mapeo de modelos auditados disponibles para revisión.
     * Facilita el filtrado amigable desde la interfaz.
     */
    private const MODELOS = [
        'Causacion'             => \App\Models\Causacion::class,
        'Compromiso'            => \App\Models\Compromiso::class,
        'CreditoPresupuestario' => \App\Models\CreditoPresupuestario::class,
        'OrdenPago'             => \App\Models\OrdenPago::class,
        'Pago'                  => \App\Models\Pago::class,
        'Nomina'                => \App\Models\Nomina::class,
        'Ingreso'               => \App\Models\Ingreso::class,
        'Bien'                  => \App\Models\Bien::class,
        'User'                  => \App\Models\User::class,
    ];

    /**
     * Muestra el listado de auditorías con filtros aplicados.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Optimización: eager loading de 'user' y 'auditable' para evitar N+1
        $query = Audit::with(['user', 'auditable'])
            ->when($request->modelo, function ($q, $modelo) {
                $q->where('auditable_type', self::MODELOS[$modelo] ?? $modelo);
            })
            ->when($request->evento, fn($q, $e) => $q->where('event', $e))
            ->when($request->usuario, fn($q, $u) => $q->where('user_id', $u))
            ->when($request->fecha_desde, fn($q, $f) => $q->whereDate('created_at', '>=', $f))
            ->when($request->fecha_hasta, fn($q, $f) => $q->whereDate('created_at', '<=', $f))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.auditoria.index', [
            'query'    => $query,
            'modelos'  => array_keys(self::MODELOS),
            'eventos'  => ['created', 'updated', 'deleted', 'restored'],
            'usuarios' => User::orderBy('name')->pluck('name', 'id')
        ]);
    }

    /**
     * Muestra el detalle de una auditoría específica.
     * 
     * @param Audit $audit
     * @return \Illuminate\View\View
     */
    public function show(Audit $audit)
    {
        $audit->load(['user', 'auditable']);
        return view('admin.auditoria.show', compact('audit'));
    }
}
