@extends('layouts.app')
@section('title','Nuevo Beneficiario')
@section('breadcrumb')
    <span>Administración</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('admin.beneficiarios.index') }}">Beneficiarios</a><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Nuevo Beneficiario</h1></div>

<div class="card fade-up">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.beneficiarios.store') }}">
            @csrf
            @include('admin.beneficiarios._form', ['beneficiario' => null])
            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
                <a href="{{ route('admin.beneficiarios.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
