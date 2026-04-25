@extends('layouts.app')
@section('title', 'Editar Unidad Ejecutora')
@section('breadcrumb')
    <a href="{{ route('admin.unidades.index') }}" style="color:var(--text-secondary);text-decoration:none;">Unidades</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Unidad Ejecutora</h1>
        <p class="page-subtitle">Modificar datos de <strong>{{ $unidad->nombre }}</strong></p>
    </div>
    <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card fade-up" style="animation-delay:.05s;max-width:600px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-sitemap" style="color:var(--accent-3);margin-right:8px;"></i>
            Datos de la Unidad
        </div>
        <code style="background:rgba(34,211,166,0.1);color:var(--accent-3);padding:3px 10px;border-radius:5px;font-size:13px;font-weight:600;">
            {{ $unidad->codigo }}
        </code>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.unidades.update', $unidad) }}">
            @csrf
            @method('PUT')

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="codigo">Código *</label>
                    <input type="text" id="codigo" name="codigo" class="form-control"
                        value="{{ old('codigo', $unidad->codigo) }}" required>
                    @error('codigo')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="parent_id">Unidad Superior</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">Sin unidad superior (raíz)</option>
                        @foreach($padres as $padre)
                            <option value="{{ $padre->id }}"
                                {{ old('parent_id', $unidad->parent_id) == $padre->id ? 'selected' : '' }}>
                                [{{ $padre->codigo }}] {{ $padre->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                    value="{{ old('nombre', $unidad->nombre) }}" required>
                @error('nombre')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion', $unidad->descripcion) }}</textarea>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--border);">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;">
                    <input type="checkbox" name="activo" value="1"
                        {{ old('activo', $unidad->activo) ? 'checked' : '' }}
                        style="accent-color:var(--accent);width:16px;height:16px;">
                    <span>Unidad activa</span>
                </label>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
