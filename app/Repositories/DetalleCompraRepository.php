<?php
namespace App\Repositories;
require_once __DIR__ . '/../config/DB.php';

class DetalleCompraRepository
{
    public function guardar($idCompra, $detalles)
    {
        foreach ($detalles as $item) {

            DB::query("
                INSERT INTO detalle_compras
                (
                    id_compra,
                    id_producto,
                    cantidad,
                    precio_unitario,
                    subtotal
                )
                VALUES (?,?,?,?,?)
            ",[
                $idCompra,
                $item['id_producto'],
                $item['cantidad'],
                $item['precio'],
                $item['subtotal']
            ]);
        }
    }

    public function getByCompra($idCompra)
    {
        $stmt = DB::query("
            SELECT
                dc.*,
                p.nombre
            FROM detalle_compras dc
            INNER JOIN productos p
                ON p.id_producto = dc.id_producto
            WHERE dc.id_compra = ?
        ",[$idCompra]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}