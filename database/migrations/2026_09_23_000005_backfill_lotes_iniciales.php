<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migración de DATOS (no de esquema): crea un "lote inicial" por cada
 * producto activo que ya tiene stock, usando ese stock_actual como
 * cantidad. Sin esto, el stock que ya existe hoy no tendría ningún lote
 * del cual descontar una vez que la lógica de ventas empiece a exigir
 * id_lote.
 *
 * No se conoce la fecha de vencimiento real de ese stock histórico
 * (nunca se registró), así que queda NULL a propósito. Es trabajo
 * manual pendiente: el farmacéutico debería revisar físicamente esos
 * lotes iniciales y completar la fecha de vencimiento cuanto antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $productos = DB::table('productos')
            ->where('activo', 1)
            ->where('stock_actual', '>', 0)
            ->get(['id_producto', 'stock_actual', 'precio_compra']);

        $ahora = now();

        foreach ($productos as $producto) {
            DB::table('lotes')->insert([
                'id_producto'      => $producto->id_producto,
                'id_compra'        => null,
                'numero_lote'      => 'INICIAL-' . $producto->id_producto,
                'fecha_vencimiento'=> null, // pendiente de completar manualmente
                'cantidad_inicial' => $producto->stock_actual,
                'cantidad_actual'  => $producto->stock_actual,
                'costo_unitario'   => $producto->precio_compra,
                'estado'           => 'ACTIVO',
                'creado_en'        => $ahora,
                'actualizado_en'   => $ahora,
            ]);
        }
    }

    public function down(): void
    {
        // Solo revierte los lotes que esta migración creó (numero_lote
        // con el prefijo INICIAL-), para no borrar lotes reales
        // creados después por compras.
        DB::table('lotes')->where('numero_lote', 'like', 'INICIAL-%')->delete();
    }
};
