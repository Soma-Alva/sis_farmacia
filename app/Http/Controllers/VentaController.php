<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Lote;


class VentaController extends Controller
{
    // Mostrar POS
    public function index()
    {

        $caja = DB::table('caja')
        ->where('estado', 'ABIERTA')
        ->first();

        if (!$caja) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'Debe abrir una caja antes de realizar ventas.');
        }

        $productos = Producto::where('activo', 1)->get();
        return view('ventas.index', compact('productos','caja' ));


    }

    public function historial(Request $request)
    {
        $query = DB::table('ventas');

        // Buscar por ticket
        if ($request->filled('ticket')) {
            $query->where('numero_ticket', 'like', '%' . $request->ticket . '%');
        }

        // Buscar por cliente
        if ($request->filled('cliente')) {
            $query->where('cliente_nombre', 'like', '%' . $request->cliente . '%');
        }

        // Buscar por fecha
        if ($request->filled('fecha')) {
            $query->whereDate('fecha_venta', $request->fecha);
        }

        $ventas = $query
            ->orderBy('fecha_venta', 'desc')
            ->get();

        return view('ventas.historial', compact('ventas'));
    }

    public function show($id)
    {
        $venta = DB::table('ventas')
        ->where('id_venta', $id)
        ->first();

        $detalle = DB::table('detalle_ventas')
        ->join(
            'productos',
            'detalle_ventas.id_producto',
            '=',
            'productos.id_producto'
        )
        ->leftJoin(
            'lotes',
            'detalle_ventas.id_lote',
            '=',
            'lotes.id_lote'
        )
        ->where('detalle_ventas.id_venta', $id)
        ->select(
            'productos.nombre',
            'detalle_ventas.cantidad',
            'detalle_ventas.precio_unitario',
            'detalle_ventas.subtotal',
            'lotes.numero_lote',
            'lotes.fecha_vencimiento'
        )
        ->get();

        return view('ventas.show', compact(
            'venta',
            'detalle'
        ));
    }

    public function anular($id)
    {
        DB::beginTransaction();

        try {

            $venta = DB::table('ventas')->where('id_venta', $id)->first();

            if (!$venta) {
                throw new \Exception('Venta no encontrada.');
            }

            if ($venta->estado === 'ANULADA') {
                throw new \Exception('Esta venta ya fue anulada anteriormente.');
            }

            $detalle = DB::table('detalle_ventas')
                ->where('id_venta', $id)
                ->get();

            foreach ($detalle as $item) {

                // Devolver la cantidad al lote específico del que salió.
                // Ventas anteriores a la migración de lotes no tienen
                // id_lote (quedó NULL); en ese caso solo se restaura
                // el stock general del producto, como funcionaba antes.
                if ($item->id_lote) {

                    $lote = Lote::where('id_lote', $item->id_lote)
                        ->lockForUpdate()
                        ->first();

                    if ($lote) {
                        $lote->cantidad_actual += $item->cantidad;
                        if ($lote->estado === 'AGOTADO' && $lote->cantidad_actual > 0) {
                            $lote->estado = 'ACTIVO';
                        }
                        $lote->save();
                    }
                }

                DB::table('productos')
                    ->where('id_producto', $item->id_producto)
                    ->increment(
                        'stock_actual',
                        $item->cantidad
                    );

                DB::table('movimientos_inventario')->insert([
                    'id_producto'    => $item->id_producto,
                    'id_lote'        => $item->id_lote,
                    'tipo_movimiento'=> 'ENTRADA',
                    'cantidad'       => $item->cantidad,
                    'descripcion'    => 'Devolución por anulación de venta ' . $venta->numero_ticket,
                    'motivo'         => 'Anulación de venta',
                    'id_usuario'     => auth()->user()->id_usuario,
                    'creado_en'      => now(),
                ]);

            }

            DB::table('ventas')
                ->where('id_venta', $id)
                ->update([
                    'estado' => 'ANULADA'
                ]);

            DB::commit();

            // Obtener el movimiento de caja de esa venta
            $movimiento = DB::table('movimientos_caja')
                ->where('id_venta', $id)
                ->first();

            if ($movimiento) {

                // Restar el monto de la caja
                DB::table('caja')
                    ->where('id_caja', $movimiento->id_caja)
                    ->decrement('saldo_final', $movimiento->monto);

                // Marcar el movimiento como anulado
                DB::table('movimientos_caja')
                    ->where('id_movimiento', $movimiento->id_movimiento)
                    ->update([
                        'descripcion' => 'VENTA ANULADA',
                        'monto' => 0
                    ]);
            }
            return redirect()
                ->route('ventas.historial')
                ->with(
                    'success',
                    'Venta anulada correctamente.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'No fue posible anular la venta: ' . $e->getMessage()
            );

        }
    }


    /**
     * Descuenta `$cantidadRequerida` unidades del producto `$idProducto`
     * usando FEFO (First Expired, First Out): toma primero del lote
     * activo que vence más pronto, y si no alcanza, sigue con el
     * siguiente lote hasta completar la cantidad pedida.
     *
     * Bloquea las filas de `lotes` involucradas (lockForUpdate) dentro
     * de la transacción de la venta, así que si dos cajas venden el
     * mismo producto al mismo tiempo, la segunda espera a que la
     * primera termine en vez de vender stock que ya no existe.
     *
     * Devuelve un array de consumos: [['id_lote' => x, 'cantidad' => y], ...]
     * (puede ser más de un lote si uno solo no alcanza).
     *
     * @throws \Exception si no hay stock suficiente en ningún lote.
     */
    private function consumirLotesFefo(int $idProducto, int $cantidadRequerida): array
    {
        $lotes = Lote::disponiblesFefo($idProducto)
            ->lockForUpdate()
            ->get();

        $totalDisponible = $lotes->sum('cantidad_actual');

        if ($totalDisponible < $cantidadRequerida) {

            $nombreProducto = Producto::where('id_producto', $idProducto)
                ->value('nombre') ?? "producto #{$idProducto}";

            throw new \Exception(
                "Stock insuficiente para {$nombreProducto}. " .
                "Disponible: {$totalDisponible}, solicitado: {$cantidadRequerida}."
            );
        }

        $restante = $cantidadRequerida;
        $consumos = [];

        foreach ($lotes as $lote) {

            if ($restante <= 0) {
                break;
            }

            $tomar = min($lote->cantidad_actual, $restante);

            if ($tomar <= 0) {
                continue;
            }

            $lote->cantidad_actual -= $tomar;

            if ($lote->cantidad_actual <= 0) {
                $lote->cantidad_actual = 0;
                $lote->estado = 'AGOTADO';
            }

            $lote->save();

            $consumos[] = [
                'id_lote'  => $lote->id_lote,
                'cantidad' => $tomar,
            ];

            $restante -= $tomar;
        }

        return $consumos;
    }


    // Procesar venta
    public function procesar(Request $request)
    {

                $montoPagado = 0;
        if(
            $request->metodo_pago == 'TARJETA' ||
            $request->metodo_pago == 'TRANSFERENCIA'
        ){
            $montoPagado = $request->total;
        }
        // EFECTIVO
        if ($request->metodo_pago == 'EFECTIVO') {

            $montoPagado = $request->monto_pagado;

        }

        // DÓLARES
        elseif ($request->metodo_pago == 'DOLARES') {

            $tipoCambio = DB::table('caja')
                ->where('estado', 'ABIERTA')
                ->value('tipo_cambio');

            $montoPagado = $request->monto_pagado * $tipoCambio;

        }

        // TARJETA Y TRANSFERENCIA
        else {

            // Se asume que paga exactamente el total
            $montoPagado = $request->total;

        }

        if ($montoPagado <= 0) {
            return back()->with('error', 'Debe ingresar un monto válido.');
        }

        if ($montoPagado < $request->total) {
            return back()->with('error', 'El monto pagado es insuficiente.');
        }

        \Log::info([
            'montoPagado' => $montoPagado,
            'total' => $request->total,
        ]);

        DB::beginTransaction();

        try {

            //  Convertir JSON a array
            $productos = json_decode($request->productos, true);

            //  Generar ticket
            $ticket = 'TICKET-' . time();

            //  Calcular valores
            $subtotal = $request->total / 1.18;
            $igv = $request->total - $subtotal;
            $cambio = $montoPagado - $request->total;

            //  Insertar venta completa
            $ventaId = DB::table('ventas')->insertGetId([
                'numero_ticket' => $ticket,
                'id_usuario' => auth()->user()->id_usuario,
                'fecha_venta' => now(),

                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $request->total,

                'metodo_pago' => $request->metodo_pago ?? 'EFECTIVO',
                'monto_pagado' => $montoPagado,
                'cambio' => $cambio,

                'cliente_nombre' => $request->cliente_nombre,
                'cliente_dni' => $request->cliente_dni,
                'observaciones' => $request->observaciones,

                'estado' => 'COMPLETADA'
            ]);


            foreach ($productos as $item) {

                // Descontar el stock usando FEFO: puede tomar de más
                // de un lote si el primero que vence no alcanza solo.
                $consumos = $this->consumirLotesFefo(
                    (int) $item['id'],
                    (int) $item['cantidad']
                );

                foreach ($consumos as $consumo) {

                    DB::table('detalle_ventas')->insert([
                        'id_venta'        => $ventaId,
                        'id_producto'     => $item['id'],
                        'id_lote'         => $consumo['id_lote'],
                        'cantidad'        => $consumo['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal'        => $consumo['cantidad'] * $item['precio'],
                    ]);

                    DB::table('movimientos_inventario')->insert([
                        'id_producto'    => $item['id'],
                        'id_lote'        => $consumo['id_lote'],
                        'tipo_movimiento'=> 'SALIDA',
                        'cantidad'       => $consumo['cantidad'],
                        'descripcion'    => 'Venta ' . $ticket,
                        'motivo'         => 'Venta',
                        'id_usuario'     => auth()->user()->id_usuario,
                        'creado_en'      => now(),
                    ]);
                }

                //  Sincronizar el caché de stock en productos
                DB::table('productos')
                    ->where('id_producto', $item['id'])
                    ->decrement('stock_actual', $item['cantidad']);
            }

            // Registrar movimiento de caja por la venta
            $caja = DB::table('caja')
                ->where('estado', 'ABIERTA')
                ->first();

            if ($caja) {

                $saldoAnterior = $caja->saldo_final;

                $saldoActual = $saldoAnterior + $request->total;

                DB::table('movimientos_caja')->insert([

                    'id_caja' => $caja->id_caja,

                    'id_venta' => $ventaId,

                    'id_compra' => null,

                    'tipo_movimiento' => 'INGRESO',

                    'forma_pago' => $request->metodo_pago,

                    'monto' => $request->total,

                    'descripcion' => 'Venta ' . $ticket,

                    'motivo' => 'Venta',

                    'saldo_anterior' => $saldoAnterior,

                    'saldo_actual' => $saldoActual,

                    'id_usuario' => auth()->user()->id_usuario,

                    'creado_en' => now()

                ]);

                DB::table('caja')
                    ->where('id_caja', $caja->id_caja)
                    ->update([

                        'saldo_final' => $saldoActual

                    ]);
            }

            //  Auditoría
            DB::table('auditoria')->insert([
                'id_usuario' => auth()->user()->id_usuario,
                'tabla_afectada' => 'ventas',
                'accion' => 'INSERT',
                'registro_id' => $ventaId,
                'datos_nuevos' => json_encode([
                    'total' => $request->total
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta realizada correctamente');

        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());

        }
    }
}
