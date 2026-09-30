<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->increments('id_cuenta');
            $table->string('codigo_cuenta', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('tipo_cuenta', ['ACTIVO', 'PASIVO', 'PATRIMONIO', 'INGRESOS', 'GASTOS']);
            $table->decimal('saldo_inicial', 15, 4)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_contables');
    }
};
