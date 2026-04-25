<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('causaciones', function (Blueprint $table) {
            // FK al compromiso que origina la causación (nullable: puede existir sin compromiso previo)
            $table->foreignId('compromiso_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('compromisos')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('causaciones', function (Blueprint $table) {
            $table->dropForeign(['compromiso_id']);
            $table->dropColumn('compromiso_id');
        });
    }
};
