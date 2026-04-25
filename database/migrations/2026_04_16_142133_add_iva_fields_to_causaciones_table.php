<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('causaciones', function (Blueprint $table) {
            // Monto base sin IVA (precio neto del proveedor antes de impuestos)
            $table->decimal('monto_sin_iva', 15, 2)->nullable()->after('monto_causado')
                  ->comment('Monto base de la factura sin IVA');
            // Alícuota del IVA aplicada por el proveedor (ej: 16.00)
            $table->decimal('alicuota_iva', 5, 2)->nullable()->after('monto_sin_iva')
                  ->comment('Alícuota IVA cobrada por el proveedor (ej: 16.00%)');
        });
    }

    public function down(): void
    {
        Schema::table('causaciones', function (Blueprint $table) {
            $table->dropColumn(['monto_sin_iva', 'alicuota_iva']);
        });
    }
};
