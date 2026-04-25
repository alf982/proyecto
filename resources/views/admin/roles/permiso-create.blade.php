@extends('layouts.app')
@section('title', 'Nuevo Permiso')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-key"></i> Nuevo Permiso</h1>
        <p class="page-subtitle">Define un permiso que luego podrás asignar a uno o más roles</p>
    </div>
    <a href="{{ route('admin.permisos.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('admin.permisos.store') }}">
        @csrf
        <div class="form-group" style="margin-bottom:1rem">
            <label class="form-label">Nombre del Permiso <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="modulo.accion"
                   style="font-family:monospace" autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="alert" style="background:rgba(79,142,247,.07);border:1px solid rgba(79,142,247,.2);border-radius:10px;padding:1rem;margin-bottom:1.5rem">
            <p style="font-size:.83rem;color:var(--text-secondary);margin-bottom:.5rem"><strong style="color:var(--text-primary)"><i class="fa-solid fa-circle-info"></i> Convención de nombres</strong></p>
            <p style="font-size:.82rem;color:var(--text-secondary)">Usa la estructura <code>modulo.accion</code> o <code>modulo.sub.accion</code>. Solo letras minúsculas, números, puntos y guiones.</p>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.75rem">
                @foreach(['compras.aprobar','nomina.ver','bienes.trasladar','nomina.aprobar'] as $ej)
                <code style="font-size:.75rem;background:rgba(255,255,255,.05);padding:.2rem .5rem;border-radius:5px;cursor:pointer"
                      onclick="document.querySelector('[name=name]').value='{{ $ej }}'">{{ $ej }}</code>
                @endforeach
            </div>
        </div>

        <div style="display:flex;gap:1rem">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Crear Permiso</button>
            <a href="{{ route('admin.permisos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
