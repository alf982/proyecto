<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('retenciones', function (Blueprint $table) {
            // Alícuota del IVA que ya viene incluida en la factura (ej: 16.00)
            $table->decimal('alicuota_iva', 5, 2)->nullable()->after('porcentaje')
                  ->comment('Alícuota IVA incluida en la factura. Sólo para tipo porcentaje_iva');
        });

        // Actualizar la columna tipo para incluir el nuevo valor
        // MySQL/MariaDB: modificar ENUM
        \DB::statement("ALTER TABLE retenciones MODIFY tipo ENUM('porcentaje','monto_fijo','porcentaje_iva') NOT NULL DEFAULT 'porcentaje'");
    }

    public function down(): void
    {
        Schema::table('retenciones', function (Blueprint $table) {
            $table->dropColumn('alicuota_iva');
        });
        \DB::statement("ALTER TABLE retenciones MODIFY tipo ENUM('porcentaje','monto_fijo') NOT NULL DEFAULT 'porcentaje'");
    }
};
