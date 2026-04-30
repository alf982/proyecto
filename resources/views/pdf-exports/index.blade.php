@extends('layouts.app')

@section('title', 'Mis Exportaciones PDF')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                Mis Exportaciones PDF
            </h1>
            <p class="text-muted small mb-0">Archivos generados en segundo plano. Recarga la página para ver nuevos archivos.</p>
        </div>
        <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </button>
    </div>

    {{-- Alerta informativa cuando se acaba de despachar un Job --}}
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-hourglass-split fs-5"></i>
            <span>{{ session('info') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($archivos->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-3">No tienes archivos PDF generados.</p>
                <p class="text-muted small">Cuando solicites un reporte o inventario, aparecerá aquí automáticamente.</p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Archivo</th>
                                <th>Tamaño</th>
                                <th>Generado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivos as $archivo)
                            <tr>
                                <td class="ps-3">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                                    {{ $archivo['nombre'] }}
                                </td>
                                <td class="text-muted small">
                                    {{ number_format($archivo['tamano'] / 1024, 1) }} KB
                                </td>
                                <td class="text-muted small">
                                    {{ \Carbon\Carbon::createFromTimestamp($archivo['fecha'])->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ $archivo['url'] }}"
                                       class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-download me-1"></i> Descargar
                                    </a>
                                    <form action="{{ route('pdf-exports.destroy', $archivo['nombre']) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este archivo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Auto-refresh si hay un job en progreso (sesión info activa) --}}
        @if(session('info'))
        <script>
            // Refresca la página cada 5 segundos para detectar el PDF generado
            setTimeout(() => location.reload(), 5000);
        </script>
        @endif
    @endif
</div>
@endsection
