<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Empleado;
use App\Models\UnidadEjecutora;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadosTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── Obtener/Crear Cargos ──────────────────────────────────────
        $cargos = $this->asegurarCargos();
        $unidades = UnidadEjecutora::where('activo', true)->pluck('id')->toArray();
        $unidadPrincipal = $unidades[0] ?? null;

        // ── Definición de los 12 trabajadores de prueba ───────────────
        $trabajadores = [
            // Fijos (Personal de Planta)
            ['cedula'=>'V-12345678','nombres'=>'Carlos Eduardo','primer_apellido'=>'Ramírez','segundo_apellido'=>'Medina',
             'tipo'=>'fijo','cargo'=>'Director de Administración','banco'=>'Banco de Venezuela','cuenta'=>'01020512345678901234',
             'sexo'=>'M','fecha_ingreso'=>'2018-03-01','unidad_idx'=>0],

            ['cedula'=>'V-15678234','nombres'=>'María Alejandra','primer_apellido'=>'Flores','segundo_apellido'=>'Castillo',
             'tipo'=>'fijo','cargo'=>'Analista de Sistemas','banco'=>'Banesco','cuenta'=>'01340512376543219876',
             'sexo'=>'F','fecha_ingreso'=>'2020-06-15','unidad_idx'=>0],

            ['cedula'=>'V-10234567','nombres'=>'José Antonio','primer_apellido'=>'Hernández','segundo_apellido'=>'Pérez',
             'tipo'=>'fijo','cargo'=>'Contador Público','banco'=>'BBVA Provincial','cuenta'=>'01080512312345678901',
             'sexo'=>'M','fecha_ingreso'=>'2016-11-01','unidad_idx'=>1 % count($unidades)],

            ['cedula'=>'V-18456321','nombres'=>'Luisa Gabriela','primer_apellido'=>'Morales','segundo_apellido'=>'Torres',
             'tipo'=>'fijo','cargo'=>'Analista de Sistemas','banco'=>'Mercantil','cuenta'=>'01050512398765432109',
             'sexo'=>'F','fecha_ingreso'=>'2021-02-20','unidad_idx'=>1 % count($unidades)],

            // Contratados
            ['cedula'=>'V-23456789','nombres'=>'Andrés Felipe','primer_apellido'=>'González','segundo_apellido'=>'Díaz',
             'tipo'=>'contratado','cargo'=>'Técnico en Informática','banco'=>'Banco de Venezuela','cuenta'=>'01020512311111111111',
             'sexo'=>'M','fecha_ingreso'=>'2023-01-10','unidad_idx'=>0],

            ['cedula'=>'V-26789012','nombres'=>'Valentina','primer_apellido'=>'Rojas','segundo_apellido'=>'Méndez',
             'tipo'=>'contratado','cargo'=>'Técnico en Informática','banco'=>'Banesco','cuenta'=>'01340512322222222222',
             'sexo'=>'F','fecha_ingreso'=>'2023-07-05','unidad_idx'=>2 % count($unidades)],

            ['cedula'=>'V-21345678','nombres'=>'Ricardo Javier','primer_apellido'=>'Núñez','segundo_apellido'=>'Vargas',
             'tipo'=>'contratado','cargo'=>'Analista de Sistemas','banco'=>'BNC','cuenta'=>'01910512333333333333',
             'sexo'=>'M','fecha_ingreso'=>'2022-09-01','unidad_idx'=>2 % count($unidades)],

            // Obreros
            ['cedula'=>'V-16789234','nombres'=>'Pedro Miguel','primer_apellido'=>'Sánchez','segundo_apellido'=>'López',
             'tipo'=>'obrero','cargo'=>'Obrero General','banco'=>'Banco de Venezuela','cuenta'=>'01020512344444444444',
             'sexo'=>'M','fecha_ingreso'=>'2019-04-12','unidad_idx'=>1 % count($unidades)],

            ['cedula'=>'V-19876543','nombres'=>'Carmen Rosa','primer_apellido'=>'Martínez','segundo_apellido'=>'García',
             'tipo'=>'obrero','cargo'=>'Obrero General','banco'=>'BBVA Provincial','cuenta'=>'01080512355555555555',
             'sexo'=>'F','fecha_ingreso'=>'2020-08-30','unidad_idx'=>0],

            ['cedula'=>'V-14567890','nombres'=>'Luis Alberto','primer_apellido'=>'Jiménez','segundo_apellido'=>'Ramos',
             'tipo'=>'obrero','cargo'=>'Técnico en Informática','banco'=>'Mercantil','cuenta'=>'01050512366666666666',
             'sexo'=>'M','fecha_ingreso'=>'2017-12-01','unidad_idx'=>1 % count($unidades)],

            ['cedula'=>'V-22456789','nombres'=>'Ana Belén','primer_apellido'=>'Suárez','segundo_apellido'=>'Villalobos',
             'tipo'=>'fijo','cargo'=>'Contador Público','banco'=>'Banesco','cuenta'=>'01340512377777777777',
             'sexo'=>'F','fecha_ingreso'=>'2019-09-15','unidad_idx'=>2 % count($unidades)],

            ['cedula'=>'V-25678901','nombres'=>'Gustavo Enrique','primer_apellido'=>'Betancourt','segundo_apellido'=>'Álvarez',
             'tipo'=>'contratado','cargo'=>'Director de Administración','banco'=>'BBVA Provincial','cuenta'=>'01080512388888888888',
             'sexo'=>'M','fecha_ingreso'=>'2024-01-03','unidad_idx'=>0],
        ];

        $creados = 0;
        foreach ($trabajadores as $t) {
            // Evitar duplicados por cédula
            if (Empleado::where('cedula', $t['cedula'])->exists()) {
                $this->command->line("  ⏭  Saltando {$t['cedula']} (ya existe)");
                continue;
            }

            $cargoId = $cargos[$t['cargo']] ?? array_values($cargos)[0];
            $unidadId = $unidades[$t['unidad_idx']] ?? $unidadPrincipal;

            Empleado::create([
                'cedula'              => $t['cedula'],
                'nombres'             => $t['nombres'],
                'primer_apellido'     => $t['primer_apellido'],
                'segundo_apellido'    => $t['segundo_apellido'],
                'sexo'                => $t['sexo'],
                'cargo_id'            => $cargoId,
                'unidad_ejecutora_id' => $unidadId,
                'tipo'                => $t['tipo'],
                'estado'              => 'activo',
                'fecha_ingreso'       => $t['fecha_ingreso'],
                'banco'               => $t['banco'],
                'numero_cuenta'       => $t['cuenta'],
                'telefono'            => '0414-' . rand(1000000, 9999999),
                'email'               => strtolower($t['primer_apellido']) . '.' . strtolower($t['primer_apellido']) . rand(10,99) . '@cep.gob.ve',
                'nacionalidad'        => 'Venezolana',
                'nivel_instruccion'   => 'universitario',
                'anos_experiencia_publica' => rand(1, 10),
                'meses_experiencia_publica' => rand(0, 11),
                'anos_experiencia_privada' => rand(0, 5),
                'meses_experiencia_privada' => rand(0, 11),
                'anos_experiencia_independiente' => 0,
                'meses_experiencia_independiente' => 0,
            ]);
            $creados++;
        }

        $this->command->info("✅ Se crearon {$creados} trabajadores de prueba.");
    }

    private function asegurarCargos(): array
    {
        $definiciones = [
            ['codigo'=>'COD-DIR-01', 'nombre'=>'Director de Administración', 'nivel'=>'directivo',   'salario_base'=>4500.00, 'activo'=>true],
            ['codigo'=>'COD-CNT-01', 'nombre'=>'Contador Público',            'nivel'=>'profesional','salario_base'=>3200.00, 'activo'=>true],
            ['codigo'=>'COD-ANS-01', 'nombre'=>'Analista de Sistemas',         'nivel'=>'profesional','salario_base'=>2800.00, 'activo'=>true],
            ['codigo'=>'COD-TEC-01', 'nombre'=>'Técnico en Informática',       'nivel'=>'tecnico',    'salario_base'=>2200.00, 'activo'=>true],
            ['codigo'=>'COD-OBR-01', 'nombre'=>'Obrero General',               'nivel'=>'obrero',     'salario_base'=>1800.00, 'activo'=>true],
        ];

        $mapa = [];
        foreach ($definiciones as $def) {
            // Buscar por nombre primero para no duplicar
            $cargo = Cargo::where('nombre', $def['nombre'])->first();
            if (!$cargo) {
                // Si el código ya existe, usar un sufijo único
                $codigoFinal = $def['codigo'];
                if (Cargo::where('codigo', $codigoFinal)->exists()) {
                    $codigoFinal = $def['codigo'] . '-' . rand(10, 99);
                }
                $cargo = Cargo::create(array_merge($def, ['codigo' => $codigoFinal]));
            }
            $mapa[$def['nombre']] = $cargo->id;
        }
        return $mapa;
    }
}
