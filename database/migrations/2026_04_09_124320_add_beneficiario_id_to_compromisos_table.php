<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->foreignId('beneficiario_id')
                  ->nullable()
                  ->after('rif_beneficiario')
                  ->constrained('beneficiarios')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->dropForeign(['beneficiario_id']);
            $table->dropColumn('beneficiario_id');
        });
    }
};
