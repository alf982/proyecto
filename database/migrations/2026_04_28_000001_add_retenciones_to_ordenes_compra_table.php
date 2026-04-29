<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('ordenes_compra', function (Blueprint $table) {
            $table->decimal('monto_retencion', 18, 2)->default(0)->after('total');
            $table->decimal('monto_neto', 18, 2)->default(0)->after('monto_retencion');
        });
    }
    public function down(): void {
        Schema::table('ordenes_compra', function (Blueprint $table) {
            $table->dropColumn(['monto_retencion', 'monto_neto']);
        });
    }
};
