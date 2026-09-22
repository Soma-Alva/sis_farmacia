<?php
namespace App\Repositories;
require_once __DIR__ . '/../config/DB.php';

class ProveedorRepository
{
    public function getAll()
    {
        $stmt = DB::query("
            SELECT *
            FROM proveedores
            WHERE activo = 1
            ORDER BY nombre
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}