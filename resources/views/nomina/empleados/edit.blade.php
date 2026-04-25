@extends('layouts.app')
@section('title', 'Editar Empleado')
@section('breadcrumb')
    <a href="{{ route('nomina.empleados.show', $empleado) }}">{{ $empleado->nombre_completo }}</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Editar: {{ $empleado->nombre_completo }}</h1>
</div>

<form method="POST" action="{{ route('nomina.empleados.update', $empleado) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>

{{-- ── Identificación ──────────────────────────────────────────── --}}
<div class="card fade-up">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-id-card" style="color:var(--accent);margin-right:8px;"></i>Identificación</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Cédula *</label>
                <input type="text" name="cedula" value="{{ old('cedula', $empleado->cedula) }}" class="form-control" required maxlength="15">
                @error('cedula')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre(s) *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $empleado->nombre) }}" class="form-control" required maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Apellido(s) *</label>
                <input type="text" name="apellido" value="{{ old('apellido', $empleado->apellido) }}" class="form-control" required maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $empleado->telefono) }}" class="form-control" maxlength="20">
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $empleado->email) }}" class="form-control">
            </div>
        </div>
    </div>
</div>

{{-- ── Datos Civiles ───────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-person" style="color:var(--accent-2);margin-right:8px;"></i>Datos Civiles</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Estado Civil</label>
                <select name="estado_civil" class="form-control">
                    <option value="">— Seleccionar —</option>
                    @foreach(App\Models\Empleado::estadosCiviles() as $val => $lbl)
                    <option value="{{ $val }}" {{ old('estado_civil', $empleado->estado_civil) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nacionalidad</label>
                <input type="text" name="nacionalidad" value="{{ old('nacionalidad', $empleado->nacionalidad) }}" class="form-control" maxlength="30" placeholder="Venezolano/a…">
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $empleado->fecha_nacimiento?->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="form-group" style="grid-column:span 3;">
                <label class="form-label">Lugar de Nacimiento</label>
                <input type="text" name="lugar_nacimiento" value="{{ old('lugar_nacimiento', $empleado->lugar_nacimiento) }}" class="form-control" maxlength="150">
            </div>
        </div>
    </div>
</div>

{{-- ── Grado Académico ─────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-graduation-cap" style="color:var(--accent-warn);margin-right:8px;"></i>Grado Académico</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Nivel de Instrucción</label>
                <select name="nivel_instruccion" class="form-control">
                    <option value="">— Seleccionar —</option>
                    @foreach(App\Models\Empleado::nivelesInstruccion() as $val => $lbl)
                    <option value="{{ $val }}" {{ old('nivel_instruccion', $empleado->nivel_instruccion) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Título Obtenido</label>
                <input type="text" name="titulo" value="{{ old('titulo', $empleado->titulo) }}" class="form-control" maxlength="200">
            </div>
            <div class="form-group">
                <label class="form-label">Institución Educativa</label>
                <input type="text" name="institucion_educativa" value="{{ old('institucion_educativa', $empleado->institucion_educativa) }}" class="form-control" maxlength="200">
            </div>
        </div>
    </div>
</div>

{{-- ── Dirección de Residencia ─────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-location-dot" style="color:var(--accent);margin-right:8px;"></i>Dirección de Residencia</div></div>
    <div class="card-body">
        <div class="form-row form-row-3" style="margin-bottom:12px;">
            <div class="form-group">
                <label class="form-label">Estado</label>
                <input type="text" name="estado_residencia" value="{{ old('estado_residencia', $empleado->estado_residencia) }}" class="form-control" maxlength="80">
            </div>
            <div class="form-group">
                <label class="form-label">Municipio</label>
                <input type="text" name="municipio" value="{{ old('municipio', $empleado->municipio) }}" class="form-control" maxlength="80">
            </div>
            <div class="form-group">
                <label class="form-label">Parroquia</label>
                <input type="text" name="parroquia" value="{{ old('parroquia', $empleado->parroquia) }}" class="form-control" maxlength="80">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Dirección Completa</label>
            <textarea name="direccion_completa" class="form-control" rows="3">{{ old('direccion_completa', $empleado->direccion_completa) }}</textarea>
        </div>
    </div>
</div>

{{-- ── Salud y Condición Física ────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-heart-pulse" style="color:#e74c7a;margin-right:8px;"></i>Salud y Condición Física</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Tipo de Sangre</label>
                <select name="tipo_sangre" class="form-control">
                    <option value="">— No registrado —</option>
                    @foreach(App\Models\Empleado::tiposSangre() as $ts)
                    <option value="{{ $ts }}" {{ old('tipo_sangre', $empleado->tipo_sangre) === $ts ? 'selected' : '' }}>{{ $ts }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Condición Médica Relevante</label>
                <input type="text" name="condicion_medica" class="form-control" maxlength="300"
                    value="{{ old('condicion_medica', $empleado->condicion_medica) }}"
                    placeholder="Ej: Diabetes, Hipertensión…">
            </div>
            <div class="form-group" style="display:flex;flex-direction:column;justify-content:flex-end;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                    <input type="hidden" name="tiene_discapacidad" value="0">
                    <input type="checkbox" name="tiene_discapacidad" value="1"
                        {{ old('tiene_discapacidad', $empleado->tiene_discapacidad) ? 'checked' : '' }}
                        id="chkDiscapacidad"
                        style="width:18px;height:18px;accent-color:var(--accent-warn);"
                        onchange="document.getElementById('wrapDiscapacidad').style.display=this.checked?'block':'none';">
                    <span><strong>Tiene discapacidad</strong></span>
                </label>
            </div>
            <div class="form-group" id="wrapDiscapacidad" style="grid-column:span 3;display:{{ old('tiene_discapacidad', $empleado->tiene_discapacidad) ? 'block' : 'none' }};">
                <label class="form-label">Tipo / Descripción de Discapacidad</label>
                <input type="text" name="tipo_discapacidad" class="form-control" maxlength="200"
                    value="{{ old('tipo_discapacidad', $empleado->tipo_discapacidad) }}">
            </div>
        </div>
    </div>
</div>

{{-- ── Contacto de Emergencia ──────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-phone-volume" style="color:var(--accent-3);margin-right:8px;"></i>Contacto de Emergencia</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="contacto_emergencia_nombre" class="form-control" maxlength="150"
                    value="{{ old('contacto_emergencia_nombre', $empleado->contacto_emergencia_nombre) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Parentesco</label>
                <input type="text" name="contacto_emergencia_parentesco" class="form-control" maxlength="80"
                    value="{{ old('contacto_emergencia_parentesco', $empleado->contacto_emergencia_parentesco) }}"
                    placeholder="Ej: Cónyuge, Hijo/a, Madre…">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="contacto_emergencia_telefono" class="form-control" maxlength="30"
                    value="{{ old('contacto_emergencia_telefono', $empleado->contacto_emergencia_telefono) }}">
            </div>
        </div>
    </div>
</div>

{{-- ── Datos Laborales ─────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-briefcase" style="color:var(--accent-2);margin-right:8px;"></i>Datos Laborales</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Cargo *</label>
                <select name="cargo_id" class="form-control" required>
                    @foreach($cargos as $c)
                    <option value="{{ $c->id }}" {{ old('cargo_id', $empleado->cargo_id) == $c->id ? 'selected' : '' }}>
                        {{ $c->nombre }} — Bs. {{ number_format($c->salario_base, 2) }}
                    </option>
                    @endforeach
                </select>
                @error('cargo_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Unidad Ejecutora *</label>
                <select name="unidad_ejecutora_id" class="form-control" required>
                    @foreach($unidades as $u)
                    <option value="{{ $u->id }}" {{ old('unidad_ejecutora_id', $empleado->unidad_ejecutora_id) == $u->id ? 'selected' : '' }}>{{ $u->nombre }}</option>
                    @endforeach
                </select>
                @error('unidad_ejecutora_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Empleado *</label>
                <select name="tipo" class="form-control" required>
                    @foreach(['fijo' => 'Fijo', 'contratado' => 'Contratado', 'obrero' => 'Obrero'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('tipo', $empleado->tipo) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado *</label>
                <select name="estado" class="form-control" required>
                    @foreach(['activo' => 'Activo', 'inactivo' => 'Inactivo', 'jubilado' => 'Jubilado', 'retirado' => 'Retirado'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('estado', $empleado->estado) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Ingreso *</label>
                <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', $empleado->fecha_ingreso->format('Y-m-d')) }}" class="form-control" required>
                @error('fecha_ingreso')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Egreso</label>
                <input type="date" name="fecha_egreso" value="{{ old('fecha_egreso', $empleado->fecha_egreso?->format('Y-m-d')) }}" class="form-control">
            </div>
        </div>
    </div>
</div>

{{-- ── Datos Bancarios ─────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-building-columns" style="color:var(--accent-3);margin-right:8px;"></i>Datos Bancarios</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Banco</label>
                <input type="text" name="banco" value="{{ old('banco', $empleado->banco) }}" class="form-control" maxlength="80" placeholder="Ej: Banco de Venezuela">
            </div>
            <div class="form-group">
                <label class="form-label">N° de Cuenta</label>
                <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta', $empleado->numero_cuenta) }}" class="form-control" maxlength="30" placeholder="0102-XXXX-XXXX">
            </div>
        </div>
    </div>
</div>

{{-- ── Observaciones ────────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:16px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-note-sticky" style="color:var(--text-secondary);margin-right:8px;"></i>Observaciones</div></div>
    <div class="card-body">
        <textarea name="observaciones" class="form-control" rows="3" placeholder="Notas internas sobre el empleado…">{{ old('observaciones', $empleado->observaciones) }}</textarea>
    </div>
</div>

</div>{{-- /col izquierda --}}

{{-- ── Columna derecha ──────────────────────────────────────────── --}}
<div>
<div class="card fade-up" style="animation-delay:.05s;">
    <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;">
            <i class="fa-solid fa-save"></i> Guardar Cambios
        </button>
        <a href="{{ route('nomina.empleados.show', $empleado) }}" class="btn btn-outline" style="width:100%;justify-content:center;">
            Cancelar
        </a>
    </div>
</div>

{{-- Curriculum --}}
<div class="card fade-up" style="margin-top:16px;animation-delay:.1s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-file-pdf" style="color:var(--accent-danger);margin-right:8px;"></i>Curriculum</div></div>
    <div class="card-body">
        @if($empleado->curriculum_path)
        <div style="margin-bottom:12px;padding:10px;background:rgba(34,211,166,0.08);border-radius:8px;border:1px solid rgba(34,211,166,0.2);font-size:12px;">
            <i class="fa-solid fa-file-circle-check" style="color:var(--accent-3);margin-right:6px;"></i>
            CV cargado — <a href="{{ route('nomina.empleados.curriculum', $empleado) }}" style="color:var(--accent-3);">Descargar actual</a>
        </div>
        @endif
        <div class="form-group">
            <label class="form-label">{{ $empleado->curriculum_path ? 'Reemplazar CV' : 'Cargar Curriculum' }}</label>
            <input type="file" name="curriculum" class="form-control" accept=".pdf,.doc,.docx">
            <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">PDF, DOC o DOCX · Máx. 5 MB</div>
            @error('curriculum')<div class="form-error">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Accesos directos a tabs de show (solo lectura / cargas / historial) --}}
<div class="card fade-up" style="margin-top:16px;animation-delay:.15s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-arrow-up-right-from-square" style="color:var(--accent);margin-right:8px;"></i>Gestión en Perfil</div></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
        <p style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">Los siguientes datos se gestionan desde el perfil del empleado:</p>
        <a href="{{ route('nomina.empleados.show', $empleado) }}#familiares" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-people-group" style="margin-right:6px;color:var(--accent-3);"></i>Familiares y Cargas
        </a>
        <a href="{{ route('nomina.empleados.show', $empleado) }}#historial" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-timeline" style="margin-right:6px;color:var(--accent);"></i>Historial de Cargos
        </a>
        <a href="{{ route('nomina.empleados.show', $empleado) }}#formacion" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-book-open" style="margin-right:6px;color:var(--accent-warn);"></i>Formación Adicional
        </a>
    </div>
</div>

</div>{{-- /col derecha --}}
</div>
</form>

<script>
// Discapacidad toggle al cargar
document.addEventListener('DOMContentLoaded', function () {
    const chk = document.getElementById('chkDiscapacidad');
    const wrap = document.getElementById('wrapDiscapacidad');
    if (chk && wrap) {
        chk.addEventListener('change', function () {
            wrap.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>
@endsection
