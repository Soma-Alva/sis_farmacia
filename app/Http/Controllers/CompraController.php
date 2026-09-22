<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use Illuminate\Support\Facades\DB;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with([
            'proveedor',
            'usuario'
        ])->get();

        return view(
            'compras.index',
            compact('compras')
        );
    }

    public function create()
{
    $proveedores = Proveedor::where('activo', true)
        ->orderBy('nombre')
        ->get();

    $productos = Producto::where(
        'activo',
        true
    )->get();

    return view(
        'compras.create',
        compact(
            'proveedores',
            'productos'
        )
    );
}
/*
public function store(Request $request)
{
    $request->validate([
        'numero_factura' => 'required|unique:compras,numero_factura',
        'id_proveedor'   => 'required',
        'fecha_compra'   => 'required'
    ],[
        'numero_factura.required' => 'Debe ingresar un número de factura.',
        'numero_factura.unique'   => 'La factura ingresada ya existe.',
        'id_proveedor.required'   => 'Debe seleccionar un proveedor.',
        'fecha_compra.required'   => 'Debe seleccionar una fecha.'
    ]);

    Compra::create([

        'numero_factura' => $request->numero_factura,
        'id_proveedor'   => $request->id_proveedor,
        'id_usuario'     => auth()->user()->id_usuario,
        'fecha_compra'   => $request->fecha_compra,
        'subtotal'       => ($request->cantidad * $request->precio),
        'igv'            => 0,
        'total'          => ($request->cantidad * $request->precio),
        'estado'         => 'PENDIENTE'

    ]);

    return redirect()
        ->route('compras.index')
        ->with(
            'success',
            'Compra creada correctamente.'
        );
}
*/
public function store(Request $request)
{
    $request->validate([
        'numero_factura' => 'required|unique:compras,numero_factura',
        'id_proveedor' => 'required',
        'fecha_compra' => 'required'
    ]);

    DB::beginTransaction();

    try {

        $subtotal =
            $request->cantidad *
            $request->precio;

        $compra = Compra::create([

            'numero_factura' => $request->numero_factura,
            'id_proveedor'   => $request->id_proveedor,
            'id_usuario'     => auth()->user()->id_usuario,
            'fecha_compra'   => $request->fecha_compra,
            'subtotal'       => $subtotal,
            'igv'            => 0,
            'total'          => $subtotal,
            'estado'         => 'PENDIENTE'

        ]);

        DetalleCompra::create([

            'id_compra'        => $compra->id_compra,
            'id_producto'      => $request->id_producto,
            'cantidad'         => $request->cantidad,
            'precio_unitario'  => $request->precio,
            'subtotal'         => $subtotal

        ]);

        DB::commit();

        return redirect()
            ->route('compras.index')
            ->with(
                'success',
                'Compra registrada correctamente'
            );

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withErrors($e->getMessage());
    }
}

public function aprobar($id)
{
    DB::beginTransaction();

    try {

        $compra = Compra::findOrFail($id);

        $detalles = DetalleCompra::where(
            'id_compra',
            $id
        )->get();

        foreach ($detalles as $detalle) {

            $producto = Producto::findOrFail(
                $detalle->id_producto
            );

            $stockAnterior =
                $producto->stock_actual;

            $nuevoStock =
                $stockAnterior +
                $detalle->cantidad;

            $producto->update([
                'stock_actual' => $nuevoStock
            ]);
        }

        $compra->update([
            'estado' => 'APROBADA'
        ]);

        DB::commit();

        return redirect()
            ->route('compras.index')
            ->with(
                'success',
                'Compra aprobada correctamente'
            );

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withErrors($e->getMessage());
    }
}

}
