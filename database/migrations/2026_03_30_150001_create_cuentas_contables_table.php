<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->string('descripcion')->nullable();
            $table->enum('clase', ['1','2','3','4','5','6','7'])->comment('1=Activo,2=Pasivo,3=Patrimonio,4=Gastos,5=Ingresos,6=OrdenDeudora,7=OrdenAcreedora');
            $table->enum('tipo', ['activo','pasivo','patrimonio','gasto','ingreso','orden_deudora','orden_acreedora']);
            $table->enum('naturaleza', ['deudora','acreedora'])->comment('Deudora=Activos+Gastos, Acreedora=Pasivos+Patrimonio+Ingresos');
            $table->integer('nivel')->default(1)->comment('Profundidad en la jerarquía');
            $table->foreignId('parent_id')->nullable()->constrained('cuentas_contables')->nullOnDelete();
            $table->boolean('permite_movimiento')->default(false)->comment('Solo cuentas de detalle (hoja) aceptan asientos');
            $table->decimal('saldo_inicial', 18, 2)->default(0);
            $table->decimal('saldo_actual', 18, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_contables');
    }
};
