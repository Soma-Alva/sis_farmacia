<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Presentaciones de venta por paquete (ej. "Caja x100").
 * El producto sigue manejándose en unidades (stock, lotes, FEFO);
 * una presentación solo define cuántas unidades descuenta y a qué
 * precio fijo se vende el paquete completo.
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL no revierte DDL: si un intento anterior falló a medias,
        // la tabla pudo quedar creada. Se parte siempre limpio.
        Schema::dropIfExists('producto_presentaciones');

        Schema::create('producto_presentaciones', function (Blueprint $table) {
            $table->increments('id_presentacion');
            // Sin llave foránea a propósito: el producto se desactiva, no
            // se borra, y así evitamos errores de tipos incompatibles
            // (3780) entre columnas en distintas instalaciones de MySQL.
            $table->unsignedInteger('id_producto')->index();
            $table->string('nombre', 100);
            $table->unsignedInteger('unidades');
            $table->decimal('precio_venta', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_ventas', 'presentacion')) {
                return;
            }
            // Nombre de la presentación vendida (null = venta por unidad)
            $table->string('presentacion', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropColumn('presentacion');
        });
        Schema::dropIfExists('producto_presentaciones');
    }
};
