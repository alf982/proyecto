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
        <div class="card fade-up">
            <div class="card-header"><div class="card-title">Datos Personales (Ficha Laboral)</div></div>
            <div class="card-body">
                <!-- Nombres y Apellidos -->
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label">Primer Apellido *</label>
                        <input type="text" name="primer_apellido" value="{{ old('primer_apellido', $empleado->primer_apellido) }}" class="form-control" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Segundo Apellido</label>
                        <input type="text" name="segundo_apellido" value="{{ old('segundo_apellido', $empleado->segundo_apellido) }}" class="form-control" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" value="{{ old('nombres', $empleado->nombres) }}" class="form-control" required maxlength="100">
                    </div>
                </div>

                <!-- Documentos -->
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label">Cédula *</label>
                        <input type="text" name="cedula" value="{{ old('cedula', $empleado->cedula) }}" class="form-control" required maxlength="15" placeholder="V-12345678">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pasaporte</label>
                        <input type="text" name="pasaporte" value="{{ old('pasaporte', $empleado->pasaporte) }}" class="form-control" maxlength="30">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sexo *</label>
                        <select name="sexo" class="form-control" required>
                            <option value="M" {{ old('sexo', $empleado->sexo)=='M'?'selected':'' }}>Masculino</option>
                            <option value="F" {{ old('sexo', $empleado->sexo)=='F'?'selected':'' }}>Femenino</option>
                        </select>
                    </div>
                </div>

                <!-- Militares y Nacionalidad -->
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label">Nacionalidad</label>
                        <select name="nacionalidad" class="form-control">
                            <option value="venezolana" {{ old('nacionalidad', $empleado->nacionalidad)=='venezolana'?'selected':'' }}>Venezolano/a</option>
                            <option value="extranjera" {{ old('nacionalidad', $empleado->nacionalidad)=='extranjera'?'selected':'' }}>Extranjero/a</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Carnet Inscripción Militar</label>
                        <input type="text" name="numero_carnet_militar" value="{{ old('numero_carnet_militar', $empleado->numero_carnet_militar) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expedición Militar</label>
                        <input type="date" name="fecha_expedicion_militar" value="{{ old('fecha_expedicion_militar', $empleado->fecha_expedicion_militar) }}" class="form-control">
                    </div>
                </div>
                
                <div class="form-row" style="margin-bottom:20px;">
                    <div class="form-group" style="background:var(--bg-secondary);padding:15px;border-radius:8px;border:1px dashed var(--border);">
                        <label class="form-label" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <i class="fa-solid fa-id-card" style="color:var(--accent);"></i>
                            Foto del Carnet Militar (Opcional)
                            @if($empleado->carnet_militar_foto_path)
                            <a href="{{ route('empleados.carnet_militar', $empleado) }}" target="_blank" class="badge" style="margin-left:auto;background:rgba(34,211,166,0.1);color:var(--accent-3);text-decoration:none;display:inline-flex;align-items:center;gap:4px;padding:4px 8px;">
                                <i class="fa-solid fa-download"></i> Ver actual
                            </a>
                            @endif
                        </label>
                        <input type="file" name="carnet_militar_foto" class="form-control" accept=".jpg,.jpeg,.png,.pdf" style="background:transparent;border:none;padding:0;">
                        <small style="color:var(--text-secondary);display:block;margin-top:5px;">Formatos permitidos: JPG, PNG, PDF. Max: 2MB.</small>
                    </div>
                </div>

                <!-- Nacimiento -->
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $empleado->fecha_nacimiento?->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">País Nacimiento</label>
                        <input type="text" name="pais_nacimiento" value="{{ old('pais_nacimiento', $empleado->pais_nacimiento) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado Nacimiento</label>
                        <input type="text" name="estado_nacimiento" value="{{ old('estado_nacimiento', $empleado->estado_nacimiento) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Municipio Nac.</label>
                        <input type="text" name="municipio_nacimiento" value="{{ old('municipio_nacimiento', $empleado->municipio_nacimiento) }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="card fade-up" style="margin-top:20px;">
            <div class="card-header"><div class="card-title">Dirección y Contacto</div></div>
            <div class="card-body">
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label">País Residencia</label>
                        <input type="text" name="pais_residencia" value="{{ old('pais_residencia', $empleado->pais_residencia) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado Residencia</label>
                        <input type="text" name="estado_residencia" value="{{ old('estado_residencia', $empleado->estado_residencia) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Municipio Residencia</label>
                        <input type="text" name="municipio" value="{{ old('municipio', $empleado->municipio) }}" class="form-control">
                    </div>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">Dirección Completa</label>
                    <input type="text" name="direccion_completa" value="{{ old('direccion_completa', $empleado->direccion_completa) }}" class="form-control">
                </div>
                <div class="form-row form-row-2" style="margin-top:10px;">
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $empleado->telefono) }}" class="form-control" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $empleado->email) }}" class="form-control" maxlength="150">
                    </div>
                </div>
            </div>
        </div>

        <div class="card fade-up" style="margin-top:20px;">
            <div class="card-header"><div class="card-title">Tiempo de Experiencia Previa</div></div>
            <div class="card-body">
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label">Pública (Años/Meses)</label>
                        <div style="display:flex;gap:10px">
                            <input type="number" name="anos_experiencia_publica" value="{{ old('anos_experiencia_publica', $empleado->anos_experiencia_publica) }}" class="form-control" min="0" placeholder="Años">
                            <input type="number" name="meses_experiencia_publica" value="{{ old('meses_experiencia_publica', $empleado->meses_experiencia_publica) }}" class="form-control" min="0" max="11" placeholder="Meses">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Privada (Años/Meses)</label>
                        <div style="display:flex;gap:10px">
                            <input type="number" name="anos_experiencia_privada" value="{{ old('anos_experiencia_privada', $empleado->anos_experiencia_privada) }}" class="form-control" min="0" placeholder="Años">
                            <input type="number" name="meses_experiencia_privada" value="{{ old('meses_experiencia_privada', $empleado->meses_experiencia_privada) }}" class="form-control" min="0" max="11" placeholder="Meses">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Independiente (A/M)</label>
                        <div style="display:flex;gap:10px">
                            <input type="number" name="anos_experiencia_independiente" value="{{ old('anos_experiencia_independiente', $empleado->anos_experiencia_independiente) }}" class="form-control" min="0" placeholder="Años">
                            <input type="number" name="meses_experiencia_independiente" value="{{ old('meses_experiencia_independiente', $empleado->meses_experiencia_independiente) }}" class="form-control" min="0" max="11" placeholder="Meses">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div>
        <div class="card fade-up">
            <div class="card-header"><div class="card-title">Datos Laborales</div></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Cargo *</label>
                    <select name="cargo_id" class="form-control" required>
                        <option value="">Seleccione…</option>
                        @foreach($cargos as $c)
                            <option value="{{ $c->id }}" {{ old('cargo_id', $empleado->cargo_id)==$c->id?'selected':'' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">Unidad Ejecutora *</label>
                    <select name="unidad_ejecutora_id" class="form-control" required>
                        <option value="">Seleccione…</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u->id }}" {{ old('unidad_ejecutora_id', $empleado->unidad_ejecutora_id)==$u->id?'selected':'' }}>{{ $u->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">Tipo de Empleado *</label>
                    <select name="tipo" class="form-control" required>
                        <option value="fijo" {{ old('tipo', $empleado->tipo)=='fijo'?'selected':'' }}>Fijo</option>
                        <option value="contratado" {{ old('tipo', $empleado->tipo)=='contratado'?'selected':'' }}>Contratado</option>
                        <option value="obrero" {{ old('tipo', $empleado->tipo)=='obrero'?'selected':'' }}>Obrero</option>
                    </select>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">Estado *</label>
                    <select name="estado" class="form-control" required>
                        <option value="activo" {{ old('estado', $empleado->estado)=='activo'?'selected':'' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $empleado->estado)=='inactivo'?'selected':'' }}>Inactivo</option>
                        <option value="jubilado" {{ old('estado', $empleado->estado)=='jubilado'?'selected':'' }}>Jubilado</option>
                        <option value="retirado" {{ old('estado', $empleado->estado)=='retirado'?'selected':'' }}>Retirado</option>
                    </select>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">Fecha de Ingreso *</label>
                    <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', $empleado->fecha_ingreso?->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">Fecha de Egreso</label>
                    <input type="date" name="fecha_egreso" value="{{ old('fecha_egreso', $empleado->fecha_egreso?->format('Y-m-d')) }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card fade-up" style="margin-top:20px;">
            <div class="card-header"><div class="card-title">Datos Bancarios</div></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Banco</label>
                    <input type="text" name="banco" value="{{ old('banco', $empleado->banco) }}" class="form-control" maxlength="80">
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label class="form-label">N° de Cuenta</label>
                    <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta', $empleado->numero_cuenta) }}" class="form-control" maxlength="30">
                </div>
            </div>
        </div>
        
        <div class="card fade-up" style="margin-top:20px;">
            <div class="card-header"><div class="card-title">Declaración Jurada</div></div>
            <div class="card-body">
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="inhabilitado" value="1" {{ old('inhabilitado', $empleado->inhabilitado) ? 'checked' : '' }}>
                    <span>¿Inhabilitado(a) o con incompatibilidad legal?</span>
                </label>
            </div>
        </div>

        <div class="card fade-up" style="margin-top:20px;">
            <div class="card-body">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
                <a href="{{ route('nomina.empleados.show', $empleado) }}" class="btn btn-outline" style="width:100%;justify-content:center;">Cancelar</a>
            </div>
        </div>
        
        @if($errors->any())
        <div class="card fade-up" style="margin-top:20px;border-color:var(--color-danger)">
            <div class="card-body" style="color:var(--color-danger);font-size:12px;">
                <ul style="margin:0;padding-left:15px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
</form>
@endsection
