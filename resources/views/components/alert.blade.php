{{-- Muestra mensajes flash de sesión: success, error, warning, info --}}

@if(session('success'))
<div class="alert alert-success fade-up" role="alert" style="display:flex;align-items:center;gap:10px;animation-delay:.02s;">
    <i class="fa-solid fa-circle-check" style="font-size:16px;flex-shrink:0;"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger fade-up" role="alert" style="display:flex;align-items:center;gap:10px;animation-delay:.02s;">
    <i class="fa-solid fa-circle-xmark" style="font-size:16px;flex-shrink:0;"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning fade-up" role="alert" style="display:flex;align-items:center;gap:10px;animation-delay:.02s;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size:16px;flex-shrink:0;"></i>
    <span>{{ session('warning') }}</span>
</div>
@endif

@if(session('info'))
<div class="alert alert-info fade-up" role="alert" style="display:flex;align-items:center;gap:10px;animation-delay:.02s;">
    <i class="fa-solid fa-circle-info" style="font-size:16px;flex-shrink:0;"></i>
    <span>{{ session('info') }}</span>
</div>
@endif

@if($errors->any() && !isset($hideErrors))
<div class="alert alert-danger fade-up" role="alert" style="animation-delay:.02s;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
        <i class="fa-solid fa-circle-xmark" style="font-size:16px;flex-shrink:0;"></i>
        <strong>Por favor corrige los siguientes errores:</strong>
    </div>
    <ul style="margin:0;padding-left:22px;">
        @foreach($errors->all() as $error)
            <li style="font-size:13px;">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
