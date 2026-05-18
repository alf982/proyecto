@props(['event'])

@php
    $config = match ($event) {
        'created'  => ['class' => 'badge-active', 'label' => 'Creado'],
        'updated'  => ['class' => 'badge-blue',   'label' => 'Actualizado'],
        'deleted'  => ['class' => 'badge-danger', 'label' => 'Eliminado'],
        'restored' => ['class' => 'badge-purple', 'label' => 'Restaurado'],
        default    => ['class' => 'badge-warn',   'label' => ucfirst($event)],
    };
@endphp

<span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
