<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->increments('id_venta');
            $table->string('numero_ticket', 50)->unique();
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_cliente')->nullable();
            $table->dateTime('fecha_venta')->useCurrent();
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->decimal('igv', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->enum('metodo_pago', ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'DOLARES'])->default('EFECTIVO');
            $table->decimal('monto_pagado', 12, 4)->default(0);
            $table->decimal('cambio', 12, 4)->default(0);
            $table->enum('estado', ['PENDIENTE', 'COMPLETADA', 'ANULADA'])->default('COMPLETADA');
            $table->string('cliente_nombre', 150)->nullable();
            $table->string('cliente_dni', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_cliente', 'fk_ventas_clientes')->references('id_cliente')->on('clientes');
            $table->index('fecha_venta', 'idx_ventas_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
