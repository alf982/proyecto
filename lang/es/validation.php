<?php

return [

    /*
    |----------------------------------------------------------------------
    | Validation Language Lines
    |----------------------------------------------------------------------
    */

    'accepted'             => 'El campo :attribute debe ser aceptado.',
    'active_url'           => 'El campo :attribute no es una URL válida.',
    'after'                => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal'       => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha'                => 'El campo :attribute sólo puede contener letras.',
    'alpha_dash'           => 'El campo :attribute sólo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num'            => 'El campo :attribute sólo puede contener letras y números.',
    'array'                => 'El campo :attribute debe ser un arreglo.',
    'before'               => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal'      => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between'              => [
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file'    => 'El campo :attribute debe ser entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe ser entre :min y :max.',
        'string'  => 'El campo :attribute debe ser entre :min y :max caracteres.',
    ],
    'boolean'              => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'            => 'La confirmación de :attribute no coincide.',
    'date'                 => 'El campo :attribute no es una fecha válida.',
    'date_equals'          => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format'          => 'El campo :attribute no coincide con el formato :format.',
    'different'            => 'El campo :attribute y :other deben ser diferentes.',
    'digits'               => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between'       => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'email'                => 'El campo :attribute debe ser una dirección de correo válida.',
    'exists'               => 'El :attribute seleccionado no es válido.',
    'file'                 => 'El campo :attribute debe ser un archivo.',
    'filled'               => 'El campo :attribute debe tener un valor.',
    'gt'                   => [
        'array'   => 'El campo :attribute debe tener más de :value elementos.',
        'file'    => 'El campo :attribute debe ser mayor de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string'  => 'El campo :attribute debe ser mayor que :value caracteres.',
    ],
    'image'                => 'El campo :attribute debe ser una imagen.',
    'in'                   => 'El :attribute seleccionado no es válido.',
    'integer'              => 'El campo :attribute debe ser un número entero.',
    'ip'                   => 'El campo :attribute debe ser una dirección IP válida.',
    'json'                 => 'El campo :attribute debe ser una cadena JSON válida.',
    'max'                  => [
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
        'file'    => 'El campo :attribute no debe ser mayor de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor de :max.',
        'string'  => 'El campo :attribute no debe ser mayor de :max caracteres.',
    ],
    'min'                  => [
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
        'file'    => 'El campo :attribute debe ser al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string'  => 'El campo :attribute debe ser al menos :min caracteres.',
    ],
    'not_in'               => 'El :attribute seleccionado no es válido.',
    'numeric'              => 'El campo :attribute debe ser un número.',
    'present'              => 'El campo :attribute debe estar presente.',
    'regex'                => 'El formato del campo :attribute no es válido.',
    'required'             => 'El campo :attribute es obligatorio.',
    'required_if'          => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless'      => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all'    => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without'     => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values están presentes.',
    'same'                 => 'El campo :attribute y :other deben coincidir.',
    'size'                 => [
        'array'   => 'El campo :attribute debe contener :size elementos.',
        'file'    => 'El campo :attribute debe ser de :size kilobytes.',
        'numeric' => 'El campo :attribute debe ser :size.',
        'string'  => 'El campo :attribute debe tener :size caracteres.',
    ],
    'string'               => 'El campo :attribute debe ser una cadena de caracteres.',
    'timezone'             => 'El campo :attribute debe ser una zona horaria válida.',
    'unique'               => 'El campo :attribute ya ha sido registrado.',
    'url'                  => 'El formato del campo :attribute no es válido.',
    'uuid'                 => 'El campo :attribute debe ser un UUID válido.',

    'attributes' => [
        'name'                  => 'nombre',
        'email'                 => 'correo electrónico',
        'password'              => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'rif'                   => 'RIF',
        'razon_social'          => 'razón social',
        'monto'                 => 'monto',
        'fecha'                 => 'fecha',
        'descripcion'           => 'descripción',
        'concepto'              => 'concepto',
    ],

];
