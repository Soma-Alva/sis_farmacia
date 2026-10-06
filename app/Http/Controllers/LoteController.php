<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    /**
     * Listado de lotes con búsqueda y filtros: por producto/código de
     * barras, por número de lote, por estado, y "próximos a vencer".
     */
    public function index(Request $request)
    {
        $query = Lote::with('producto')
            ->join('productos', 'productos.id_producto', '=', 'lotes.id_producto')
            ->select('lotes.*');

        // Buscar por nombre de producto, código de barras o número de lote
        if ($request->filled('buscar')) {
            $texto = $request->buscar;
            $query->where(function ($q) use ($texto) {
                $q->where('productos.nombre', 'like', "%{$texto}%")
                  ->orWhere('productos.codigo_barra', 'like', "%{$texto}%")
                  ->orWhere('lotes.numero_lote', 'like', "%{$texto}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('lotes.estado', $request->estado);
        }

        // Filtro "próximos a vencer" (checkbox)
        if ($request->boolean('proximos_vencer')) {
            $query->whereNotNull('lotes.fecha_vencimiento')
                  ->whereDate('lotes.fecha_vencimiento', '<=', now()->addDays(90))
                  ->where('lotes.cantidad_actual', '>', 0);
        }

        // Por default, mostrar primero lo que tiene stock y vence antes
        $lotes = $query
            ->orderByRaw('lotes.cantidad_actual = 0, lotes.fecha_vencimiento IS NULL, lotes.fecha_vencimiento ASC')
            ->paginate(20)
            ->withQueryString();

        // Contadores para las tarjetas resumen de arriba
        $resumen = [
            'total_lotes'     => Lote::count(),
            'con_stock'       => Lote::where('cantidad_actual', '>', 0)->count(),
            'proximos_vencer' => Lote::proximosAVencer(90)->count(),
            'vencidos'        => Lote::whereNotNull('fecha_vencimiento')
                                    ->whereDate('fecha_vencimiento', '<', now())
                                    ->where('cantidad_actual', '>', 0)
                                    ->count(),
        ];

        return view('lotes.index', compact('lotes', 'resumen'));
    }

    /**
     * Detalle de un lote: de qué compra vino, y en qué ventas se usó.
     */
    public function show($id)
    {
        $lote = Lote::with(['producto', 'compra.proveedor'])
            ->findOrFail($id);

        $movimientos = DB::table('movimientos_inventario')
            ->where('id_lote', $id)
            ->orderByDesc('creado_en')
            ->get();

        $ventasDelLote = DB::table('detalle_ventas')
            ->join('ventas', 'ventas.id_venta', '=', 'detalle_ventas.id_venta')
            ->where('detalle_ventas.id_lote', $id)
            ->select(
                'ventas.numero_ticket',
                'ventas.fecha_venta',
                'ventas.estado',
                'detalle_ventas.cantidad',
                'detalle_ventas.subtotal'
            )
            ->orderByDesc('ventas.fecha_venta')
            ->get();

        return view('lotes.show', compact('lote', 'movimientos', 'ventasDelLote'));
    }

    /**
     * Da de baja un lote vencido: lo saca del stock vendible (stock
     * actual del lote y del producto quedan en 0) y deja registrado
     * el motivo (destrucción o devolución al proveedor) en
     * movimientos_inventario, para auditoría. No se borra el lote —
     * su historial de ventas y de origen debe seguir siendo
     * consultable.
     */
    public function darDeBaja(Request $request, $id)
    {
        $request->validate([
            'motivo_baja' => 'required|in:DESTRUIDO,DEVUELTO_PROVEEDOR',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {

            $lote = Lote::lockForUpdate()->findOrFail($id);

            if ($lote->cantidad_actual <= 0) {
                throw new \Exception('Este lote ya no tiene stock, no hay nada que dar de baja.');
            }

            $cantidadDadaDeBaja = $lote->cantidad_actual;

            $producto = Producto::where('id_producto', $lote->id_producto)
                ->lockForUpdate()
                ->firstOrFail();

            $stockAnterior = $producto->stock_actual;
            $stockNuevo = max(0, $stockAnterior - $cantidadDadaDeBaja);

            $lote->update([
                'cantidad_actual' => 0,
                'estado' => 'VENCIDO',
            ]);

            $producto->update(['stock_actual' => $stockNuevo]);

            $motivoTexto = $request->motivo_baja === 'DESTRUIDO'
                ? 'Vencimiento - producto destruido'
                : 'Vencimiento - devuelto al proveedor';

            DB::table('movimientos_inventario')->insert([
                'id_producto'     => $lote->id_producto,
                'id_lote'         => $lote->id_lote,
                'tipo_movimiento' => 'AJUSTE',
                'cantidad'        => $cantidadDadaDeBaja,
                'descripcion'     => $motivoTexto . ($request->observaciones ? ' - ' . $request->observaciones : ''),
                'motivo'          => 'Baja por vencimiento',
                'saldo_anterior'  => $stockAnterior,
                'saldo_nuevo'     => $stockNuevo,
                'id_usuario'      => auth()->user()->id_usuario,
                'creado_en'       => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('lotes.show', $lote->id_lote)
                ->with('success', "Lote dado de baja: {$cantidadDadaDeBaja} unidad(es) retiradas del stock.");

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'No fue posible dar de baja el lote: ' . $e->getMessage());
        }
    }
}
