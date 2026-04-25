<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;
use App\Models\CategoriaBien;
use App\Models\ConceptoIngreso;
use App\Models\ConceptoNomina;
use App\Models\CuentaBancaria;
use App\Models\FuenteFinanciamiento;
use App\Models\UnidadEjecutora;
use App\Models\User;

class DatosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // ── 1. FUENTES DE FINANCIAMIENTO ──────────────────────────────
        $this->command->info('→ Fuentes de financiamiento...');
        $fuentes = [
            ['codigo' => '01', 'nombre' => 'Recursos Ordinarios',       'tipo' => 'ordinario'],
            ['codigo' => '02', 'nombre' => 'Recursos Propios',          'tipo' => 'propio'],
            ['codigo' => '03', 'nombre' => 'Transferencias Nacionales',  'tipo' => 'otro'],
            ['codigo' => '04', 'nombre' => 'Operaciones de Crédito',     'tipo' => 'credito_externo'],
            ['codigo' => '05', 'nombre' => 'Donaciones y Legados',       'tipo' => 'donacion'],
        ];
        foreach ($fuentes as $f) {
            FuenteFinanciamiento::firstOrCreate(['codigo' => $f['codigo']], $f + ['activo' => true]);
        }

        // ── 2. CUENTAS BANCARIAS ────────────────────────────────────
        $this->command->info('→ Cuentas bancarias...');
        $ejercicio = \App\Models\EjercicioFiscal::where('estado', 'activo')->first();

        CuentaBancaria::firstOrCreate(['numero_cuenta' => '0102-0001-00-0000000001'], [
            'codigo'          => CuentaBancaria::generarCodigo(),
            'banco'           => 'Banco de Venezuela',
            'nombre'          => 'Cuenta Principal Nómina',
            'tipo'            => 'corriente',
            'numero_cuenta'   => '0102-0001-00-0000000001',
            'firmante_1'      => 'Administrador SIA',
            'saldo_inicial'   => 500000.00,
            'saldo_actual'    => 500000.00,
            'estado'          => 'activa',
            'ejercicio_fiscal_id' => $ejercicio?->id,
        ]);
        CuentaBancaria::firstOrCreate(['numero_cuenta' => '0134-0001-00-0000000002'], [
            'codigo'          => CuentaBancaria::generarCodigo(),
            'banco'           => 'Banesco',
            'nombre'          => 'Cuenta Operativa Gastos',
            'tipo'            => 'corriente',
            'numero_cuenta'   => '0134-0001-00-0000000002',
            'firmante_1'      => 'Administrador SIA',
            'saldo_inicial'   => 250000.00,
            'saldo_actual'    => 250000.00,
            'estado'          => 'activa',
            'ejercicio_fiscal_id' => $ejercicio?->id,
        ]);

        // ── 3. CARGOS DE NÓMINA ─────────────────────────────────────
        $this->command->info('→ Cargos de nómina...');
        $cargos = [
            ['codigo' => 'DIR-001', 'nombre' => 'Director General',        'nivel' => 'directivo',     'salario_base' => 8500.00],
            ['codigo' => 'DIR-002', 'nombre' => 'Director de Área',        'nivel' => 'directivo',     'salario_base' => 7200.00],
            ['codigo' => 'PRO-001', 'nombre' => 'Profesional Especialista','nivel' => 'profesional',   'salario_base' => 5500.00],
            ['codigo' => 'PRO-002', 'nombre' => 'Abogado I',               'nivel' => 'profesional',   'salario_base' => 5000.00],
            ['codigo' => 'PRO-003', 'nombre' => 'Contador I',              'nivel' => 'profesional',   'salario_base' => 5000.00],
            ['codigo' => 'TEC-001', 'nombre' => 'Técnico Superior',        'nivel' => 'tecnico',       'salario_base' => 3800.00],
            ['codigo' => 'TEC-002', 'nombre' => 'Analista de Sistemas',    'nivel' => 'tecnico',       'salario_base' => 4200.00],
            ['codigo' => 'ADM-001', 'nombre' => 'Asistente Administrativo','nivel' => 'administrativo','salario_base' => 2800.00],
            ['codigo' => 'ADM-002', 'nombre' => 'Secretaria',              'nivel' => 'administrativo','salario_base' => 2500.00],
            ['codigo' => 'OBR-001', 'nombre' => 'Chofer',                  'nivel' => 'obrero',        'salario_base' => 2200.00],
            ['codigo' => 'OBR-002', 'nombre' => 'Vigilante',               'nivel' => 'obrero',        'salario_base' => 2100.00],
        ];
        foreach ($cargos as $c) {
            Cargo::firstOrCreate(['codigo' => $c['codigo']], $c + ['activo' => true]);
        }

        // ── 4. CONCEPTOS DE NÓMINA ──────────────────────────────────
        $this->command->info('→ Conceptos de nómina...');
        $conceptosNomina = [
            // Asignaciones
            ['codigo' => 'PRIM-HG',   'nombre' => 'Prima de Hogar',          'tipo' => 'asignacion', 'calculo' => 'porcentaje', 'valor' => 10,  'aplica_a' => 'todos',        'es_obligatorio' => false],
            ['codigo' => 'BI-ALIM',   'nombre' => 'Bono de Alimentación',    'tipo' => 'asignacion', 'calculo' => 'fijo',       'valor' => 800, 'aplica_a' => 'todos',        'es_obligatorio' => true],
            ['codigo' => 'BI-TRANSP', 'nombre' => 'Bono de Transporte',      'tipo' => 'asignacion', 'calculo' => 'fijo',       'valor' => 400, 'aplica_a' => 'todos',        'es_obligatorio' => true],
            // Deducciones
            ['codigo' => 'SS-IVSS',   'nombre' => 'Seguro Social (IVSS)',    'tipo' => 'deduccion',  'calculo' => 'porcentaje', 'valor' => 4,   'aplica_a' => 'todos',        'es_obligatorio' => true],
            ['codigo' => 'SS-RPE',    'nombre' => 'Régimen de Paro Forzoso', 'tipo' => 'deduccion',  'calculo' => 'porcentaje', 'valor' => 0.5, 'aplica_a' => 'todos',        'es_obligatorio' => true],
            ['codigo' => 'SS-FAOV',   'nombre' => 'FAOV (Ahorro Habita.)',   'tipo' => 'deduccion',  'calculo' => 'porcentaje', 'valor' => 1,   'aplica_a' => 'todos',        'es_obligatorio' => true],
            ['codigo' => 'ISLR',      'nombre' => 'ISLR',                    'tipo' => 'deduccion',  'calculo' => 'porcentaje', 'valor' => 5,   'aplica_a' => 'fijos',        'es_obligatorio' => false],
        ];
        foreach ($conceptosNomina as $c) {
            ConceptoNomina::firstOrCreate(['codigo' => $c['codigo']], $c + ['activo' => true]);
        }

        // ── 5. CONCEPTOS DE INGRESO ─────────────────────────────────
        $this->command->info('→ Conceptos de ingreso...');
        $conceptosIngreso = [
            ['codigo' => 'ING-001', 'nombre' => 'Tasas Administrativas',        'tipo' => 'tasa'],
            ['codigo' => 'ING-002', 'nombre' => 'Multas y Sanciones',           'tipo' => 'multa'],
            ['codigo' => 'ING-003', 'nombre' => 'Derechos de las Empresas',     'tipo' => 'otro'],
            ['codigo' => 'ING-004', 'nombre' => 'Servicios de Certificación',   'tipo' => 'otro'],
            ['codigo' => 'ING-005', 'nombre' => 'Constancias y Documentos',     'tipo' => 'tasa'],
            ['codigo' => 'ING-006', 'nombre' => 'Copias Certificadas',          'tipo' => 'tasa'],
            ['codigo' => 'ING-007', 'nombre' => 'Inspecciones y Auditorías',    'tipo' => 'intereses'],
        ];
        foreach ($conceptosIngreso as $c) {
            ConceptoIngreso::firstOrCreate(['codigo' => $c['codigo']], $c + ['activo' => true]);
        }

        // ── 6. CATEGORÍAS DE BIENES ─────────────────────────────────
        $this->command->info('→ Categorías de bienes...');
        $categorias = [
            ['codigo' => 'CAT-EQ-INFO', 'nombre' => 'Equipos de Computación',     'vida_util_anios' => 5,  'tasa_depreciacion' => 20.00],
            ['codigo' => 'CAT-MOB',     'nombre' => 'Mobiliario de Oficina',       'vida_util_anios' => 10, 'tasa_depreciacion' => 10.00],
            ['codigo' => 'CAT-VEH',     'nombre' => 'Vehículos',                   'vida_util_anios' => 5,  'tasa_depreciacion' => 20.00],
            ['codigo' => 'CAT-EQ-OF',   'nombre' => 'Equipos de Oficina',          'vida_util_anios' => 8,  'tasa_depreciacion' => 12.50],
            ['codigo' => 'CAT-BLDG',    'nombre' => 'Edificios e Instalaciones',   'vida_util_anios' => 30, 'tasa_depreciacion' => 3.33],
            ['codigo' => 'CAT-ELEC',    'nombre' => 'Equipos Eléctricos',          'vida_util_anios' => 10, 'tasa_depreciacion' => 10.00],
        ];
        foreach ($categorias as $c) {
            CategoriaBien::firstOrCreate(['codigo' => $c['codigo']], $c + ['activo' => true]);
        }

        $this->command->info('✅ Datos base cargados correctamente.');
        $this->command->table(
            ['Catálogo', 'Registros'],
            [
                ['Fuentes de Financiamiento', count($fuentes)],
                ['Cuentas Bancarias',          2],
                ['Cargos de Nómina',           count($cargos)],
                ['Conceptos de Nómina',        count($conceptosNomina)],
                ['Conceptos de Ingreso',       count($conceptosIngreso)],
                ['Categorías de Bienes',       count($categorias)],
            ]
        );
    }
}
