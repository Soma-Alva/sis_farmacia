<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja', function (Blueprint $table) {
            $table->increments('id_caja');
            $table->date('fecha_apertura');
            $table->time('hora_apertura');
            $table->date('fecha_cierre')->nullable();
            $table->time('hora_cierre')->nullable();
            $table->unsignedInteger('id_usuario_apertura');
            $table->unsignedInteger('id_usuario_cierre')->nullable();
            $table->decimal('saldo_inicial', 12, 4)->default(0);
            $table->decimal('saldo_final', 12, 4)->default(0);
            $table->decimal('saldo_contado', 12, 2)->nullable();
            $table->decimal('tipo_cambio', 10, 4)->default(36.5000);
            $table->decimal('total_ingresos', 12, 4)->default(0);
            $table->decimal('total_egresos', 12, 4)->default(0);
            $table->decimal('diferencia', 12, 4)->default(0);
            $table->enum('estado', ['ABIERTA', 'CERRADA'])->default('ABIERTA');
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_usuario_apertura')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};
