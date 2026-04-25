@extends('layouts.app')
@section('title', 'Empleado: ' . $empleado->nombre_completo)
@section('breadcrumb')
    <a href="{{ route('nomina.empleados.index') }}">Empleados</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $empleado->nombre_completo }}</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">{{ $empleado->nombre_completo }}</h1>
    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
        <span class="badge {{ $empleado->estadoBadge() }}" style="font-size:13px;">{{ ucfirst($empleado->estado) }}</span>
        <code style="color:var(--accent);background:rgba(79,142,247,0.1);padding:5px 12px;border-radius:7px;">{{ $empleado->cedula }}</code>
        <a href="{{ route('nomina.empleados.edit', $empleado) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
        @if($empleado->curriculum_path)
        <a href="{{ route('nomina.empleados.curriculum', $empleado) }}" class="btn btn-outline btn-sm" style="color:var(--accent-2);border-color:var(--accent-2);">
            <i class="fa-solid fa-file-arrow-down"></i> Descargar CV
        </a>
        @endif
    </div>
</div>

{{-- ── TABS ──────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--border);overflow-x:auto;" id="emp-tabs">
    @foreach([['perfil','Perfil','fa-id-card'],['salud','Salud','fa-heart-pulse'],['direccion','Dirección','fa-location-dot'],['familiares','Familiares','fa-people-group'],['historial','Historial','fa-timeline'],['formacion','Formación','fa-book-open']] as [$tid,$tlabel,$ticon])
    <button onclick="showTab('{{ $tid }}')" id="tab-{{ $tid }}"
        style="padding:10px 18px;background:none;border:none;border-bottom:2px solid transparent;color:var(--text-secondary);cursor:pointer;font-size:13px;font-weight:600;white-space:nowrap;transition:all .2s;"
        class="emp-tab">
        <i class="fa-solid {{ $ticon }}" style="margin-right:6px;"></i>{{ $tlabel }}
        @if($tid==='familiares')
            <span style="background:var(--accent);color:#fff;border-radius:100px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $empleado->familiares->count() }}</span>
        @endif
        @if($tid==='formacion')
            <span style="background:var(--accent-warn);color:#fff;border-radius:100px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $empleado->formaciones->count() }}</span>
        @endif
    </button>
    @endforeach
</div>

{{-- ═══════════ TAB: PERFIL ═══════════ --}}
<div id="pane-perfil">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
{{-- Datos laborales --}}
<div class="card fade-up">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-briefcase" style="color:var(--accent);margin-right:8px;"></i>Datos Laborales</div></div>
    <div class="card-body">
        <div class="form-row form-row-3" style="margin-bottom:16px;">
            <div><div style="font-size:11px;color:var(--text-secondary);">CARGO</div><div style="font-weight:600;">{{ $empleado->cargo->nombre ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">SALARIO BASE</div><div style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($empleado->cargo->salario_base ?? 0, 2) }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">TIPO</div><div><span class="badge badge-blue">{{ ucfirst($empleado->tipo) }}</span></div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">UNIDAD EJECUTORA</div><div style="font-size:13px;">{{ $empleado->unidadEjecutora->nombre ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">FECHA INGRESO</div><div>{{ $empleado->fecha_ingreso->format('d/m/Y') }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">ANTIGÜEDAD</div><div style="font-weight:700;">{{ $empleado->antiguedad }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">TELÉFONO</div><div>{{ $empleado->telefono ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">EMAIL</div><div style="font-size:12px;">{{ $empleado->email ?? '—' }}</div></div>
            @if($empleado->fecha_egreso)
            <div><div style="font-size:11px;color:var(--text-secondary);">FECHA EGRESO</div><div>{{ $empleado->fecha_egreso->format('d/m/Y') }}</div></div>
            @endif
        </div>
        <div><div style="font-size:11px;color:var(--text-secondary);">BANCO / CUENTA</div><div style="font-family:monospace;font-size:13px;">{{ $empleado->banco ?? '—' }} / {{ $empleado->numero_cuenta ?? '—' }}</div></div>
    </div>
</div>

{{-- Datos civiles y académicos --}}
<div class="card fade-up" style="margin-top:20px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-person" style="color:var(--accent-2);margin-right:8px;"></i>Datos Civiles y Académicos</div>
        @can('nomina.empleados.editar')
        <a href="{{ route('nomina.empleados.edit', $empleado) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
        @endcan
    </div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div><div style="font-size:11px;color:var(--text-secondary);">ESTADO CIVIL</div><div>{{ $empleado->estado_civil ? App\Models\Empleado::estadosCiviles()[$empleado->estado_civil] : '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">NACIONALIDAD</div><div>{{ $empleado->nacionalidad ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">FECHA DE NACIMIENTO</div><div>{{ $empleado->fecha_nacimiento ? $empleado->fecha_nacimiento->format('d/m/Y') . ' (' . $empleado->fecha_nacimiento->diffInYears(now()) . ' años)' : '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">LUGAR DE NACIMIENTO</div><div>{{ $empleado->lugar_nacimiento ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">NIVEL DE INSTRUCCIÓN</div><div>{{ $empleado->nivel_instruccion ? App\Models\Empleado::nivelesInstruccion()[$empleado->nivel_instruccion] : '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">TÍTULO</div><div>{{ $empleado->titulo ?? '—' }}</div></div>
            <div style="grid-column:span 3;"><div style="font-size:11px;color:var(--text-secondary);">INSTITUCIÓN EDUCATIVA</div><div>{{ $empleado->institucion_educativa ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">CARGAS FAMILIARES</div>
                <div style="font-weight:700;color:var(--accent-3);">{{ $empleado->familiares->where('es_carga_familiar',true)->count() }}</div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Sidebar --}}
<div>
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-body">
        <div style="padding:12px 0;border-bottom:1px solid var(--border);margin-bottom:12px;">
            <div style="font-size:11px;color:var(--text-secondary);">ANTIGÜEDAD</div>
            <div style="font-weight:700;font-size:20px;color:var(--accent);">{{ $empleado->antiguedad }}</div>
        </div>
        <div style="padding:12px 0;border-bottom:1px solid var(--border);margin-bottom:12px;">
            <div style="font-size:11px;color:var(--text-secondary);">CARGAS FAMILARES</div>
            <div style="font-weight:700;font-size:20px;color:var(--accent-3);">{{ $empleado->familiares->where('es_carga_familiar',true)->count() }}</div>
        </div>
        <div style="padding:12px 0;">
            <div style="font-size:11px;color:var(--text-secondary);">CURRICULUM</div>
            @if($empleado->curriculum_path)
                <a href="{{ route('nomina.empleados.curriculum', $empleado) }}" class="btn btn-outline btn-sm" style="margin-top:8px;width:100%;justify-content:center;">
                    <i class="fa-solid fa-file-arrow-down"></i> Descargar CV
                </a>
            @else
                <div style="color:var(--text-secondary);font-size:12px;margin-top:4px;">Sin curriculum cargado</div>
            @endif
        </div>
    </div>
</div>

{{-- Historial nóminas --}}
<div class="card fade-up" style="margin-top:20px;animation-delay:.15s">
    <div class="card-header"><div class="card-title">Últimas Nóminas</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nómina</th><th>Período</th><th>Neto</th></tr></thead>
            <tbody>
            @forelse($empleado->nominasDetalle->take(8) as $det)
            <tr>
                <td><a href="{{ route('nomina.nominas.show', $det->nomina) }}" style="color:var(--accent);">{{ $det->nomina->numero }}</a></td>
                <td style="font-size:12px;">{{ $det->nomina->periodo_inicio->format('d/m/Y') }}</td>
                <td style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($det->neto,2) }}</td>
            </tr>
            @empty<tr><td colspan="3" style="text-align:center;color:var(--text-secondary);">Sin nóminas</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
</div>{{-- /grid --}}
</div>{{-- /pane-perfil --}}

{{-- ═══════════ TAB: DIRECCIÓN ═══════════ --}}
<div id="pane-direccion" style="display:none;">
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-location-dot" style="color:var(--accent);margin-right:8px;"></i>Dirección de Residencia</div>
        @can('nomina.empleados.editar')
        <a href="{{ route('nomina.empleados.edit', $empleado) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
        @endcan
    </div>
    <div class="card-body">
        <div class="form-row form-row-3" style="margin-bottom:16px;">
            <div><div style="font-size:11px;color:var(--text-secondary);">ESTADO</div><div>{{ $empleado->estado_residencia ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">MUNICIPIO</div><div>{{ $empleado->municipio ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">PARROQUIA</div><div>{{ $empleado->parroquia ?? '—' }}</div></div>
        </div>
        <div><div style="font-size:11px;color:var(--text-secondary);">DIRECCIÓN COMPLETA</div>
            <div style="margin-top:6px;padding:14px;background:rgba(255,255,255,0.04);border-radius:8px;border:1px solid var(--border);font-size:13px;line-height:1.6;">
                {{ $empleado->direccion_completa ?? 'Sin dirección registrada.' }}</div>
        </div>
    </div>
</div>
</div>

{{-- ═══════════ TAB: FAMILIARES ═══════════ --}}
<div id="pane-familiares" style="display:none;">
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-people-group" style="color:var(--accent);margin-right:8px;"></i>Familiares y Cargas</div>
        <span style="font-size:12px;color:var(--accent-3);font-weight:700;">{{ $empleado->familiares->where('es_carga_familiar',true)->count() }} cargas familiares</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nombre</th><th>Parentesco</th><th>Cédula</th><th>Edad</th><th>Teléfono</th><th>Carga</th><th></th></tr></thead>
            <tbody>
            @forelse($empleado->familiares as $f)
            <tr>
                <td style="font-weight:600;">{{ $f->nombre_completo }}</td>
                <td><span class="badge badge-blue">{{ App\Models\EmpleadoFamiliar::parentescoLabel($f->parentesco) }}</span></td>
                <td style="font-family:monospace;">{{ $f->cedula ?? '—' }}</td>
                <td>{{ $f->edad !== null ? $f->edad . ' años' : '—' }}</td>
                <td>{{ $f->telefono ?? '—' }}</td>
                <td>
                    @if($f->es_carga_familiar)
                        <span class="badge badge-active">Sí</span>
                    @else
                        <span class="badge" style="opacity:.5;">No</span>
                    @endif
                </td>
                <td>
                    <form method="POST" action="{{ route('nomina.empleados.familiares.destroy',[$empleado,$f]) }}" onsubmit="return confirm('¿Eliminar este familiar?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="color:var(--accent-danger);background:none;border:1px solid var(--accent-danger);padding:3px 10px;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty<tr><td colspan="7" style="text-align:center;color:var(--text-secondary);">Sin familiares registrados.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Formulario agregar familiar --}}
<div class="card fade-up" style="margin-top:20px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-person-circle-plus" style="color:var(--accent-3);margin-right:8px;"></i>Agregar Familiar</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('nomina.empleados.familiares.store', $empleado) }}">
            @csrf
            <div class="form-row form-row-3" style="margin-bottom:16px;">
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="nombre_completo" class="form-control" required maxlength="150" value="{{ old('nombre_completo') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Parentesco *</label>
                    <select name="parentesco" class="form-control" required>
                        <option value="">Seleccionar…</option>
                        @foreach(['hijo'=>'Hijo/a','conyuge'=>'Cónyuge','padre'=>'Padre','madre'=>'Madre','hermano'=>'Hermano/a','otro'=>'Otro'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ old('parentesco')===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" maxlength="20" value="{{ old('cedula') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" maxlength="30" value="{{ old('telefono') }}">
                </div>
                <div class="form-group" style="grid-column:span 3;">
                    <label class="form-label">Observaciones</label>
                    <input type="text" name="observaciones" class="form-control" maxlength="300" value="{{ old('observaciones') }}">
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                    <input type="checkbox" name="es_carga_familiar" value="1" {{ old('es_carga_familiar')?'checked':'' }}
                        style="width:18px;height:18px;accent-color:var(--accent-3);">
                    <span><strong>Es carga familiar</strong> <span style="color:var(--text-secondary);font-size:12px;">(aplica para beneficios de nómina)</span></span>
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Agregar Familiar
                </button>
            </div>
        </form>
    </div>
</div>
</div>{{-- /pane-familiares --}}

{{-- ═══════════ TAB: HISTORIAL ═══════════ --}}
<div id="pane-historial" style="display:none;">
<div class="card fade-up">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-timeline" style="color:var(--accent);margin-right:8px;"></i>Historial de Cargos</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Cargo</th><th>Institución</th><th>Unidad</th><th>Desde</th><th>Hasta</th><th>Duración</th><th>Motivo</th><th></th></tr></thead>
            <tbody>
            @forelse($empleado->historialCargos as $h)
            <tr>
                <td style="font-weight:600;">{{ $h->cargo_nombre }}</td>
                <td style="font-size:12px;">{{ $h->institucion_nombre }}</td>
                <td style="font-size:12px;">{{ $h->unidadEjecutora->nombre ?? '—' }}</td>
                <td style="font-family:monospace;font-size:12px;">{{ $h->fecha_inicio->format('d/m/Y') }}</td>
                <td style="font-family:monospace;font-size:12px;">
                    {{ $h->fecha_fin ? $h->fecha_fin->format('d/m/Y') : '<span class="badge badge-active">Actual</span>' }}
                </td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $h->duracion }}</td>
                <td style="font-size:12px;max-width:180px;">{{ $h->motivo_cambio ?? '—' }}</td>
                <td>
                    <form method="POST" action="{{ route('nomina.empleados.historial.destroy',[$empleado,$h]) }}" onsubmit="return confirm('¿Eliminar este registro?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="color:var(--accent-danger);background:none;border:1px solid var(--accent-danger);padding:3px 10px;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty<tr><td colspan="8" style="text-align:center;color:var(--text-secondary);">Sin historial registrado.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Formulario agregar cargo al historial --}}
<div class="card fade-up" style="margin-top:20px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-plus-circle" style="color:var(--accent-3);margin-right:8px;"></i>Agregar Cargo al Historial</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('nomina.empleados.historial.store', $empleado) }}">
            @csrf
            <div class="form-row form-row-3" style="margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Cargo del Sistema</label>
                    <select name="cargo_id" class="form-control" id="hCargo">
                        <option value="">— Cargo libre / externo —</option>
                        @foreach($cargos as $c)
                        <option value="{{ $c->id }}" {{ old('cargo_id')==$c->id?'selected':'' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="hCargoTextoWrap" style="display:{{ old('cargo_id') ? 'none' : 'block' }};">
                    <label class="form-label">Cargo (texto libre)</label>
                    <input type="text" name="cargo_texto" class="form-control" maxlength="200"
                        placeholder="Nombre del cargo externo o libre…" value="{{ old('cargo_texto') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Institución</label>
                    <input type="text" name="institucion" class="form-control" maxlength="200"
                        placeholder="Dejar vacío = esta institución" value="{{ old('institucion') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Unidad Ejecutora</label>
                    <select name="unidad_ejecutora_id" class="form-control">
                        <option value="">— N/A o externa —</option>
                        @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ old('unidad_ejecutora_id')==$u->id?'selected':'' }}>{{ $u->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Inicio *</label>
                    <input type="date" name="fecha_inicio" class="form-control" required value="{{ old('fecha_inicio') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}"
                        placeholder="Vacío = cargo actual">
                </div>
                <div class="form-group" style="grid-column:span 3;">
                    <label class="form-label">Motivo del Cambio</label>
                    <input type="text" name="motivo_cambio" class="form-control" maxlength="300" value="{{ old('motivo_cambio') }}">
                </div>
            </div>
            <div style="text-align:right;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Agregar al Historial
                </button>
            </div>
        </form>
    </div>
</div>
</div>{{-- /pane-historial --}}

{{-- ═══════════ TAB: SALUD ═══════════ --}}
<div id="pane-salud" style="display:none;">

{{-- Datos de salud --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-heart-pulse" style="color:#e74c7a;margin-right:8px;"></i>Condición de Salud</div>
        @can('nomina.empleados.editar')
        <a href="{{ route('nomina.empleados.edit', $empleado) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
        @endcan
    </div>
    <div class="card-body">
        <div class="form-row form-row-3" style="margin-bottom:20px;">
            <div>
                <div style="font-size:11px;color:var(--text-secondary);">TIPO DE SANGRE</div>
                @if($empleado->tipo_sangre)
                    <div style="font-size:22px;font-weight:800;color:#e74c7a;">{{ $empleado->tipo_sangre }}</div>
                @else
                    <div style="color:var(--text-secondary);">No registrado</div>
                @endif
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);">DISCAPACIDAD</div>
                <div style="margin-top:4px;">
                    @if($empleado->tiene_discapacidad)
                        <span class="badge badge-warn">Sí</span>
                        <div style="font-size:12px;margin-top:4px;">{{ $empleado->tipo_discapacidad ?? 'No especificada' }}</div>
                    @else
                        <span class="badge badge-active">No</span>
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);">CONDICIÓN MÉDICA RELEVANTE</div>
                <div style="font-size:13px;margin-top:4px;">{{ $empleado->condicion_medica ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Contacto de emergencia --}}
<div class="card fade-up" style="margin-top:20px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-phone-volume" style="color:var(--accent-3);margin-right:8px;"></i>Contacto de Emergencia</div>
        @can('nomina.empleados.editar')
        <a href="{{ route('nomina.empleados.edit', $empleado) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
        @endcan
    </div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div>
                <div style="font-size:11px;color:var(--text-secondary);">NOMBRE</div>
                <div style="font-weight:600;">{{ $empleado->contacto_emergencia_nombre ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);">PARENTESCO</div>
                <div>{{ $empleado->contacto_emergencia_parentesco ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-secondary);">TELÉFONO</div>
                <div style="font-weight:700;color:var(--accent-3);">{{ $empleado->contacto_emergencia_telefono ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>
</div>{{-- /pane-salud --}}

{{-- ═══════════ TAB: FORMACIÓN ADICIONAL ═══════════ --}}
<div id="pane-formacion" style="display:none;">

{{-- Lista de formaciones --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-book-open" style="color:var(--accent-warn);margin-right:8px;"></i>Cursos, Certificaciones e Idiomas</div>
        <span style="font-size:12px;color:var(--text-secondary);">{{ $empleado->formaciones->count() }} registro(s)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tipo</th><th>Nombre</th><th>Institución</th><th>País</th><th>Período</th><th>Horas</th><th>Nivel</th><th></th></tr></thead>
            <tbody>
            @forelse($empleado->formaciones as $f)
            <tr>
                <td><span class="badge {{ $f->tipoBadgeColor() }}">{{ $f->tipo_label }}</span></td>
                <td style="font-weight:600;max-width:200px;">{{ $f->nombre }}</td>
                <td style="font-size:12px;">{{ $f->institucion ?? '—' }}</td>
                <td style="font-size:12px;">{{ $f->pais ?? '—' }}</td>
                <td style="font-family:monospace;font-size:12px;">
                    {{ $f->periodo }}
                    @if($f->en_curso)<span class="badge badge-active" style="margin-left:4px;">En curso</span>@endif
                </td>
                <td style="font-size:12px;">{{ $f->duracion_horas ? $f->duracion_horas . 'h' : '—' }}</td>
                <td style="font-size:12px;">
                    @if($f->tipo === 'idioma' && $f->nivel_idioma)
                        <span class="badge badge-blue">{{ App\Models\EmpleadoFormacion::nivelesIdioma()[$f->nivel_idioma] }}</span>
                    @else
                        —
                    @endif
                </td>
                <td style="display:flex;gap:6px;align-items:center;">
                    @if($f->documento_path)
                    <a href="{{ route('nomina.empleados.formaciones.documento', [$empleado, $f]) }}"
                        class="btn btn-sm" style="color:var(--accent);border:1px solid var(--accent);padding:3px 8px;"
                        title="Descargar documento">
                        <i class="fa-solid fa-file-arrow-down"></i>
                    </a>
                    @endif
                    @can('nomina.empleados.editar')
                    <form method="POST" action="{{ route('nomina.empleados.formaciones.destroy', [$empleado, $f]) }}"
                        onsubmit="return confirm('\u00bfEliminar esta formación?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="color:var(--accent-danger);background:none;border:1px solid var(--accent-danger);padding:3px 10px;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                    @endcan
                </td>
            </tr>
            @if($f->descripcion)
            <tr style="background:rgba(255,255,255,0.02);">
                <td></td>
                <td colspan="7" style="font-size:11px;color:var(--text-secondary);padding-top:0;"><i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>{{ $f->descripcion }}</td>
            </tr>
            @endif
            @empty
            <tr><td colspan="8" style="text-align:center;color:var(--text-secondary);">Sin formaciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Formulario agregar formación --}}
@can('nomina.empleados.editar')
<div class="card fade-up" style="margin-top:20px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-plus-circle" style="color:var(--accent-3);margin-right:8px;"></i>Agregar Formación</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('nomina.empleados.formaciones.store', $empleado) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row form-row-3" style="margin-bottom:16px;">
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-control" required id="fTipo" onchange="toggleNivelIdioma()">
                        <option value="">Seleccionar…</option>
                        @foreach(App\Models\EmpleadoFormacion::tipos() as $val => $lbl)
                        <option value="{{ $val }}" {{ old('tipo')===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Nombre del Curso / Certificación / Idioma *</label>
                    <input type="text" name="nombre" class="form-control" required maxlength="200" value="{{ old('nombre') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Institución</label>
                    <input type="text" name="institucion" class="form-control" maxlength="200" value="{{ old('institucion') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">País</label>
                    <input type="text" name="pais" class="form-control" maxlength="80" value="{{ old('pais') }}" placeholder="Venezuela, EEUU...">
                </div>
                <div class="form-group" id="wrapNivelIdioma" style="display:none;">
                    <label class="form-label">Nivel del Idioma</label>
                    <select name="nivel_idioma" class="form-control">
                        <option value="">Seleccionar…</option>
                        @foreach(App\Models\EmpleadoFormacion::nivelesIdioma() as $val => $lbl)
                        <option value="{{ $val }}" {{ old('nivel_idioma')===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Horas Académicas</label>
                    <input type="number" name="duracion_horas" class="form-control" min="1" max="9999" value="{{ old('duracion_horas') }}" placeholder="Ej: 40">
                </div>
                <div class="form-group">
                    <label class="form-label">Nro. Constancia / Certificado</label>
                    <input type="text" name="numero_registro" class="form-control" maxlength="100" value="{{ old('numero_registro') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Documento (PDF / Imagen)</label>
                    <input type="file" name="documento" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="form-group" style="grid-column:span 3;">
                    <label class="form-label">Descripción / Notas</label>
                    <textarea name="descripcion" class="form-control" rows="2" maxlength="1000" placeholder="Descripción breve del contenido o logros…">{{ old('descripcion') }}</textarea>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                    <input type="checkbox" name="en_curso" value="1" {{ old('en_curso')?'checked':'' }}
                        style="width:18px;height:18px;accent-color:var(--accent-3);">
                    <span><strong>En curso</strong> <span style="color:var(--text-secondary);font-size:12px;">(aún no finalizado)</span></span>
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Agregar Formación
                </button>
            </div>
        </form>
    </div>
</div>
@endcan
</div>{{-- /pane-formacion --}}

<div style="margin-top:16px;"><a href="{{ route('nomina.empleados.index') }}" class="btn btn-outline">← Volver</a></div>

<script>
function showTab(id) {
    document.querySelectorAll('[id^="pane-"]').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.emp-tab').forEach(t => {
        t.style.borderBottomColor = 'transparent';
        t.style.color = 'var(--text-secondary)';
    });
    document.getElementById('pane-' + id).style.display = 'block';
    const btn = document.getElementById('tab-' + id);
    btn.style.borderBottomColor = 'var(--accent)';
    btn.style.color = 'var(--text-primary)';
}
// Activar tab por hash o default perfil
const hash = location.hash.replace('#','') || 'perfil';
showTab(['perfil','salud','direccion','familiares','historial','formacion'].includes(hash) ? hash : 'perfil');
// Actualizar hash al cambiar tab
document.querySelectorAll('.emp-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        location.hash = btn.id.replace('tab-','');
    });
});

// ── Toggle campo "cargo libre" ────────────────────────────────
const hCargo = document.getElementById('hCargo');
const hCargoTextoWrap = document.getElementById('hCargoTextoWrap');
function toggleCargoTexto() {
    const tieneCargoSistema = hCargo.value !== '';
    hCargoTextoWrap.style.display = tieneCargoSistema ? 'none' : 'block';
    if (tieneCargoSistema) {
        // Limpiar el campo cuando se selecciona un cargo del sistema
        hCargoTextoWrap.querySelector('input').value = '';
    }
}
hCargo.addEventListener('change', toggleCargoTexto);
// Aplicar estado inicial al cargar la página

// ── Toggle nivel idioma ────────────────────────────────────────
function toggleNivelIdioma() {
    const tipo = document.getElementById('fTipo');
    const wrap = document.getElementById('wrapNivelIdioma');
    if (!tipo || !wrap) return;
    wrap.style.display = tipo.value === 'idioma' ? 'block' : 'none';
    if (tipo.value !== 'idioma') {
        const sel = wrap.querySelector('select');
        if (sel) sel.value = '';
    }
}
document.addEventListener('DOMContentLoaded', toggleNivelIdioma);
toggleCargoTexto();
</script>
@endsection
