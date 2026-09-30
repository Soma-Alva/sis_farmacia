<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Lote;

class DashboardController extends Controller
{
    public function index()
    {
        $ventasHoy = DB::table('ventas')
            ->whereDate('fecha_venta', now()->toDateString())
            ->where('estado', 'COMPLETADA')
            ->sum('total');

        $totalProductos = DB::table('productos')->count();

        $stockBajo = DB::table('productos')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        $ultimasVentas = DB::table('ventas')
            ->orderBy('fecha_venta', 'desc')
            ->limit(5)
            ->get();

        $productosStockBajo = DB::table('productos')
            ->whereColumn('stock_actual','<=','stock_minimo')
            ->select('nombre','stock_actual')
            ->get();

        $productosPorCategoria = DB::table('categorias')
                ->leftJoin(
                    'productos',
                    'categorias.id_categoria',
                    '=',
                    'productos.id_categoria'
                )
                ->select(
                    'categorias.nombre',
                    DB::raw('COUNT(productos.id_producto) as total')
                )
                ->groupBy(
                    'categorias.id_categoria',
                    'categorias.nombre'
                )
                ->get();


        $ventasPorMes = DB::table('ventas')
                ->select(
                    DB::raw('MONTH(fecha_venta) as mes'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy(DB::raw('MONTH(fecha_venta)'))
                ->orderBy('mes')
                ->get();

        // --- Alertas de vencimiento de lotes ---

        // Lotes que ya vencieron y todavía tienen stock: lo más urgente,
        // deberían dejar de venderse.
        $lotesVencidos = Lote::with('producto')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now())
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        // Lotes que vencen dentro de los próximos 90 días (y todavía
        // no vencieron), para poder priorizar promociones o devolución
        // al proveedor a tiempo.
        $lotesPorVencer = Lote::with('producto')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', now())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(90))
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('dashboard', compact(
            'ventasHoy',
            'totalProductos',
            'stockBajo',
            'productosStockBajo',
            'productosPorCategoria',
            'ventasPorMes',
            'lotesVencidos',
            'lotesPorVencer'
        ));
    }
}