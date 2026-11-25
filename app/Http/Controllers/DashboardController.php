<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $esAdmin = optional(auth()->user()->rol)->nombre === 'ADMINISTRADOR';

        // ---------- Periodo del filtro (afecta gráficos y rankings) ----------
        $periodo = $request->input('periodo', '30');
        $hoy = now()->startOfDay();

        switch ($periodo) {
            case 'hoy':
                $desde = $hoy->copy();
                $hasta = now()->endOfDay();
                break;
            case '7':
                $desde = $hoy->copy()->subDays(6);
                $hasta = now()->endOfDay();
                break;
            case 'mes':
                $desde = now()->startOfMonth();
                $hasta = now()->endOfDay();
                break;
            case 'rango':
                try {
                    $desde = Carbon::parse($request->input('desde'))->startOfDay();
                    $hasta = Carbon::parse($request->input('hasta'))->endOfDay();
                } catch (\Throwable $e) {
                    $desde = $hoy->copy()->subDays(29);
                    $hasta = now()->endOfDay();
                    $periodo = '30';
                }
                if ($hasta->lt($desde)) {
                    [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
                }
                break;
            default:
                $periodo = '30';
                $desde = $hoy->copy()->subDays(29);
                $hasta = now()->endOfDay();
        }

        // ---------- Tarjetas (KPIs) ----------
        $ventasHoy = $this->totalVentas($hoy, now()->endOfDay());
        $ticketsHoy = $this->contarVentas($hoy, now()->endOfDay());
        $ventasAyer = $this->totalVentas($hoy->copy()->subDay(), $hoy->copy()->subSecond());

        // Mes actual vs. el mes anterior en el mismo tramo de días
        $iniMes = now()->startOfMonth();
        $iniMesAnt = $iniMes->copy()->subMonthNoOverflow();
        $finMesAnt = $iniMesAnt->copy()->addDays(now()->day - 1)->endOfDay();
        if ($finMesAnt->gt($iniMes)) {
            $finMesAnt = $iniMes->copy()->subSecond();
        }

        $ventasMes = $this->totalVentas($iniMes, now()->endOfDay());
        $ventasMesAnt = $this->totalVentas($iniMesAnt, $finMesAnt);
        $ticketsMes = $this->contarVentas($iniMes, now()->endOfDay());
        $ticketPromedio = $ticketsMes > 0 ? $ventasMes / $ticketsMes : 0;

        $gananciaMes = $esAdmin ? $this->ganancia($iniMes, now()->endOfDay()) : null;
        $gananciaMesAnt = $esAdmin ? $this->ganancia($iniMesAnt, $finMesAnt) : null;

        // Caja: estado actual y efectivo que debería haber ahora
        $caja = DB::table('caja')->where('estado', 'ABIERTA')->first();
        $efectivoEnCaja = null;
        if ($caja) {
            $mov = DB::table('movimientos_caja')
                ->where('id_caja', $caja->id_caja)
                ->whereIn('forma_pago', ['EFECTIVO', 'DOLARES'])
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo_movimiento='INGRESO' THEN monto ELSE -monto END),0) as neto")
                ->value('neto');
            $efectivoEnCaja = (float) $caja->saldo_inicial + (float) $mov;
        }

        // Inventario (solo productos activos)
        $inv = DB::table('productos')->where('activo', 1)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(stock_actual*precio_compra),0) as costo, COALESCE(SUM(stock_actual*precio_venta),0) as venta')
            ->first();
        $totalProductos = (int) $inv->total;
        $valorInventarioCosto = (float) $inv->costo;
        $valorInventarioVenta = (float) $inv->venta;

        $stockBajo = DB::table('productos')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        // Mismos productos que cuenta $stockBajo, para la ventana de detalle
        $productosStockBajo = DB::table('productos')
            ->leftJoin('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
            ->whereColumn('productos.stock_actual', '<=', 'productos.stock_minimo')
            ->select(
                'productos.nombre',
                'productos.codigo_barra',
                'productos.concentracion',
                'productos.presentacion',
                'productos.stock_actual',
                'productos.stock_minimo',
                'categorias.nombre as categoria'
            )
            ->orderBy('productos.stock_actual')
            ->orderBy('productos.nombre')
            ->get();

        // ---------- Lotes vencidos / por vencer ----------
        $lotesVencidos = Lote::with('producto')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now())
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        $lotesPorVencer = Lote::with('producto')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', now())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(90))
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        $porVencer30 = $lotesPorVencer->filter(
            fn ($l) => $l->fecha_vencimiento->lte(now()->addDays(30)->endOfDay())
        )->count();

        $valorEnRiesgo = (float) $lotesVencidos->concat($lotesPorVencer)
            ->sum(fn ($l) => $l->cantidad_actual * $l->costo_unitario);

        // ---------- Gráficos del periodo ----------
        $base = fn () => DB::table('ventas')
            ->where('ventas.estado', 'COMPLETADA')
            ->whereBetween('ventas.fecha_venta', [$desde, $hasta]);

        // Ventas por día (con ceros para los días sin ventas)
        $porDia = $base()
            ->selectRaw('DATE(fecha_venta) as dia, SUM(total) as total, COUNT(*) as tickets')
            ->groupBy('dia')->pluck('total', 'dia');

        $diasLabels = [];
        $diasValores = [];
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            $diasLabels[] = $d->format('d/m');
            $diasValores[] = round((float) ($porDia[$d->toDateString()] ?? 0), 2);
            if (count($diasLabels) > 400) { break; }
        }

        $totalPeriodo = $base()->sum('total');
        $ticketsPeriodo = $base()->count();

        // Comparativo del periodo contra el periodo anterior de igual duración
        $dias = max((int) $desde->diffInDays($hasta->copy()->startOfDay()) + 1, 1);
        $antHasta = $desde->copy()->subSecond();
        $antDesde = $desde->copy()->subDays($dias);
        $totalPeriodoAnt = $this->totalVentas($antDesde, $antHasta);

        // Por método de pago
        $porMetodo = $base()
            ->selectRaw('metodo_pago, SUM(total) as total')
            ->groupBy('metodo_pago')->orderByDesc('total')->get();

        // Por hora del día
        $porHoraRaw = $base()
            ->selectRaw('HOUR(fecha_venta) as hora, COUNT(*) as tickets')
            ->groupBy('hora')->pluck('tickets', 'hora');
        $horasLabels = [];
        $horasValores = [];
        for ($h = 6; $h <= 22; $h++) {
            $horasLabels[] = sprintf('%02d:00', $h);
            $horasValores[] = (int) ($porHoraRaw[$h] ?? 0);
        }

        // Top 5 productos más vendidos
        $topProductos = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id_venta', '=', 'dv.id_venta')
            ->join('productos as p', 'p.id_producto', '=', 'dv.id_producto')
            ->where('v.estado', 'COMPLETADA')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw('p.nombre, SUM(dv.cantidad) as unidades, SUM(dv.subtotal) as monto')
            ->groupBy('p.id_producto', 'p.nombre')
            ->orderByDesc('unidades')->limit(5)->get();

        // Ventas por categoría
        $ventasPorCategoria = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id_venta', '=', 'dv.id_venta')
            ->join('productos as p', 'p.id_producto', '=', 'dv.id_producto')
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria')
            ->where('v.estado', 'COMPLETADA')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("COALESCE(c.nombre,'Sin categoría') as nombre, SUM(dv.subtotal) as total")
            ->groupBy('c.id_categoria', 'c.nombre')
            ->orderByDesc('total')->limit(6)->get();

        // Ventas por cajero
        $ventasPorCajero = DB::table('ventas as v')
            ->join('usuarios as u', 'u.id_usuario', '=', 'v.id_usuario')
            ->where('v.estado', 'COMPLETADA')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw('u.nombre_completo as nombre, SUM(v.total) as total, COUNT(*) as tickets')
            ->groupBy('u.id_usuario', 'u.nombre_completo')
            ->orderByDesc('total')->get();

        // ---------- Parte 2: listas operativas ----------
        $ultimasVentas = DB::table('ventas as v')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'v.id_usuario')
            ->select('v.id_venta', 'v.numero_ticket', 'v.fecha_venta', 'v.total', 'v.metodo_pago', 'v.estado', 'v.cliente_nombre', 'u.nombre_completo as cajero')
            ->orderByDesc('v.fecha_venta')->limit(8)->get();

        // Productos con stock que no se venden hace 60 días
        $vendidosRecientes = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id_venta', '=', 'dv.id_venta')
            ->where('v.estado', 'COMPLETADA')
            ->where('v.fecha_venta', '>=', now()->subDays(60))
            ->select('dv.id_producto')->distinct();

        $sinMovimiento = DB::table('productos')
            ->where('activo', 1)
            ->where('stock_actual', '>', 0)
            ->whereNotIn('id_producto', $vendidosRecientes)
            ->selectRaw('nombre, stock_actual, (stock_actual*precio_compra) as valor')
            ->orderByDesc('valor')->limit(8)->get();
        $sinMovimientoTotal = (float) DB::table('productos')
            ->where('activo', 1)->where('stock_actual', '>', 0)
            ->whereNotIn('id_producto', $vendidosRecientes)
            ->sum(DB::raw('stock_actual*precio_compra'));

        $movimientosCaja = DB::table('movimientos_caja as m')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->select('m.tipo_movimiento', 'm.forma_pago', 'm.monto', 'm.descripcion', 'm.creado_en', 'u.nombre_completo as usuario')
            ->orderByDesc('m.creado_en')->limit(8)->get();

        return view('dashboard', compact(
            'esAdmin', 'periodo', 'desde', 'hasta',
            'ventasHoy', 'ticketsHoy', 'ventasAyer',
            'ventasMes', 'ventasMesAnt', 'ticketPromedio', 'ticketsMes',
            'gananciaMes', 'gananciaMesAnt',
            'caja', 'efectivoEnCaja',
            'totalProductos', 'valorInventarioCosto', 'valorInventarioVenta',
            'stockBajo', 'productosStockBajo',
            'lotesVencidos', 'lotesPorVencer', 'porVencer30', 'valorEnRiesgo',
            'diasLabels', 'diasValores', 'totalPeriodo', 'ticketsPeriodo', 'totalPeriodoAnt',
            'porMetodo', 'horasLabels', 'horasValores',
            'topProductos', 'ventasPorCategoria', 'ventasPorCajero',
            'ultimasVentas', 'sinMovimiento', 'sinMovimientoTotal', 'movimientosCaja'
        ));
    }

    private function totalVentas(Carbon $desde, Carbon $hasta): float
    {
        return (float) DB::table('ventas')
            ->where('estado', 'COMPLETADA')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->sum('total');
    }

    private function contarVentas(Carbon $desde, Carbon $hasta): int
    {
        return DB::table('ventas')
            ->where('estado', 'COMPLETADA')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->count();
    }

    /** Ganancia estimada = venta - costo (costo del lote vendido, o precio de compra del producto). */
    private function ganancia(Carbon $desde, Carbon $hasta): float
    {
        $r = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id_venta', '=', 'dv.id_venta')
            ->join('productos as p', 'p.id_producto', '=', 'dv.id_producto')
            ->leftJoin('lotes as l', 'l.id_lote', '=', 'dv.id_lote')
            ->where('v.estado', 'COMPLETADA')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw('COALESCE(SUM(dv.subtotal),0) as venta, COALESCE(SUM(dv.cantidad * COALESCE(l.costo_unitario, p.precio_compra)),0) as costo')
            ->first();

        return (float) $r->venta - (float) $r->costo;
    }
}
