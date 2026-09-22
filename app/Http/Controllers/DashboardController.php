<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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

               
        return view('dashboard', compact(
            'ventasHoy',
            'totalProductos',
            'stockBajo',
            'productosStockBajo',
            'productosPorCategoria',
            'ventasPorMes'
        ));
    }
}