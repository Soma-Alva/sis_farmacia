<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->increments('id_asiento');
            $table->date('fecha_asiento');
            $table->string('descripcion', 300)->nullable();
            $table->unsignedInteger('id_usuario');
            $table->decimal('total_debe', 15, 4)->default(0);
            $table->decimal('total_haber', 15, 4)->default(0);
            $table->enum('estado', ['BORRADOR', 'CONTABILIZADO', 'ANULADO'])->default('BORRADOR');
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos_contables');
    }
};
