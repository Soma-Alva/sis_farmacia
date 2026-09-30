<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
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
        ])->orderByDesc('id_compra')->get();

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

        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'compras.create',
            compact('proveedores', 'productos')
        );
    }

    public function show($id)
    {
        $compra = Compra::with([
            'proveedor',
            'usuario',
            'detalles.producto'
        ])->findOrFail($id);

        return view('compras.show', compact('compra'));
    }

    /**
     * Registra una compra con una o varias lineas de producto.
     * Cada linea puede traer su propio numero de lote y fecha de
     * vencimiento (se piden aqui porque es cuando el usuario tiene la
     * factura fisica en la mano), pero el lote real en la tabla
     * `lotes` no se crea todavia: eso pasa al aprobar() la compra,
     * que es el momento en que ese stock entra de verdad al sistema.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numero_factura'            => 'required|unique:compras,numero_factura',
            'id_proveedor'              => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'              => 'required|date',
            'id_producto'               => 'required|array|min:1',
            'id_producto.*'             => 'required|exists:productos,id_producto',
            'cantidad'                  => 'required|array|min:1',
            'cantidad.*'                => 'required|integer|min:1',
            'precio'                    => 'required|array|min:1',
            'precio.*'                  => 'required|numeric|min:0',
            'numero_lote'               => 'nullable|array',
            'numero_lote.*'             => 'nullable|string|max:100',
            'fecha_vencimiento'         => 'nullable|array',
            'fecha_vencimiento.*'       => 'nullable|date',
        ], [
            'numero_factura.required' => 'Debe ingresar un numero de factura.',
            'numero_factura.unique'   => 'La factura ingresada ya existe.',
            'id_proveedor.required'   => 'Debe seleccionar un proveedor.',
            'fecha_compra.required'   => 'Debe seleccionar una fecha.',
            'id_producto.required'    => 'Debe agregar al menos un producto.',
            'cantidad.*.min'          => 'La cantidad debe ser al menos 1.',
        ]);

        DB::beginTransaction();

        try {

            $productos = $request->id_producto;
            $cantidades = $request->cantidad;
            $precios = $request->precio;
            $numerosLote = $request->numero_lote ?? [];
            $fechasVencimiento = $request->fecha_vencimiento ?? [];

            $totalCompra = 0;
            foreach ($productos as $i => $idProducto) {
                $totalCompra += $cantidades[$i] * $precios[$i];
            }

            $compra = Compra::create([
                'numero_factura' => $request->numero_factura,
                'id_proveedor'   => $request->id_proveedor,
                'id_usuario'     => auth()->user()->id_usuario,
                'fecha_compra'   => $request->fecha_compra,
                'subtotal'       => $totalCompra,
                'igv'            => 0,
                'total'          => $totalCompra,
                'estado'         => 'PENDIENTE',
            ]);

            foreach ($productos as $i => $idProducto) {

                $cantidad = $cantidades[$i];
                $precio = $precios[$i];

                DetalleCompra::create([
                    'id_compra'         => $compra->id_compra,
                    'id_producto'       => $idProducto,
                    'cantidad'          => $cantidad,
                    'precio_unitario'   => $precio,
                    'subtotal'          => $cantidad * $precio,
                    'numero_lote'       => $numerosLote[$i] ?? null,
                    'fecha_vencimiento' => $fechasVencimiento[$i] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('compras.index')
                ->with('success', 'Compra registrada correctamente');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors($e->getMessage());
        }
    }

    /**
     * Aprueba una compra: por cada linea, crea el LOTE real (con su
     * fecha de vencimiento) y recien ahi ese stock entra al sistema.
     * Antes de esto la compra existe pero no afecta inventario, tal
     * como ya funcionaba (estado PENDIENTE -> APROBADA).
     */
    public function aprobar($id)
    {
        DB::beginTransaction();

        try {

            $compra = Compra::findOrFail($id);

            if ($compra->estado === 'APROBADA') {
                throw new \Exception('Esta compra ya fue aprobada anteriormente.');
            }

            $detalles = DetalleCompra::where('id_compra', $id)->get();

            foreach ($detalles as $detalle) {

                $producto = Producto::where('id_producto', $detalle->id_producto)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lote = Lote::create([
                    'id_producto'       => $detalle->id_producto,
                    'id_compra'         => $compra->id_compra,
                    'numero_lote'       => $detalle->numero_lote,
                    'fecha_vencimiento' => $detalle->fecha_vencimiento,
                    'cantidad_inicial'  => $detalle->cantidad,
                    'cantidad_actual'   => $detalle->cantidad,
                    'costo_unitario'    => $detalle->precio_unitario,
                    'estado'            => 'ACTIVO',
                ]);

                $detalle->update(['id_lote' => $lote->id_lote]);

                $stockAnterior = $producto->stock_actual;
                $stockNuevo = $stockAnterior + $detalle->cantidad;

                // stock_actual se mantiene como cache de la suma de
                // lotes activos; el dato real vive ahora en `lotes`.
                $producto->update(['stock_actual' => $stockNuevo]);

                DB::table('movimientos_inventario')->insert([
                    'id_producto'    => $detalle->id_producto,
                    'id_lote'        => $lote->id_lote,
                    'tipo_movimiento'=> 'ENTRADA',
                    'cantidad'       => $detalle->cantidad,
                    'descripcion'    => 'Compra aprobada - Factura ' . $compra->numero_factura,
                    'motivo'         => 'Compra',
                    'saldo_anterior' => $stockAnterior,
                    'saldo_nuevo'    => $stockNuevo,
                    'id_usuario'     => auth()->user()->id_usuario,
                    'creado_en'      => now(),
                ]);
            }

            $compra->update(['estado' => 'APROBADA']);

            DB::commit();

            return redirect()
                ->route('compras.index')
                ->with('success', 'Compra aprobada correctamente. Se crearon ' . $detalles->count() . ' lote(s).');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withErrors($e->getMessage());
        }
    }

}
