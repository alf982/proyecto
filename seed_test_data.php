<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;
use App\Models\ConciliacionBancaria;
use App\Models\EjercicioFiscal;

$ej = EjercicioFiscal::where('estado', 'activo')->first();
$ej_id = $ej ? $ej->id : null;

// ── Cuentas Bancarias ──────────────────────────────────────────────
if (CuentaBancaria::count() === 0) {
    $c1 = CuentaBancaria::create([
        'codigo'          => 'CTA-0001',
        'nombre'          => 'Cuenta Principal BDV',
        'banco'           => 'Banco de Venezuela',
        'numero_cuenta'   => '0102-0001-10-0001234567',
        'tipo'            => 'corriente',
        'moneda'          => 'VES',
        'saldo_inicial'   => 50000000.00,
        'saldo_actual'    => 50000000.00,
        'fecha_apertura'  => '2024-01-15',
        'estado'          => 'activa',
        'firmante_1'      => 'Contralor General',
        'firmante_2'      => 'Director Administrativo',
        'ejercicio_fiscal_id' => $ej_id,
        'creado_por'      => 1,
    ]);

    $c2 = CuentaBancaria::create([
        'codigo'          => 'CTA-0002',
        'nombre'          => 'Cuenta Operativa Banesco',
        'banco'           => 'Banesco',
        'numero_cuenta'   => '0134-0002-10-0002345678',
        'tipo'            => 'corriente',
        'moneda'          => 'VES',
        'saldo_inicial'   => 15000000.00,
        'saldo_actual'    => 15000000.00,
        'fecha_apertura'  => '2024-03-01',
        'estado'          => 'activa',
        'firmante_1'      => 'Contralor General',
        'ejercicio_fiscal_id' => $ej_id,
        'creado_por'      => 1,
    ]);

    $c3 = CuentaBancaria::create([
        'codigo'          => 'CTA-0003',
        'nombre'          => 'Caja Chica Mercantil',
        'banco'           => 'Mercantil',
        'numero_cuenta'   => '0105-0003-10-0003456789',
        'tipo'            => 'ahorro',
        'moneda'          => 'VES',
        'saldo_inicial'   => 500000.00,
        'saldo_actual'    => 500000.00,
        'fecha_apertura'  => '2025-01-10',
        'estado'          => 'activa',
        'ejercicio_fiscal_id' => $ej_id,
        'creado_por'      => 1,
    ]);
    echo "Cuentas creadas: " . CuentaBancaria::count() . "\n";
} else {
    $c1 = CuentaBancaria::first();
    $c2 = CuentaBancaria::skip(1)->first();
    echo "Cuentas ya existentes: " . CuentaBancaria::count() . "\n";
}

