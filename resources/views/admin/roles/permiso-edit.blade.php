@extends('layouts.app')
@section('title', 'Editar Permiso')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-pen"></i> Editar Permiso</h1>
        <p class="page-subtitle">Renombra el permiso <code>{{ $permiso->name }}</code></p>
    </div>
    <a href="{{ route('admin.permisos.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width:560px">
    @if($permiso->roles()->count() > 0)
    <div class="alert" style="background:rgba(247,185,79,.07);border:1px solid rgba(247,185,79,.25);border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem">
        <i class="fa-solid fa-triangle-exclamation" style="color:#f7b94f"></i>
        <span style="font-size:.83rem;color:#f7b94f"> Este permiso está asignado a <strong>{{ $permiso->roles()->count() }}</strong> rol(es):
        {{ $permiso->roles->pluck('name')->join(', ') }}. Al renombrarlo se actualizará en todos ellos.</span>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.permisos.update', $permiso) }}">
        @csrf @method('PUT')
        <div class="form-group" style="margin-bottom:1.5rem">
            <label class="form-label">Nombre del Permiso <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $permiso->name) }}"
                   style="font-family:monospace" autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text">Solo letras minúsculas, números, puntos y guiones.</small>
        </div>

        <div style="display:flex;gap:1rem">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            <a href="{{ route('admin.permisos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
