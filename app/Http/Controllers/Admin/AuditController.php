<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:roles.gestionar'),
        ];
    }
    /** Modelos auditados disponibles para revisar */
    private const MODELOS = [
        'Causacion'                 => \App\Models\Causacion::class,
        'Compromiso'                => \App\Models\Compromiso::class,
        'CreditoPresupuestario'     => \App\Models\CreditoPresupuestario::class,
        'OrdenPago'                 => \App\Models\OrdenPago::class,
        'Pago'                      => \App\Models\Pago::class,
        'Nomina'                    => \App\Models\Nomina::class,
        'Ingreso'                   => \App\Models\Ingreso::class,
        'Bien'                      => \App\Models\Bien::class,
        'User'                      => \App\Models\User::class,
    ];

    public function index(Request $request)
    {
        $query = Audit::with('user')
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

        $modelos  = array_keys(self::MODELOS);
        $eventos  = ['created', 'updated', 'deleted', 'restored'];
        $usuarios = \App\Models\User::orderBy('name')->pluck('name', 'id');

        return view('admin.auditoria.index', compact('query', 'modelos', 'eventos', 'usuarios'));
    }

    public function show(Audit $audit)
    {
        $audit->load('user');
        return view('admin.auditoria.show', compact('audit'));
    }
}
