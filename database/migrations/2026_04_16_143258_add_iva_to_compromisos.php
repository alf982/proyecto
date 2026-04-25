<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            // Monto base sin IVA
            $table->decimal('monto_sin_iva', 15, 2)->nullable()->after('monto')
                  ->comment('Monto base de la factura antes del IVA');
            // Alícuota del IVA cobrada por el proveedor
            $table->decimal('alicuota_iva', 5, 2)->nullable()->default(16)->after('monto_sin_iva')
                  ->comment('Alícuota IVA del proveedor (ej: 16.00%)');
        });
    }

    public function down(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->dropColumn(['monto_sin_iva', 'alicuota_iva']);
        });
    }
};
