<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EjercicioFiscal;
use App\Models\UnidadEjecutora;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\Beneficiario;
use App\Models\Compromiso;
use App\Models\Causacion;
use App\Models\Pago;
use App\Models\User;

class PruebaIntegralSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // ── 1. EJERCICIO FISCAL ──────────────────────────────────
        $this->command->info('→ Ejercicio fiscal 2026...');
        EjercicioFiscal::where('estado', 'activo')->update(['estado' => 'cerrado']);
        $ej = EjercicioFiscal::firstOrCreate(
            ['anio' => 2026],
            ['fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31', 'estado' => 'activo']
        );
        $ej->update(['estado' => 'activo']);

        // ── 2. UNIDADES EJECUTORAS ───────────────────────────────
        $this->command->info('→ Unidades ejecutoras...');
        $ue1 = UnidadEjecutora::firstOrCreate(['codigo' => 'UE-001'],
            ['nombre' => 'Dirección de Administración',   'descripcion' => 'Unidad administrativa central', 'activo' => true]);
        $ue2 = UnidadEjecutora::firstOrCreate(['codigo' => 'UE-002'],
            ['nombre' => 'Dirección de Recursos Humanos', 'descripcion' => 'Gestión de personal',           'activo' => true]);
        $ue3 = UnidadEjecutora::firstOrCreate(['codigo' => 'UE-003'],
            ['nombre' => 'Dirección de Informática',      'descripcion' => 'Tecnología y sistemas',          'activo' => true]);

        // ── 3. PARTIDAS PRESUPUESTARIAS ──────────────────────────
        $this->command->info('→ Partidas presupuestarias...');
        $p1 = PartidaPresupuestaria::firstOrCreate(['codigo' => '4.01.01.001'], [
            'generica' => '4', 'especifica' => '01', 'subespecifica' => '01.001',
            'descripcion' => 'Sueldos y Salarios', 'tipo' => 'gasto',
            'activo' => true, 'saldo_actual' => 50000000,
        ]);
        $p2 = PartidaPresupuestaria::firstOrCreate(['codigo' => '4.01.02.001'], [
            'generica' => '4', 'especifica' => '01', 'subespecifica' => '02.001',
            'descripcion' => 'Servicios Básicos (Agua, Luz, Gas)', 'tipo' => 'gasto',
            'activo' => true, 'saldo_actual' => 20000000,
        ]);
        $p3 = PartidaPresupuestaria::firstOrCreate(['codigo' => '4.01.03.001'], [
            'generica' => '4', 'especifica' => '01', 'subespecifica' => '03.001',
            'descripcion' => 'Materiales y Suministros de Oficina', 'tipo' => 'gasto',
            'activo' => true, 'saldo_actual' => 10000000,
        ]);
        $p4 = PartidaPresupuestaria::firstOrCreate(['codigo' => '4.02.01.001'], [
            'generica' => '4', 'especifica' => '02', 'subespecifica' => '01.001',
            'descripcion' => 'Equipos de Computación', 'tipo' => 'gasto',
            'activo' => true, 'saldo_actual' => 30000000,
        ]);

        // ── 4. PROYECTOS ─────────────────────────────────────────
        $this->command->info('→ Proyectos...');
        $pry1 = ProyectoSia::firstOrCreate(['codigo' => 'PRY-2026-001'], [
            'nombre'              => 'Modernización de Infraestructura TI',
            'descripcion'         => 'Actualización de servidores y red interna',
            'ejercicio_fiscal_id' => $ej->id, 'unidad_ejecutora_id' => $ue3->id,
            'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31', 'estado' => 'activo',
        ]);
        $pry2 = ProyectoSia::firstOrCreate(['codigo' => 'PRY-2026-002'], [
            'nombre'              => 'Capacitación y Formación de Personal',
            'descripcion'         => 'Programa anual de capacitación institucional',
            'ejercicio_fiscal_id' => $ej->id, 'unidad_ejecutora_id' => $ue2->id,
            'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31', 'estado' => 'activo',
        ]);

        // ── 5. BENEFICIARIOS ─────────────────────────────────────
        $this->command->info('→ Beneficiarios...');
        $ben1 = Beneficiario::firstOrCreate(['rif' => 'G-20007763-0'], [
            'razon_social'         => 'Corpoelec',
            'tipo'                 => 'proveedor',
            'banco_nombre'         => 'Banco de Venezuela',
            'banco_cuenta'         => '0102-0123-45-0123456789',
            'banco_tipo_cuenta'    => 'corriente',
            'activo'               => true,
        ]);
        $ben2 = Beneficiario::firstOrCreate(['rif' => 'J-30123456-7'], [
            'razon_social'         => 'Tecnología Avanzada S.A.',
            'tipo'                 => 'proveedor',
            'banco_nombre'         => 'Banesco',
            'banco_cuenta'         => '0134-0456-78-9012345678',
            'banco_tipo_cuenta'    => 'corriente',
            'activo'               => true,
        ]);
        $ben3 = Beneficiario::firstOrCreate(['rif' => 'J-12345678-1'], [
            'razon_social'         => 'Papelería Central C.A.',
            'tipo'                 => 'proveedor',
            'banco_nombre'         => 'Mercantil',
            'banco_cuenta'         => '0105-0789-12-3456789012',
            'banco_tipo_cuenta'    => 'corriente',
            'activo'               => true,
        ]);

        // ── 6. COMPROMISOS → auto-generan Causación ──────────────
        $this->command->info('→ Compromisos + causaciones...');

        // COM-0001: Aprobado+Causado (tiene causación aprobada → lista para pago)
        if (!Compromiso::where('numero', 'COM-2026-0001')->exists()) {
            $com1 = Compromiso::create([
                'numero'                    => 'COM-2026-0001',
                'ejercicio_fiscal_id'       => $ej->id,
                'unidad_ejecutora_id'       => $ue3->id,
                'partida_presupuestaria_id' => $p4->id,
                'proyecto_id'               => $pry1->id,
                'beneficiario_id'           => $ben2->id,
                'beneficiario'              => $ben2->razon_social,
                'rif_beneficiario'          => $ben2->rif,
                'concepto'                  => 'Adquisición de 10 computadoras portátiles - Informática',
                'monto'                     => 180000.00,
                'fecha_compromiso'          => '2026-02-01',
                'estado'                    => 'causado',
                'fecha_aprobacion'          => '2026-02-02',
                'aprobado_por'              => $admin->id,
                'created_by'                => $admin->id,
            ]);
            $p4->decrement('saldo_actual', 180000);

            // Causación auto-generada: aprobada → lista para pago
            Causacion::create([
                'compromiso_id'             => $com1->id,
                'numero'                    => 'CAU-2026-0001',
                'ejercicio_fiscal_id'       => $ej->id,
                'unidad_ejecutora_id'       => $ue3->id,
                'partida_presupuestaria_id' => $p4->id,
                'proyecto_id'               => $pry1->id,
                'beneficiario'              => $ben2->razon_social,
                'rif_beneficiario'          => $ben2->rif,
                'tipo_documento'            => 'factura',
                'numero_documento'          => 'FAC-0087-2026',
                'fecha_documento'           => '2026-02-28',
                'descripcion_documento'     => 'Factura equipos de computación',
                'concepto'                  => 'Adquisición de 10 computadoras portátiles - Informática',
                'fecha_causacion'           => '2026-02-02',
                'monto_causado'             => 180000.00,
                'monto_retencion'           => 0,
                'estado'                    => 'aprobada',
                'fecha_aprobacion'          => '2026-02-02',
                'aprobado_por'              => $admin->id,
                'created_by'               => $admin->id,
            ]);
        }

        // COM-0002: Causado + causación pagada (pago ya procesado)
        if (!Compromiso::where('numero', 'COM-2026-0002')->exists()) {
            $com2 = Compromiso::create([
                'numero'                    => 'COM-2026-0002',
                'ejercicio_fiscal_id'       => $ej->id,
                'unidad_ejecutora_id'       => $ue1->id,
                'partida_presupuestaria_id' => $p2->id,
                'beneficiario_id'           => $ben1->id,
                'beneficiario'              => $ben1->razon_social,
                'rif_beneficiario'          => $ben1->rif,
                'concepto'                  => 'Pago energía eléctrica enero-marzo 2026',
                'monto'                     => 18500.00,
                'fecha_compromiso'          => '2026-01-15',
                'estado'                    => 'causado',
                'fecha_aprobacion'          => '2026-01-16',
                'aprobado_por'              => $admin->id,
                'created_by'               => $admin->id,
            ]);
            $p2->decrement('saldo_actual', 18500);

            $cau2 = Causacion::create([
                'compromiso_id'             => $com2->id,
                'numero'                    => 'CAU-2026-0002',
                'ejercicio_fiscal_id'       => $ej->id,
                'unidad_ejecutora_id'       => $ue1->id,
                'partida_presupuestaria_id' => $p2->id,
                'beneficiario'              => $ben1->razon_social,
                'rif_beneficiario'          => $ben1->rif,
                'tipo_documento'            => 'factura',
                'numero_documento'          => '004-2026',
                'fecha_documento'           => '2026-01-31',
                'descripcion_documento'     => 'Factura servicio eléctrico Ene-Mar 2026',
                'concepto'                  => 'Pago energía eléctrica enero-marzo 2026',
                'fecha_causacion'           => '2026-01-16',
                'monto_causado'             => 18500.00,
                'monto_retencion'           => 0,
                'estado'                    => 'pagada',
                'fecha_aprobacion'          => '2026-01-16',
                'fecha_pago'               => '2026-02-10',
                'aprobado_por'             => $admin->id,
                'created_by'              => $admin->id,
            ]);

            // Pago procesado
            Pago::create([
                'numero'              => 'PAG-2026-0001',
                'causacion_id'        => $cau2->id,
                'ejercicio_fiscal_id' => $ej->id,
                'unidad_ejecutora_id' => $ue1->id,
                'beneficiario'        => $ben1->razon_social,
                'rif_beneficiario'    => $ben1->rif,
                'tipo_pago'           => 'transferencia',
                'numero_referencia'   => 'TRF-20260210-001',
                'banco'               => $ben1->banco_nombre,
                'cuenta_bancaria'     => $ben1->banco_cuenta,
                'monto_pagado'        => 18500.00,
                'fecha_pago'          => '2026-02-10',
                'concepto'            => 'Pago energía eléctrica enero-marzo 2026',
                'estado'              => 'procesado',
                'created_by'          => $admin->id,
            ]);
        }

        // COM-0003: Borrador (no tiene causación aún)
        if (!Compromiso::where('numero', 'COM-2026-0003')->exists()) {
            Compromiso::create([
                'numero'                    => 'COM-2026-0003',
                'ejercicio_fiscal_id'       => $ej->id,
                'unidad_ejecutora_id'       => $ue2->id,
                'partida_presupuestaria_id' => $p3->id,
                'beneficiario_id'           => $ben3->id,
                'beneficiario'              => $ben3->razon_social,
                'rif_beneficiario'          => $ben3->rif,
                'concepto'                  => 'Suministros de oficina Trimestre II 2026',
                'monto'                     => 12500.00,
                'fecha_compromiso'          => '2026-03-01',
                'estado'                    => 'borrador',
                'created_by'               => $admin->id,
            ]);
            // No se descuenta saldo — es borrador
        }

        $this->command->newLine();
        $this->command->table(
            ['Módulo', 'Registros'],
            [
                ['Ejercicio Fiscal',         '2026 (activo)'],
                ['Unidades Ejecutoras',       'UE-001, UE-002, UE-003'],
                ['Partidas Presupuestarias',  '4 partidas con saldo inicial'],
                ['Proyectos',                 'PRY-2026-001, PRY-2026-002'],
                ['Beneficiarios',             'Corpoelec, Tecnología Avanzada, Papelería Central'],
                ['Compromisos',               'COM-0001 causado | COM-0002 causado | COM-0003 borrador'],
                ['Causaciones',               'CAU-0001 aprobada (pendiente pago) | CAU-0002 pagada'],
                ['Pagos',                     'PAG-0001 procesado'],
            ]
        );
    }
}