// ── Movimientos Bancarios ──────────────────────────────────────────
if (MovimientoBancario::count() === 0) {
    $movs = [
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0001', 'tipo' => 'credito',               'concepto' => 'Transferencia de fondo ONAPRE - Primer trimestre', 'monto' => 50000000.00, 'fecha_movimiento' => '2026-01-05', 'referencia' => 'TRF-ONAPRE-001', 'origen' => 'manual', 'estado' => 'conciliado', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0002', 'tipo' => 'debito',                'concepto' => 'Pago nómina enero 2026',                            'monto' =>  4200000.00, 'fecha_movimiento' => '2026-01-31', 'referencia' => 'NOM-ENE-2026',  'origen' => 'pago',   'estado' => 'conciliado', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0003', 'tipo' => 'debito',                'concepto' => 'Pago Constructora Portuguesa - Contrato 001',        'monto' =>  8500000.00, 'fecha_movimiento' => '2026-02-10', 'referencia' => 'CHQ-00123',     'beneficiario_nombre' => 'Constructora Portuguesa C.A.', 'beneficiario_rif' => 'J-12345678-9', 'origen' => 'pago', 'estado' => 'conciliado', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0004', 'tipo' => 'transferencia_salida',  'concepto' => 'Transferencia a cuenta operativa Banesco',            'monto' =>  5000000.00, 'fecha_movimiento' => '2026-02-15', 'referencia' => 'TRF-INT-001',   'banco_origen' => 'BDV',     'origen' => 'manual', 'estado' => 'conciliado', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c2->id, 'numero' => 'MOV-2026-0005', 'tipo' => 'transferencia_entrada', 'concepto' => 'Transferencia recibida desde BDV',                    'monto' =>  5000000.00, 'fecha_movimiento' => '2026-02-15', 'referencia' => 'TRF-INT-001',   'banco_origen' => 'BDV',     'origen' => 'manual', 'estado' => 'conciliado', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0006', 'tipo' => 'debito',                'concepto' => 'Pago nómina febrero 2026',                           'monto' =>  4200000.00, 'fecha_movimiento' => '2026-02-28', 'referencia' => 'NOM-FEB-2026',  'origen' => 'pago',   'estado' => 'conciliado', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0007', 'tipo' => 'debito',                'concepto' => 'Compra suministros de oficina - SURTEC',             'monto' =>   850000.00, 'fecha_movimiento' => '2026-03-05', 'referencia' => 'CHQ-00124',     'beneficiario_nombre' => 'SURTEC S.A.', 'beneficiario_rif' => 'J-98765432-1', 'origen' => 'pago', 'estado' => 'pendiente', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c2->id, 'numero' => 'MOV-2026-0008', 'tipo' => 'debito',                'concepto' => 'Pago servicios públicos marzo',                      'monto' =>   320000.00, 'fecha_movimiento' => '2026-03-10', 'referencia' => 'SRV-MAR-001',   'origen' => 'manual', 'estado' => 'pendiente', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0009', 'tipo' => 'nota_credito',          'concepto' => 'Reintegro por pago duplicado - enero',                'monto' =>   150000.00, 'fecha_movimiento' => '2026-03-15', 'referencia' => 'NC-BDV-001',    'origen' => 'manual', 'estado' => 'pendiente', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
        ['cuenta_bancaria_id' => $c1->id, 'numero' => 'MOV-2026-0010', 'tipo' => 'debito',                'concepto' => 'Pago nómina marzo 2026',                             'monto' =>  4200000.00, 'fecha_movimiento' => '2026-03-28', 'referencia' => 'NOM-MAR-2026',  'origen' => 'pago',   'estado' => 'pendiente', 'ejercicio_fiscal_id' => $ej_id, 'creado_por' => 1],
    ];

    foreach ($movs as $m) {
        MovimientoBancario::create($m);
    }

    // Actualizar saldos
    // c1: +50M -4.2M -8.5M -5M -4.2M -0.85M +0.15M -4.2M = 23.2M
    $c1->update(['saldo_actual' => 23200000.00]);
    // c2: +5M -0.32M = 4.68M
    $c2->update(['saldo_actual' => 4680000.00]);

    echo "Movimientos creados: " . MovimientoBancario::count() . "\n";
} else {
    echo "Movimientos ya existentes: " . MovimientoBancario::count() . "\n";
}

// ── Conciliación Bancaria ──────────────────────────────────────────
if (ConciliacionBancaria::count() === 0) {
    ConciliacionBancaria::create([
        'numero'              => 'CON-2026-01',
        'cuenta_bancaria_id'  => $c1->id,
        'ejercicio_fiscal_id' => $ej_id,
        'anio'                => 2026,
        'mes'                 => 1,
        'fecha_desde'         => '2026-01-01',
        'fecha_hasta'         => '2026-01-31',
        'saldo_segun_banco'   => 41600000.00,
        'saldo_segun_libros'  => 45800000.00,
        'diferencia'          => -4200000.00,
        'estado'              => 'aprobada',
        'fecha_aprobacion'    => '2026-02-03',
        'aprobado_por'        => 1,
        'observaciones'       => 'Conciliación enero 2026. Diferencia por cheque en tránsito NOM-ENE-2026.',
        'creado_por'          => 1,
    ]);

    ConciliacionBancaria::create([
        'numero'              => 'CON-2026-02',
        'cuenta_bancaria_id'  => $c1->id,
        'ejercicio_fiscal_id' => $ej_id,
        'anio'                => 2026,
        'mes'                 => 2,
        'fecha_desde'         => '2026-02-01',
        'fecha_hasta'         => '2026-02-28',
        'saldo_segun_banco'   => 23200000.00,
        'saldo_segun_libros'  => 23200000.00,
        'diferencia'          => 0.00,
        'estado'              => 'borrador',
        'observaciones'       => 'Conciliación febrero 2026. Pendiente de aprobación.',
        'creado_por'          => 1,
    ]);

    echo "Conciliaciones creadas: " . ConciliacionBancaria::count() . "\n";
} else {
    echo "Conciliaciones ya existentes: " . ConciliacionBancaria::count() . "\n";
}

echo "\n✅ RESUMEN FINAL:\n";
echo "  Beneficiarios:    " . \App\Models\Beneficiario::count() . "\n";
echo "  Cuentas:          " . CuentaBancaria::count() . "\n";
echo "  Movimientos:      " . MovimientoBancario::count() . "\n";
echo "  Conciliaciones:   " . ConciliacionBancaria::count() . "\n";
echo "  Compromisos:      " . \App\Models\Compromiso::count() . "\n";
echo "  Causaciones:      " . \App\Models\Causacion::count() . "\n";
echo "  Pagos:            " . \App\Models\Pago::count() . "\n";
echo "\n";
