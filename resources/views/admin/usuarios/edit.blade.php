@extends('layouts.app')
@section('title', 'Editar Usuario')
@section('breadcrumb')
    <a href="{{ route('admin.usuarios.index') }}" style="color:var(--text-secondary);text-decoration:none;">Usuarios</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Usuario</h1>
        <p class="page-subtitle">Modificar datos de <strong>{{ $usuario->name }}</strong></p>
    </div>
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card fade-up" style="animation-delay:.05s">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-user-pen" style="color:var(--accent);margin-right:8px;"></i>
            Datos del Usuario
        </div>
        <span class="badge {{ $usuario->activo ? 'badge-active' : 'badge-danger' }}">
            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="name">Nombre completo *</label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name', $usuario->name) }}" required
                        placeholder="Ej: Juan Pérez García"
                        oninput="this.value = capitalizarNombre(this.value)">
                    @error('name')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="cedula_numero">Cédula</label>
                    <div style="display:flex;gap:0;">
                        @php
                            $cedulaActual = old('cedula', $usuario->cedula ?? '');
                            $prefijoActual = 'V';
                            $numeroActual  = $cedulaActual;
                            if (preg_match('/^([VEJGvejg])-?(\d+)$/', $cedulaActual, $m)) {
                                $prefijoActual = strtoupper($m[1]);
                                $numeroActual  = $m[2];
                            }
                        @endphp
                        <select id="cedula_prefijo" name="cedula_prefijo"
                            style="background:rgba(255,255,255,0.06);border:1px solid var(--border);border-right:none;border-radius:9px 0 0 9px;padding:10px 12px;font-size:14px;color:var(--text-primary);font-family:inherit;cursor:pointer;min-width:68px;">
                            <option value="V" {{ $prefijoActual === 'V' ? 'selected' : '' }}>V-</option>
                            <option value="E" {{ $prefijoActual === 'E' ? 'selected' : '' }}>E-</option>
                            <option value="J" {{ $prefijoActual === 'J' ? 'selected' : '' }}>J-</option>
                            <option value="G" {{ $prefijoActual === 'G' ? 'selected' : '' }}>G-</option>
                        </select>
                        <input type="text" id="cedula_numero" name="cedula_numero"
                            class="form-control" style="border-radius:0 9px 9px 0;"
                            value="{{ $numeroActual }}"
                            placeholder="12345678"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    @error('cedula_numero')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email', $usuario->email) }}" required
                        placeholder="usuario@contraloria.gob.ve">
                    @error('email')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control"
                        value="{{ old('telefono', $usuario->telefono) }}"
                        placeholder="Ej: 0416-1234567">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="password">
                        Nueva contraseña
                        <span style="font-weight:400;color:var(--text-secondary);"> (dejar vacío para no cambiar)</span>
                    </label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar nueva contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control" placeholder="Repite la contraseña">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="rol">Rol *</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="">Seleccionar rol...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->name }}"
                                {{ old('rol', $usuario->roles->first()?->name) == $rol->name ? 'selected' : '' }}>
                                {{ $rol->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="unidad_ejecutora_id">Unidad Ejecutora</label>
                    <select id="unidad_ejecutora_id" name="unidad_ejecutora_id" class="form-control">
                        <option value="">Sin unidad asignada</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}"
                                {{ old('unidad_ejecutora_id', $usuario->unidad_ejecutora_id) == $unidad->id ? 'selected' : '' }}>
                                [{{ $unidad->codigo }}] {{ $unidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--border);">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;">
                    <input type="checkbox" name="activo" value="1"
                        {{ old('activo', $usuario->activo) ? 'checked' : '' }}
                        style="accent-color:var(--accent);width:16px;height:16px;">
                    <span>Usuario activo</span>
                </label>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
