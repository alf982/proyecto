@extends('layouts.app')
@section('title', 'Editar Proyecto')
@section('breadcrumb')
    <a href="{{ route('presupuesto.proyectos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Proyectos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Proyecto</h1>
        <p class="page-subtitle">Modificar <strong>{{ $proyecto->nombre }}</strong></p>
    </div>
    <a href="{{ route('presupuesto.proyectos.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card fade-up" style="animation-delay:.05s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-diagram-project" style="color:var(--accent-2);margin-right:8px;"></i>
            Datos del Proyecto
        </div>
        @php $cls = match($proyecto->estado) { 'activo'=>'badge-active','terminado'=>'badge-blue','suspendido'=>'badge-danger',default=>'badge-warn' }; @endphp
        <span class="badge {{ $cls }}">{{ ucfirst($proyecto->estado) }}</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.proyectos.update', $proyecto) }}">
            @csrf
            @method('PUT')

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="ejercicio_fiscal_id">Ejercicio Fiscal *</label>
                    <select id="ejercicio_fiscal_id" name="ejercicio_fiscal_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($ejercicios as $ej)
                            <option value="{{ $ej->id }}"
                                {{ old('ejercicio_fiscal_id', $proyecto->ejercicio_fiscal_id) == $ej->id ? 'selected' : '' }}>
                                {{ $ej->anio }}
                            </option>
                        @endforeach
                    </select>
                    @error('ejercicio_fiscal_id')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="unidad_ejecutora_id">Unidad Ejecutora *</label>
                    <select id="unidad_ejecutora_id" name="unidad_ejecutora_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}"
                                {{ old('unidad_ejecutora_id', $proyecto->unidad_ejecutora_id) == $unidad->id ? 'selected' : '' }}>
                                [{{ $unidad->codigo }}] {{ $unidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('unidad_ejecutora_id')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="codigo">Código *</label>
                    <input type="text" id="codigo" name="codigo" class="form-control"
                        value="{{ old('codigo', $proyecto->codigo) }}" required>
                    @error('codigo')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="estado">Estado *</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="formulacion" {{ old('estado', $proyecto->estado) === 'formulacion' ? 'selected' : '' }}>Formulación</option>
                        <option value="activo"      {{ old('estado', $proyecto->estado) === 'activo'      ? 'selected' : '' }}>Activo</option>
                        <option value="suspendido"  {{ old('estado', $proyecto->estado) === 'suspendido'  ? 'selected' : '' }}>Suspendido</option>
                        <option value="terminado"   {{ old('estado', $proyecto->estado) === 'terminado'   ? 'selected' : '' }}>Terminado</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="nombre">Nombre del Proyecto *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                    value="{{ old('nombre', $proyecto->nombre) }}" required>
                @error('nombre')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="objetivo">Objetivo</label>
                <textarea id="objetivo" name="objetivo" class="form-control" rows="2">{{ old('objetivo', $proyecto->objetivo) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion', $proyecto->descripcion) }}</textarea>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                        value="{{ old('fecha_inicio', $proyecto->fecha_inicio?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_fin">Fecha de Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control"
                        value="{{ old('fecha_fin', $proyecto->fecha_fin?->format('Y-m-d')) }}">
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.proyectos.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
