<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EjercicioFiscal;
use App\Models\UnidadEjecutora;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\CreditoPresupuestario;
use App\Models\MovimientoPartida;
use App\Models\Compromiso;
use App\Models\Causacion;
use App\Models\Pago;
use App\Models\User;

class PresupuestoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin->id;

        // ── 1. LIMPIAR TABLAS (respetando FK) ─────────────────────
        $this->command->info('→ Limpiando tablas presupuestarias...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('pagos')->delete();
        DB::table('causaciones')->delete();
        DB::table('compromisos')->delete();
        DB::table('movimientos_partidas')->delete();
        DB::table('creditos_presupuestarios')->delete();
        DB::table('partidas_presupuestarias')->delete();
        DB::table('proyectos_sia')->delete();
        DB::table('ejercicios_fiscales')->delete();
        DB::table('unidades_ejecutoras')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('  ✓ Tablas vaciadas.');

        // ── 2. EJERCICIO FISCAL ACTIVO ────────────────────────────
        $this->command->info('→ Ejercicio fiscal 2026 (activo)...');
        $ej = EjercicioFiscal::create([
            'anio'        => 2026,
            'fecha_inicio'=> '2026-01-01',
            'fecha_fin'   => '2026-12-31',
            'estado'      => 'activo',
            'creado_por'  => $adminId,
        ]);

        // ── 3. UNIDADES EJECUTORAS ────────────────────────────────
        $this->command->info('→ Unidades ejecutoras...');
        $ue1 = UnidadEjecutora::create(['codigo'=>'UE-001','nombre'=>'Dirección de Administración',   'descripcion'=>'Unidad administrativa central',   'activo'=>true]);
        $ue2 = UnidadEjecutora::create(['codigo'=>'UE-002','nombre'=>'Dirección de Recursos Humanos', 'descripcion'=>'Gestión de personal',             'activo'=>true]);
        $ue3 = UnidadEjecutora::create(['codigo'=>'UE-003','nombre'=>'Dirección de Informática',      'descripcion'=>'Tecnología y sistemas',            'activo'=>true]);

        // ── 4. PARTIDAS PRESUPUESTARIAS (saldo_actual = 0 inicial) ─
        $this->command->info('→ Partidas presupuestarias...');
        $p1 = PartidaPresupuestaria::create([
            'codigo'=>'4.01.01.001','generica'=>'4','especifica'=>'01','subespecifica'=>'01.001',
            'descripcion'=>'Sueldos y Salarios','saldo_actual'=>0,'activo'=>true,
        ]);
        $p2 = PartidaPresupuestaria::create([
            'codigo'=>'4.01.02.001','generica'=>'4','especifica'=>'01','subespecifica'=>'02.001',
            'descripcion'=>'Servicios Básicos (Agua, Luz, Gas)','saldo_actual'=>0,'activo'=>true,
        ]);
        $p3 = PartidaPresupuestaria::create([
            'codigo'=>'4.01.03.001','generica'=>'4','especifica'=>'01','subespecifica'=>'03.001',
            'descripcion'=>'Materiales y Suministros de Oficina','saldo_actual'=>0,'activo'=>true,
        ]);
        $p4 = PartidaPresupuestaria::create([
            'codigo'=>'4.02.01.001','generica'=>'4','especifica'=>'02','subespecifica'=>'01.001',
            'descripcion'=>'Equipos de Computación','saldo_actual'=>0,'activo'=>true,
        ]);
        $p5 = PartidaPresupuestaria::create([
            'codigo'=>'4.03.01.001','generica'=>'4','especifica'=>'03','subespecifica'=>'01.001',
            'descripcion'=>'Servicios de Consultoría y Asesoría','saldo_actual'=>0,'activo'=>true,
        ]);

        // ── 5. PROYECTOS ──────────────────────────────────────────
        $this->command->info('→ Proyectos...');
        $pry1 = ProyectoSia::create([
            'codigo'=>'PRY-2026-001','nombre'=>'Modernización de Infraestructura TI',
            'descripcion'=>'Actualización de servidores y red interna',
            'ejercicio_fiscal_id'=>$ej->id,'unidad_ejecutora_id'=>$ue3->id,
            'fecha_inicio'=>'2026-01-01','fecha_fin'=>'2026-12-31','estado'=>'activo',
        ]);
        $pry2 = ProyectoSia::create([
            'codigo'=>'PRY-2026-002','nombre'=>'Capacitación y Formación de Personal',
            'descripcion'=>'Programa anual de capacitación institucional',
            'ejercicio_fiscal_id'=>$ej->id,'unidad_ejecutora_id'=>$ue2->id,
            'fecha_inicio'=>'2026-01-01','fecha_fin'=>'2026-12-31','estado'=>'activo',
        ]);

        // ── 6. CRÉDITOS + MOVIMIENTOS DE ASIGNACIÓN ───────────────
        $this->command->info('→ Créditos presupuestarios con asignaciones...');

        // Cada crédito genera automáticamente su MovimientoPartida de asignación
        $creditos = [
            // [$partida, $unidad, $aprobado, $modificado]
            [$p1, $ue1, 5_000_000.00,          0, 'Sueldos personal UE-001'],
            [$p2, $ue1,   350_000.00,   50_000.00, 'Servicios básicos UE-001'],
            [$p3, $ue1,   200_000.00,  -40_000.00, 'Materiales oficina UE-001'],
            [$p4, $ue3,   800_000.00,  200_000.00, 'Equipos de cómputo UE-003'],
            [$p5, $ue3,   500_000.00,          0, 'Consultoría TI UE-003'],
            [$p1, $ue2, 3_000_000.00,          0, 'Sueldos personal UE-002'],
            [$p2, $ue2,   180_000.00,          0, 'Servicios básicos UE-002'],
        ];

        // Acumular saldos por partida (puede haber múltiples créditos por partida)
        $saldosPorPartida = [];
        $creditosObjs = [];
        $anio = 2026;

        foreach ($creditos as [$partida, $unidad, $aprobado, $modificado, $obs]) {
            $cr = CreditoPresupuestario::create([
                'ejercicio_fiscal_id'       => $ej->id,
                'partida_presupuestaria_id' => $partida->id,
                'unidad_ejecutora_id'       => $unidad->id,
                'monto_aprobado'            => $aprobado,
                'monto_modificado'          => $modificado,
                'monto_comprometido'        => 0,
                'monto_causado'             => 0,
                'monto_pagado'              => 0,
                'observaciones'             => $obs,
                'creado_por'                => $adminId,
            ]);

            $vigente = $aprobado + $modificado;
            $pid = $partida->id;

            $saldoAnt  = $saldosPorPartida[$pid] ?? 0;
            $saldoPost = $saldoAnt + $vigente;

            MovimientoPartida::create([
                'numero'                    => MovimientoPartida::generarNumero($anio),
                'partida_presupuestaria_id' => $partida->id,
                'ejercicio_fiscal_id'       => $ej->id,
                'tipo'                      => 'asignacion',
                'concepto'                  => "Asignación inicial — {$obs}",
                'monto'                     => $vigente,
                'fecha_movimiento'          => '2026-01-02',
                'referencia'                => 'ASIG-' . $cr->id,
                'saldo_anterior'            => $saldoAnt,
                'saldo_posterior'           => $saldoPost,
                'estado'                    => 'confirmado',
                'observaciones'             => 'Asignación inicial del ejercicio 2026.',
                'creado_por'                => $adminId,
            ]);

            $saldosPorPartida[$pid] = $saldoPost;
            $partida->update(['saldo_actual' => $saldoPost]);
            $creditosObjs[] = $cr;
        }

        // Destructuring para usar en compromisos/causaciones
        [$cr1, $cr2, $cr3, $cr4, $cr5, $cr6, $cr7] = $creditosObjs;
        [$cr1v, $cr2v, $cr3v, $cr4v, $cr5v, $cr6v, $cr7v] = array_map(
            fn($c) => (float)$c->monto_aprobado + (float)$c->monto_modificado,
            $creditosObjs
        );

        $this->command->info('  ✓ ' . count($creditosObjs) . ' créditos creados con asignación.');

        // ── 7. COMPROMISOS ────────────────────────────────────────
        $this->command->info('→ Compromisos...');

        // COM-001: Consultoría (Cr5 - UE-003, p5)
        $com1 = Compromiso::create([
            'numero'=>'COM-2026-0001','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue3->id,'credito_presupuestario_id'=>$cr5->id,
            'partida_presupuestaria_id'=>$p5->id,'proyecto_id'=>$pry1->id,
            'beneficiario'=>'Consultores Tech C.A.','rif_beneficiario'=>'J-29876543-2',
            'concepto'=>'Servicio de consultoría en infraestructura de redes Q1 2026',
            'monto'=>120_000.00,'fecha_compromiso'=>'2026-01-20','estado'=>'aprobado',
            'fecha_aprobacion'=>'2026-01-21','aprobado_por'=>$adminId,'created_by'=>$adminId,
        ]);
        $cr5->increment('monto_comprometido', 120_000);

        // COM-002: Equipos (Cr4 - UE-003, p4)
        $com2 = Compromiso::create([
            'numero'=>'COM-2026-0002','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue3->id,'credito_presupuestario_id'=>$cr4->id,
            'partida_presupuestaria_id'=>$p4->id,'proyecto_id'=>$pry1->id,
            'beneficiario'=>'Tecnología Avanzada S.A.','rif_beneficiario'=>'J-30123456-7',
            'concepto'=>'Adquisición de 10 computadoras portátiles Core i7',
            'monto'=>650_000.00,'fecha_compromiso'=>'2026-02-01','estado'=>'aprobado',
            'fecha_aprobacion'=>'2026-02-02','aprobado_por'=>$adminId,'created_by'=>$adminId,
        ]);
        $cr4->increment('monto_comprometido', 650_000);

        // COM-003: Suministros en borrador (Cr3 - UE-001, p3)
        $com3 = Compromiso::create([
            'numero'=>'COM-2026-0003','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue1->id,'credito_presupuestario_id'=>$cr3->id,
            'partida_presupuestaria_id'=>$p3->id,
            'beneficiario'=>'Papelería Central C.A.','rif_beneficiario'=>'J-12345678-1',
            'concepto'=>'Suministros de oficina – Trimestre I 2026',
            'monto'=>60_000.00,'fecha_compromiso'=>'2026-03-01','estado'=>'aprobado',
            'fecha_aprobacion'=>'2026-03-01','aprobado_por'=>$adminId,'created_by'=>$adminId,
        ]);
        $cr3->increment('monto_comprometido', 60_000);

        // ── 8. CAUSACIONES ────────────────────────────────────────
        $this->command->info('→ Causaciones...');

        // CAU-001: Servicio básico facturado (Cr2 - UE-001, p2)
        $cau1 = Causacion::create([
            'numero'=>'CAU-2026-0001','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue1->id,'credito_presupuestario_id'=>$cr2->id,
            'partida_presupuestaria_id'=>$p2->id,
            'beneficiario'=>'Corpoelec','rif_beneficiario'=>'G-20007763-0',
            'tipo_documento'=>'factura','numero_documento'=>'CORP-0042-2026',
            'fecha_documento'=>'2026-01-31',
            'descripcion_documento'=>'Energía eléctrica enero 2026',
            'concepto'=>'Pago energía eléctrica enero 2026',
            'monto_causado'=>85_000.00,'monto_retencion'=>0,
            'fecha_causacion'=>'2026-02-05','estado'=>'pagada',
            'fecha_aprobacion'=>'2026-02-06','fecha_pago'=>'2026-02-10',
            'aprobado_por'=>$adminId,'created_by'=>$adminId,
        ]);
        $cr2->increment('monto_comprometido', 85_000);
        $cr2->increment('monto_causado', 85_000);
        $cr2->increment('monto_pagado', 85_000);

        // CAU-002: Equipos TI (Cr4 - UE-003, p4) — aprobada, pendiente de pago
        $cau2 = Causacion::create([
            'numero'=>'CAU-2026-0002','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue3->id,'credito_presupuestario_id'=>$cr4->id,
            'partida_presupuestaria_id'=>$p4->id,'proyecto_id'=>$pry1->id,
            'beneficiario'=>'Tecnología Avanzada S.A.','rif_beneficiario'=>'J-30123456-7',
            'tipo_documento'=>'factura','numero_documento'=>'TAF-0087-2026',
            'fecha_documento'=>'2026-02-28',
            'descripcion_documento'=>'Equipos de computación OC-2026-002',
            'concepto'=>'Adquisición 10 laptops Core i7 según COM-2026-0002',
            'monto_causado'=>620_000.00,'monto_retencion'=>62_000.00,
            'fecha_causacion'=>'2026-03-05','estado'=>'aprobada',
            'fecha_aprobacion'=>'2026-03-06',
            'aprobado_por'=>$adminId,'created_by'=>$adminId,
        ]);
        $cr4->increment('monto_causado', 620_000);

        // CAU-003: Nómina (Cr1 - UE-001, p1) — borrador
        $cau3 = Causacion::create([
            'numero'=>'CAU-2026-0003','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue1->id,'credito_presupuestario_id'=>$cr1->id,
            'partida_presupuestaria_id'=>$p1->id,
            'beneficiario'=>'Nómina UE-001','rif_beneficiario'=>'',
            'tipo_documento'=>'nomina','numero_documento'=>'NOM-03-2026-01',
            'fecha_documento'=>'2026-03-15',
            'descripcion_documento'=>'Nómina quincenal 01-15 marzo 2026',
            'concepto'=>'Nómina quincenal personal administrativo Q1-marzo-2026',
            'monto_causado'=>980_000.00,'monto_retencion'=>98_000.00,
            'fecha_causacion'=>'2026-03-16','estado'=>'borrador',
            'created_by'=>$adminId,
        ]);
        $cr1->increment('monto_comprometido', 980_000);

        // CAU-004: Consultoría aprobada (Cr5 - UE-003, p5)
        $cau4 = Causacion::create([
            'numero'=>'CAU-2026-0004','ejercicio_fiscal_id'=>$ej->id,
            'unidad_ejecutora_id'=>$ue3->id,'credito_presupuestario_id'=>$cr5->id,
            'partida_presupuestaria_id'=>$p5->id,'proyecto_id'=>$pry1->id,
            'beneficiario'=>'Consultores Tech C.A.','rif_beneficiario'=>'J-29876543-2',
            'tipo_documento'=>'factura','numero_documento'=>'CTC-0015-2026',
            'fecha_documento'=>'2026-03-31',
            'descripcion_documento'=>'Servicio consultoría redes Q1 2026',
            'concepto'=>'Consultoría infraestructura de redes Q1-2026',
            'monto_causado'=>115_000.00,'monto_retencion'=>11_500.00,
            'fecha_causacion'=>'2026-04-02','estado'=>'aprobada',
            'fecha_aprobacion'=>'2026-04-03',
            'aprobado_por'=>$adminId,'created_by'=>$adminId,
        ]);
        $cr5->increment('monto_causado', 115_000);

        // ── 9. PAGOS ──────────────────────────────────────────────
        $this->command->info('→ Pagos...');

        $pag1 = Pago::create([
            'numero'=>'PAG-2026-0001','causacion_id'=>$cau1->id,
            'ejercicio_fiscal_id'=>$ej->id,'unidad_ejecutora_id'=>$ue1->id,
            'beneficiario'=>'Corpoelec','rif_beneficiario'=>'G-20007763-0',
            'tipo_pago'=>'transferencia','numero_referencia'=>'TRF-20260210-001',
            'banco'=>'Banco de Venezuela','cuenta_bancaria'=>'0102-0123-45-0123456789',
            'monto_pagado'=>85_000.00,'fecha_pago'=>'2026-02-10',
            'concepto'=>'Pago energía eléctrica enero 2026 — CAU-2026-0001',
            'estado'=>'procesado','created_by'=>$adminId,
        ]);

        $pag2 = Pago::create([
            'numero'=>'PAG-2026-0002','causacion_id'=>$cau2->id,
            'ejercicio_fiscal_id'=>$ej->id,'unidad_ejecutora_id'=>$ue3->id,
            'beneficiario'=>'Tecnología Avanzada S.A.','rif_beneficiario'=>'J-30123456-7',
            'tipo_pago'=>'cheque','numero_referencia'=>'CHK-00123456','banco'=>'Banesco',
            'monto_pagado'=>558_000.00,'fecha_pago'=>'2026-04-05',
            'concepto'=>'Pago equipos computación CAU-2026-0002 (neto retención)',
            'estado'=>'pendiente','created_by'=>$adminId,
        ]);

        // ── RESUMEN FINAL ─────────────────────────────────────────
        $this->command->newLine();
        $this->command->table(
            ['Módulo', 'Detalles'],
            [
                ['Ejercicio Fiscal',        '2026 — ACTIVO'],
                ['Unidades Ejecutoras',     'UE-001 Admin | UE-002 RRHH | UE-003 IT'],
                ['Partidas',                '4.01.01.001 Sueldos | 4.01.02.001 Servicios | 4.01.03.001 Materiales | 4.02.01.001 Equipos | 4.03.01.001 Consultoría'],
                ['Créditos Presupuestarios','7 créditos distribuidos en 5 partidas (con movimiento de asignación)'],
                ['Compromisos',             '3 — COM-001 ✓ | COM-002 ✓ | COM-003 ✓'],
                ['Causaciones',             '4 — CAU-001 pagada | CAU-002 aprobada | CAU-003 borrador | CAU-004 aprobada'],
                ['Pagos',                   '2 — PAG-001 procesado | PAG-002 pendiente'],
            ]
        );

        $this->command->info('✅ Base de datos presupuestaria sincronizada correctamente.');
    }
}
