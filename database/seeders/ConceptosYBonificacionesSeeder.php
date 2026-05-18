<?php

namespace Database\Seeders;

use App\Models\ConceptoNomina;
use App\Models\Empleado;
use App\Models\EmpleadoBonificacion;
use Illuminate\Database\Seeder;

class ConceptosYBonificacionesSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Conceptos OBLIGATORIOS (aplican a todos automáticamente) ──────
        $obligatorios = [
            [
                'codigo'       => 'CESTA-TICKET',
                'nombre'       => 'Cesta Ticket / Bono de Alimentación',
                'tipo'         => 'asignacion',
                'calculo'      => 'fijo',
                'valor'        => 1200.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => true,
                'descripcion'  => 'Beneficio de alimentación establecido por la LOTTT. Aplica a todos los trabajadores activos.',
            ],
            [
                'codigo'       => 'PRIMA-ANTIGUEDAD',
                'nombre'       => 'Prima de Antigüedad',
                'tipo'         => 'asignacion',
                'calculo'      => 'porcentaje',
                'valor'        => 5.00, // 5% del salario base
                'aplica_a'     => 'todos',
                'es_obligatorio' => true,
                'descripcion'  => 'Prima mensual calculada como el 5% del salario base según antigüedad del trabajador.',
            ],
            [
                'codigo'       => 'SEG-SOCIAL-IVSS',
                'nombre'       => 'Aporte IVSS (Empleado)',
                'tipo'         => 'deduccion',
                'calculo'      => 'porcentaje',
                'valor'        => 4.00, // 4% del salario base
                'aplica_a'     => 'todos',
                'es_obligatorio' => true,
                'descripcion'  => 'Aporte obligatorio del trabajador al Instituto Venezolano de los Seguros Sociales (4%).',
            ],
            [
                'codigo'       => 'PARO-FORZOSO',
                'nombre'       => 'Paro Forzoso',
                'tipo'         => 'deduccion',
                'calculo'      => 'porcentaje',
                'valor'        => 0.50, // 0.5%
                'aplica_a'     => 'todos',
                'es_obligatorio' => true,
                'descripcion'  => 'Aporte obligatorio al Fondo de Paro Forzoso (0.5% del salario).',
            ],
            [
                'codigo'       => 'FAOV',
                'nombre'       => 'Fondo de Ahorro Obligatorio para la Vivienda (FAOV)',
                'tipo'         => 'deduccion',
                'calculo'      => 'porcentaje',
                'valor'        => 1.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => true,
                'descripcion'  => 'Aporte obligatorio del trabajador al FAOV (1% del salario integral).',
            ],
            // Solo para obreros
            [
                'codigo'       => 'BONO-UNIFORME',
                'nombre'       => 'Bono de Uniformes y Herramientas',
                'tipo'         => 'asignacion',
                'calculo'      => 'fijo',
                'valor'        => 350.00,
                'aplica_a'     => 'obreros',
                'es_obligatorio' => true,
                'descripcion'  => 'Asignación mensual para uniformes de trabajo y herramientas. Aplica solo al personal obrero.',
            ],
        ];

        // ── 2. Conceptos OPCIONALES (para asignaciones individuales) ─────────
        $opcionales = [
            [
                'codigo'       => 'BONO-EFICIENCIA',
                'nombre'       => 'Bono por Eficiencia y Productividad',
                'tipo'         => 'asignacion',
                'calculo'      => 'fijo',
                'valor'        => 800.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Reconocimiento económico por desempeño sobresaliente. Se asigna individualmente por RRHH.',
            ],
            [
                'codigo'       => 'BONO-TRANSPORTE',
                'nombre'       => 'Bono de Transporte Especial',
                'tipo'         => 'asignacion',
                'calculo'      => 'fijo',
                'valor'        => 450.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Subsidio de transporte para trabajadores que residen a más de 30 km de la institución.',
            ],
            [
                'codigo'       => 'BONO-RESPONSABILIDAD',
                'nombre'       => 'Prima por Responsabilidad',
                'tipo'         => 'asignacion',
                'calculo'      => 'porcentaje',
                'valor'        => 10.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Prima del 10% sobre el salario base para cargos con responsabilidad de supervisión o manejo de fondos.',
            ],
            [
                'codigo'       => 'DED-CAJA-AHORRO',
                'nombre'       => 'Deducción Caja de Ahorro',
                'tipo'         => 'deduccion',
                'calculo'      => 'porcentaje',
                'valor'        => 5.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Descuento voluntario del trabajador como aporte a la Caja de Ahorro de la institución (5%).',
            ],
            [
                'codigo'       => 'DED-SINDICATO',
                'nombre'       => 'Cuota Sindical',
                'tipo'         => 'deduccion',
                'calculo'      => 'fijo',
                'valor'        => 120.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Cuota mensual de afiliación al sindicato del trabajador público.',
            ],
            [
                'codigo'       => 'DED-PRESTAMO',
                'nombre'       => 'Cuota Préstamo Personal',
                'tipo'         => 'deduccion',
                'calculo'      => 'fijo',
                'valor'        => 600.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Descuento mensual por amortización de préstamo personal otorgado por la institución.',
            ],
            [
                'codigo'       => 'BONO-ESCOLARIDAD',
                'nombre'       => 'Bono de Escolaridad',
                'tipo'         => 'asignacion',
                'calculo'      => 'fijo',
                'valor'        => 500.00,
                'aplica_a'     => 'todos',
                'es_obligatorio' => false,
                'descripcion'  => 'Asignación anual (pagada en octubre) para cubrir útiles y matrículas de hijos en edad escolar.',
            ],
        ];

        $this->command->info('📦 Creando conceptos obligatorios...');
        foreach ($obligatorios as $datos) {
            $concepto = ConceptoNomina::firstOrCreate(
                ['codigo' => $datos['codigo']],
                $datos
            );
            $icono = $concepto->wasRecentlyCreated ? '  ✅' : '  ⏭ ';
            $this->command->line("{$icono} {$concepto->codigo} — {$concepto->nombre}");
        }

        $this->command->info('📋 Creando conceptos opcionales (individuales)...');
        $conceptosOpcionales = [];
        foreach ($opcionales as $datos) {
            $concepto = ConceptoNomina::firstOrCreate(
                ['codigo' => $datos['codigo']],
                $datos
            );
            $conceptosOpcionales[] = $concepto;
            $icono = $concepto->wasRecentlyCreated ? '  ✅' : '  ⏭ ';
            $this->command->line("{$icono} {$concepto->codigo} — {$concepto->nombre}");
        }

        // ── 3. Asignar conceptos opcionales al azar a cada empleado ─────────
        $this->command->info('🎲 Asignando conceptos individuales al azar...');

        $empleados = Empleado::where('estado', 'activo')->get();

        foreach ($empleados as $empleado) {
            // Eliminar asignaciones previas de este seeder (solo las que existan con concepto_nomina_id de los opcionales)
            $idsOpcionales = collect($conceptosOpcionales)->pluck('id')->toArray();
            EmpleadoBonificacion::where('empleado_id', $empleado->id)
                ->whereIn('concepto_nomina_id', $idsOpcionales)
                ->delete();

            // Elegir entre 1 y 3 conceptos opcionales al azar para este empleado
            $cantidad = rand(1, 3);
            $asignados = collect($conceptosOpcionales)->shuffle()->take($cantidad);

            foreach ($asignados as $concepto) {
                EmpleadoBonificacion::create([
                    'empleado_id'        => $empleado->id,
                    'concepto_nomina_id' => $concepto->id,
                    'monto'              => null, // usar cálculo del catálogo
                    'activo'             => true,
                    'observaciones'      => 'Asignado por seeder de prueba',
                    'registrado_por'     => null,
                ]);
            }

            $nombres = $asignados->pluck('codigo')->implode(', ');
            $this->command->line("  👤 {$empleado->nombre_completo} → [{$nombres}]");
        }

        $this->command->info('');
        $this->command->info('✅ Proceso completado. Conceptos y asignaciones individuales listas.');
    }
}
