<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->increments('id_lote');
            $table->integer('id_producto');
            $table->integer('id_compra')->nullable();
            $table->string('numero_lote', 100)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('cantidad_inicial');
            $table->integer('cantidad_actual');
            $table->decimal('costo_unitario', 10, 4)->default(0);
            $table->enum('estado', ['ACTIVO', 'AGOTADO', 'VENCIDO', 'BLOQUEADO'])->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_compra')->references('id_compra')->on('compras')->nullOnDelete();

            // Clave para las consultas FEFO: "dame lotes de este producto,
            // activos, ordenados por el que vence primero"
            $table->index(['id_producto', 'estado', 'fecha_vencimiento'], 'idx_lotes_fefo');
            $table->index('fecha_vencimiento', 'idx_lotes_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
