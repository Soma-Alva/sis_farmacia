<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_asientos', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_asiento');
            $table->unsignedInteger('id_cuenta');
            $table->decimal('debe', 15, 4)->default(0);
            $table->decimal('haber', 15, 4)->default(0);
            $table->string('descripcion', 300)->nullable();

            $table->foreign('id_asiento')->references('id_asiento')->on('asientos_contables')->cascadeOnDelete();
            $table->foreign('id_cuenta')->references('id_cuenta')->on('cuentas_contables');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_asientos');
    }
};
