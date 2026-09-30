<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->increments('id_movimiento');
            $table->unsignedInteger('id_producto');
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA', 'AJUSTE']);
            $table->integer('cantidad');
            $table->string('descripcion', 255)->nullable();
            $table->string('motivo', 200)->nullable();
            $table->integer('saldo_anterior')->nullable();
            $table->integer('saldo_nuevo')->nullable();
            $table->unsignedInteger('id_usuario');
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
