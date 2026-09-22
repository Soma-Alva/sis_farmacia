<?php
namespace App\Repositories;
require_once __DIR__ . '/../config/DB.php';

class ProductoRepository
{
    public function getAll()
    {
        $stmt = DB::query("
            SELECT *
            FROM productos
            WHERE activo = 1
            ORDER BY nombre
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarStock($idProducto, $nuevoStock)
    {
        DB::query(
            "UPDATE productos SET stock_actual = ? WHERE id_producto = ?",
            [$nuevoStock, $idProducto]
        );
    }

    public function obtenerProducto($idProducto)
    {
        $stmt = DB::query(
            "SELECT * FROM productos WHERE id_producto = ?",
            [$idProducto]
        );

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}