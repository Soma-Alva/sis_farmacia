<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_compra');
            $table->unsignedInteger('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 4);
            $table->decimal('subtotal', 12, 4);

            $table->foreign('id_compra')->references('id_compra')->on('compras')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compras');
    }
};
