@extends('layouts.app')
@section('title','Editar Beneficiario')
@section('breadcrumb')
    <span>Administración</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('admin.beneficiarios.index') }}">Beneficiarios</a><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Editar: {{ $beneficiario->razon_social }}</h1></div>

<div class="card fade-up">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.beneficiarios.update', $beneficiario) }}">
            @csrf @method('PUT')
            @include('admin.beneficiarios._form', ['beneficiario' => $beneficiario])
            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Actualizar</button>
                <a href="{{ route('admin.beneficiarios.show', $beneficiario) }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
