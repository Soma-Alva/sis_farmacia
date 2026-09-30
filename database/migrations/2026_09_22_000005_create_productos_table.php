<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->string('codigo_barra', 50)->nullable()->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('id_categoria')->nullable();
            $table->unsignedInteger('id_proveedor')->nullable();
            $table->string('concentracion', 50)->nullable();
            $table->string('presentacion', 100)->nullable();
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->decimal('precio_compra', 10, 4)->default(0);
            $table->decimal('precio_venta', 10, 4)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
