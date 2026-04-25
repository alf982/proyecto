<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->enum('tipo_documento', ['factura','contrato','recibo','planilla','otro'])
                  ->nullable()->after('concepto');
            $table->string('numero_documento', 60)->nullable()->after('tipo_documento');
            $table->date('fecha_documento')->nullable()->after('numero_documento');
            $table->string('descripcion_documento', 300)->nullable()->after('fecha_documento');
        });
    }

    public function down(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->dropColumn(['tipo_documento','numero_documento','fecha_documento','descripcion_documento']);
        });
    }
};
