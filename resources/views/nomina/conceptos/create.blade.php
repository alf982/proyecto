@extends('layouts.app')
@section('title', 'Nuevo Concepto de Nómina')
@section('breadcrumb')
    <a href="{{ route('nomina.conceptos.index') }}">Conceptos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Nuevo Concepto de Nómina</h1></div>
<div style="max-width:640px;">
<form method="POST" action="{{ route('nomina.conceptos.store') }}">
@csrf
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Configuración del Concepto</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" class="form-control" required maxlength="20" placeholder="Ej: IVSS, FAOV, BANAVIH">
                @error('codigo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" required maxlength="150" placeholder="Ej: Seguro Social (IVSS)">
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Tipo *</label>
                <select name="tipo" class="form-control" required>
                    <option value="asignacion" {{ old('tipo','asignacion')=='asignacion'?'selected':'' }}>+ Asignación</option>
                    <option value="deduccion" {{ old('tipo')=='deduccion'?'selected':'' }}>- Deducción</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Cálculo *</label>
                <select name="calculo" id="sel-calculo" class="form-control" required onchange="updateLabel()">
                    <option value="porcentaje" {{ old('calculo','porcentaje')=='porcentaje'?'selected':'' }}>Porcentaje del sueldo</option>
                    <option value="fijo" {{ old('calculo')=='fijo'?'selected':'' }}>Monto fijo</option>
                </select>
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label" id="lbl-valor">Valor (%) *</label>
                <input type="number" name="valor" value="{{ old('valor', 0) }}" class="form-control" required step="0.0001" min="0">
                @error('valor')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Aplica A *</label>
                <select name="aplica_a" class="form-control" required>
                    @foreach(['todos'=>'Todos','fijos'=>'Personal Fijo','contratados'=>'Contratados','obreros'=>'Obreros'] as $v=>$lbl)
                    <option value="{{ $v }}" {{ old('aplica_a','todos')==$v?'selected':'' }}>{{ $lbl }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="es_obligatorio" value="1" {{ old('es_obligatorio')?'checked':'' }}>
                <span class="form-label" style="margin:0;">Es obligatorio (no se puede omitir en el cálculo)</span>
            </label>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción / Base legal</label>
            <textarea name="descripcion" class="form-control" rows="2" placeholder="Ej: Art. 62 Ley del IVSS — Aporte del trabajador 4%">{{ old('descripcion') }}</textarea>
        </div>
    </div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
    <a href="{{ route('nomina.conceptos.index') }}" class="btn btn-outline">Cancelar</a>
</div>
</form>
</div>
@push('scripts')
<script>
function updateLabel() {
    document.getElementById('lbl-valor').textContent = document.getElementById('sel-calculo').value === 'porcentaje' ? 'Porcentaje (%) *' : 'Monto Fijo (Bs.) *';
}
updateLabel();
</script>
@endpush
@endsection
