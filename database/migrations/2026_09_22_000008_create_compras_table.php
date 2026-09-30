<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('id_compra');
            $table->string('numero_factura', 50)->unique();
            $table->unsignedInteger('id_proveedor');
            $table->unsignedInteger('id_usuario');
            $table->date('fecha_compra');
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->decimal('igv', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->enum('estado', ['PENDIENTE', 'APROBADA', 'CANCELADA'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->index('fecha_compra', 'idx_compras_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
