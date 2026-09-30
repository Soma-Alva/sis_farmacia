<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW vista_inventario AS
            SELECT p.id_producto, p.nombre, p.stock_actual, p.stock_minimo, p.precio_venta,
                CASE
                    WHEN p.stock_actual <= p.stock_minimo THEN 'CRÍTICO'
                    WHEN p.stock_actual <= p.stock_minimo * 2 THEN 'BAJO'
                    ELSE 'NORMAL'
                END AS estado_stock
            FROM productos p
            WHERE p.activo = 1
        ");

        DB::statement("
            CREATE VIEW vista_ventas_diarias AS
            SELECT CAST(v.fecha_venta AS DATE) AS fecha,
                COUNT(0) AS total_ventas,
                SUM(v.total) AS total_ventas_monto
            FROM ventas v
            WHERE v.estado = 'COMPLETADA'
            GROUP BY CAST(v.fecha_venta AS DATE)
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vista_inventario');
        DB::statement('DROP VIEW IF EXISTS vista_ventas_diarias');
    }
};
