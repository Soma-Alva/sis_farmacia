<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->increments('id_movimiento');
            $table->unsignedInteger('id_caja');
            $table->unsignedInteger('id_venta')->nullable();
            $table->unsignedInteger('id_compra')->nullable();
            $table->enum('tipo_movimiento', ['INGRESO', 'EGRESO']);
            $table->enum('forma_pago', ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'DOLARES'])->nullable();
            $table->decimal('monto', 12, 4);
            $table->string('descripcion', 200)->nullable();
            $table->text('motivo')->nullable();
            $table->decimal('saldo_anterior', 12, 4)->nullable();
            $table->decimal('saldo_actual', 12, 4)->nullable();
            $table->unsignedInteger('id_usuario');
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_caja')->references('id_caja')->on('caja');
            $table->foreign('id_venta')->references('id_venta')->on('ventas');
            $table->foreign('id_compra')->references('id_compra')->on('compras');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
