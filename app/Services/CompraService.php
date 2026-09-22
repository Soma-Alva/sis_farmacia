<?php
namespace App\Services;

require_once __DIR__ . '/../repositories/CompraRepository.php';
require_once __DIR__ . '/../repositories/DetalleCompraRepository.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';

class CompraService
{
    private $compraRepo;
    private $detalleRepo;
    private $productoRepo;

    public function __construct()
    {
        $this->compraRepo = new CompraRepository();
        $this->detalleRepo = new DetalleCompraRepository();
        $this->productoRepo = new ProductoRepository();
    }

    public function registrarCompra($data)
    {
        $idCompra = $this->compraRepo->create($data);

        $this->detalleRepo->guardar(
            $idCompra,
            $data['detalles']
        );

        return $idCompra;
    }

    public function aprobarCompra($idCompra, $idUsuario)
    {
        $detalles = $this->detalleRepo->getByCompra($idCompra);

        foreach ($detalles as $detalle) {

            $producto = $this->productoRepo->obtenerProducto(
                $detalle['id_producto']
            );

            $nuevoStock =
                $producto['stock_actual']
                + $detalle['cantidad'];

            $this->productoRepo->actualizarStock(
                $detalle['id_producto'],
                $nuevoStock
            );

            DB::query("
                INSERT INTO movimientos_inventario
                (
                    id_producto,
                    tipo_movimiento,
                    cantidad,
                    saldo_anterior,
                    saldo_nuevo,
                    id_usuario
                )
                VALUES
                (
                    ?,
                    'ENTRADA',
                    ?,
                    ?,
                    ?,
                    ?
                )
            ",[
                $detalle['id_producto'],
                $detalle['cantidad'],
                $producto['stock_actual'],
                $nuevoStock,
                $idUsuario
            ]);
        }

        $this->compraRepo->aprobar($idCompra);
    }
}