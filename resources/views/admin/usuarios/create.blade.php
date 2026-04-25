@extends('layouts.app')
@section('title', 'Nuevo Usuario')
@section('breadcrumb')
    <a href="{{ route('admin.usuarios.index') }}" style="color:var(--text-secondary);text-decoration:none;">Usuarios</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Nuevo Usuario</h1>
        <p class="page-subtitle">Completa los datos para registrar un nuevo usuario</p>
    </div>
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card fade-up" style="animation-delay:.05s">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-user-plus" style="color:var(--accent);margin-right:8px;"></i>Datos del Usuario</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.usuarios.store') }}">
            @csrf
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="name">Nombre completo *</label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name') }}" required
                        placeholder="Ej: Juan Pérez García"
                        oninput="this.value = capitalizarNombre(this.value)">
                    @error('name')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="cedula_numero">Cédula</label>
                    <div style="display:flex;gap:0;">
                        <select id="cedula_prefijo" name="cedula_prefijo"
                            style="background:rgba(255,255,255,0.06);border:1px solid var(--border);border-right:none;border-radius:9px 0 0 9px;padding:10px 12px;font-size:14px;color:var(--text-primary);font-family:inherit;cursor:pointer;min-width:68px;">
                            @php $prefijo = old('cedula_prefijo', 'V'); @endphp
                            <option value="V" {{ $prefijo === 'V' ? 'selected' : '' }}>V-</option>
                            <option value="E" {{ $prefijo === 'E' ? 'selected' : '' }}>E-</option>
                            <option value="J" {{ $prefijo === 'J' ? 'selected' : '' }}>J-</option>
                            <option value="G" {{ $prefijo === 'G' ? 'selected' : '' }}>G-</option>
                        </select>
                        <input type="text" id="cedula_numero" name="cedula_numero"
                            class="form-control" style="border-radius:0 9px 9px 0;"
                            value="{{ old('cedula_numero', ltrim(ltrim(old('cedula'), 'VEJGvejg'), '-')) }}"
                            placeholder="12345678"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    @error('cedula_numero')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="usuario@contraloria.gob.ve">
                    @error('email')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej: 0416-1234567">
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Mínimo 8 caracteres">
                    @error('password')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar contraseña *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Repite la contraseña">
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="rol">Rol *</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="">Seleccionar rol...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->name }}" {{ old('rol') == $rol->name ? 'selected' : '' }}>{{ $rol->name }}</option>
                        @endforeach
                    </select>
                    @error('rol')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="unidad_ejecutora_id">Unidad Ejecutora</label>
                    <select id="unidad_ejecutora_id" name="unidad_ejecutora_id" class="form-control">
                        <option value="">Sin unidad asignada</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}" {{ old('unidad_ejecutora_id') == $unidad->id ? 'selected' : '' }}>
                                [{{ $unidad->codigo }}] {{ $unidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--border);">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;">
                    <input type="checkbox" name="activo" value="1" checked style="accent-color:var(--accent);width:16px;height:16px;">
                    <span>Usuario activo</span>
                </label>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Usuario</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
