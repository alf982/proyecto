@extends('layouts.app')
@section('title','Beneficiario: '.$beneficiario->razon_social)
@section('breadcrumb')
    <span>Administración</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('admin.beneficiarios.index') }}">Beneficiarios</a><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $beneficiario->rif }}</span>
@endsection
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">{{ $beneficiario->razon_social }}</h1>
        <p class="page-subtitle">{{ $beneficiario->rif }} · <span class="badge badge-blue">{{ $beneficiario->getTipoLabel() }}</span></p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.beneficiarios.edit', $beneficiario) }}" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
        <form method="POST" action="{{ route('admin.beneficiarios.toggle', $beneficiario) }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn btn-outline">
                <i class="fa-solid {{ $beneficiario->activo ? 'fa-ban' : 'fa-check' }}"></i>
                {{ $beneficiario->activo ? 'Desactivar' : 'Activar' }}
            </button>
        </form>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card fade-up">
        <div class="card-header"><h3 class="card-title"><i class="fa-solid fa-building"></i> Datos Generales</h3></div>
        <div class="card-body">
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:14px;">
                <dt style="color:var(--text-secondary);font-weight:500;">RIF</dt>
                <dd><code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:2px 8px;border-radius:5px;">{{ $beneficiario->rif }}</code></dd>
                <dt style="color:var(--text-secondary);font-weight:500;">Razón Social</dt>
                <dd style="font-weight:600;">{{ $beneficiario->razon_social }}</dd>
                @if($beneficiario->nombre_comercial)
                <dt style="color:var(--text-secondary);font-weight:500;">Nombre Comercial</dt>
                <dd>{{ $beneficiario->nombre_comercial }}</dd>
                @endif
                <dt style="color:var(--text-secondary);font-weight:500;">Tipo</dt>
                <dd><span class="badge badge-blue">{{ $beneficiario->getTipoLabel() }}</span></dd>
                <dt style="color:var(--text-secondary);font-weight:500;">Teléfono</dt>
                <dd>{{ $beneficiario->telefono ?? '—' }}</dd>
                <dt style="color:var(--text-secondary);font-weight:500;">Email</dt>
                <dd>{{ $beneficiario->email ?? '—' }}</dd>
                <dt style="color:var(--text-secondary);font-weight:500;">Dirección</dt>
                <dd>{{ $beneficiario->direccion ?? '—' }}</dd>
                <dt style="color:var(--text-secondary);font-weight:500;">Estado</dt>
                <dd><span class="badge {{ $beneficiario->activo ? 'badge-active' : 'badge-danger' }}">{{ $beneficiario->activo ? 'Activo' : 'Inactivo' }}</span></dd>
            </dl>
        </div>
    </div>

    <div class="card fade-up" style="animation-delay:.05s;">
        <div class="card-header"><h3 class="card-title"><i class="fa-solid fa-building-columns"></i> Datos Bancarios</h3></div>
        <div class="card-body">
            @if($beneficiario->banco_nombre)
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:14px;">
                <dt style="color:var(--text-secondary);font-weight:500;">Banco</dt>
                <dd style="font-weight:600;">{{ $beneficiario->banco_nombre }}</dd>
                <dt style="color:var(--text-secondary);font-weight:500;">N° Cuenta</dt>
                <dd><code>{{ $beneficiario->banco_cuenta ?? '—' }}</code></dd>
                <dt style="color:var(--text-secondary);font-weight:500;">Tipo</dt>
                <dd>{{ ucfirst($beneficiario->banco_tipo_cuenta ?? '—') }}</dd>
            </dl>
            @else
            <div style="text-align:center;padding:24px;color:var(--text-secondary);">
                <i class="fa-solid fa-building-columns" style="font-size:28px;opacity:.3;margin-bottom:8px;display:block;"></i>
                Sin datos bancarios registrados
            </div>
            @endif
            @if($beneficiario->observaciones)
            <div style="margin-top:16px;padding:12px;background:rgba(79,142,247,0.05);border-radius:8px;border-left:3px solid var(--accent);">
                <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;">OBSERVACIONES</div>
                <div style="font-size:13px;">{{ $beneficiario->observaciones }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
