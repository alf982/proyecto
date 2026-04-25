<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('icono', 60)->nullable()->default('fa-user-shield')->after('guard_name');
            $table->string('color', 20)->nullable()->default('#4f8ef7')->after('icono');
            $table->string('descripcion', 200)->nullable()->after('color');
        });

        // Pre-llenar los roles existentes con sus valores conocidos
        $defaults = [
            'super-admin'          => ['fa-crown',        '#f7b94f', 'Acceso ilimitado al sistema completo'],
            'administrador'        => ['fa-user-tie',     '#7c5cfc', 'Gestión general del sistema'],
            'analista-presupuesto' => ['fa-chart-bar',    '#4f8ef7', 'Módulo de presupuesto completo'],
            'tesorero'             => ['fa-piggy-bank',   '#22d3a6', 'Tesorería y órdenes de pago'],
            'analista-contable'    => ['fa-calculator',   '#f7b94f', 'Contabilidad y reportes financieros'],
            'jefe-compras'         => ['fa-cart-shopping','#f97316', 'Compras, almacén e inventario'],
            'jefe-bienes'          => ['fa-box-archive',  '#a78bfa', 'Control de bienes nacionales'],
            'jefe-nomina'          => ['fa-id-badge',     '#34d399', 'Nómina y personal'],
            'cajero'               => ['fa-cash-register','#fb7185', 'Cajas recaudadoras e ingresos'],
            'consultor'            => ['fa-eye',          '#8a91a8', 'Solo lectura en todos los módulos'],
        ];

        foreach ($defaults as $name => [$icono, $color, $desc]) {
            DB::table('roles')->where('name', $name)->update([
                'icono'       => $icono,
                'color'       => $color,
                'descripcion' => $desc,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['icono', 'color', 'descripcion']);
        });
    }
};
