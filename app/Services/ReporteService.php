<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Cálculos de los reportes (pantalla y Excel usan exactamente los mismos
 * datos, así que las cifras nunca difieren entre una y otro).
 */
class ReporteService
{
    public const METODOS = ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'DOLARES'];

    public static function etiquetaMetodo(?string $m): string
    {
        return match (strtoupper((string) $m)) {
            'DOLARES' => 'Dólares',
            default => ucfirst(strtolower((string) $m)),
        };
    }

    /** Resuelve el rango de fechas a partir del request (periodo rápido o desde/hasta). */
    public static function rango(?string $periodo, ?string $desde, ?string $hasta): array
    {
        $hoy = now()->startOfDay();
        switch ($periodo) {
            case 'hoy':
                return ['hoy', $hoy->copy(), now()->endOfDay()];
            case '7':
                return ['7', $hoy->copy()->subDays(6), now()->endOfDay()];
            case '30':
                return ['30', $hoy->copy()->subDays(29), now()->endOfDay()];
            case 'mes_ant':
                $ini = now()->startOfMonth()->subMonthNoOverflow();
                return ['mes_ant', $ini, $ini->copy()->endOfMonth()];
            case 'rango':
                try {
                    $d = Carbon::parse($desde)->startOfDay();
                    $h = Carbon::parse($hasta)->endOfDay();
                    if ($h->lt($d)) {
                        [$d, $h] = [$h->copy()->startOfDay(), $d->copy()->endOfDay()];
                    }
                    return ['rango', $d, $h];
                } catch (\Throwable $e) {
                    // cae al mes actual
                }
        }
        return ['mes', now()->startOfMonth(), now()->endOfDay()];
    }

    // ------------------------------------------------------------------
    // Ingresos y egresos
    // ------------------------------------------------------------------
    public function ingresosEgresos(Carbon $desde, Carbon $hasta): array
    {
        $ventasMetodo = DB::table('ventas')
            ->where('estado', 'COMPLETADA')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->selectRaw('metodo_pago, COUNT(*) as tickets, SUM(total) as total')
            ->groupBy('metodo_pago')->get()->keyBy('metodo_pago');

        // Siempre se muestran los 4 métodos, aunque estén en cero
        $porMetodo = collect(self::METODOS)->map(fn ($m) => (object) [
            'metodo' => $m,
            'tickets' => (int) ($ventasMetodo[$m]->tickets ?? 0),
            'total' => (float) ($ventasMetodo[$m]->total ?? 0),
        ]);
        $totalVentas = (float) $porMetodo->sum('total');

        $ingresosManuales = DB::table('movimientos_caja as m')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->where('m.tipo_movimiento', 'INGRESO')
            ->whereNull('m.id_venta')
            ->whereBetween('m.creado_en', [$desde, $hasta])
            ->select('m.creado_en as fecha', 'm.descripcion', 'm.motivo', 'm.forma_pago', 'm.monto', 'u.nombre_completo as usuario')
            ->orderBy('m.creado_en')->get();
        $totalIngresosManuales = (float) $ingresosManuales->sum('monto');

        $compras = DB::table('compras as c')
            ->leftJoin('proveedores as p', 'p.id_proveedor', '=', 'c.id_proveedor')
            ->where('c.estado', 'APROBADA')
            ->whereBetween('c.fecha_compra', [$desde->toDateString(), $hasta->toDateString()])
            ->select('c.fecha_compra as fecha', 'c.numero_factura', 'p.nombre as proveedor', 'c.total')
            ->orderBy('c.fecha_compra')->get();
        $totalCompras = (float) $compras->sum('total');

        // Egresos de caja que no son una compra (las compras ya van arriba)
        $egresosCaja = DB::table('movimientos_caja as m')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->where('m.tipo_movimiento', 'EGRESO')
            ->whereNull('m.id_compra')
            ->whereBetween('m.creado_en', [$desde, $hasta])
            ->select('m.creado_en as fecha', 'm.descripcion', 'm.motivo', 'm.forma_pago', 'm.monto', 'u.nombre_completo as usuario')
            ->orderBy('m.creado_en')->get();
        $totalEgresosCaja = (float) $egresosCaja->sum('monto');

        $totalIngresos = $totalVentas + $totalIngresosManuales;
        $totalEgresos = $totalCompras + $totalEgresosCaja;

        // Resumen por día
        $dias = [];
        for ($d = $desde->copy()->startOfDay(); $d->lte($hasta); $d->addDay()) {
            $dias[$d->toDateString()] = ['ing' => 0.0, 'egr' => 0.0];
            if (count($dias) > 400) { break; }
        }
        $suma = function ($coleccion, string $campo, string $tipo) use (&$dias) {
            foreach ($coleccion as $fila) {
                $k = Carbon::parse($fila->fecha)->toDateString();
                if (isset($dias[$k])) { $dias[$k][$tipo] += (float) $fila->{$campo}; }
            }
        };
        $ventasDia = DB::table('ventas')->where('estado', 'COMPLETADA')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->selectRaw('DATE(fecha_venta) as fecha, SUM(total) as total')->groupBy('fecha')->get();
        $suma($ventasDia, 'total', 'ing');
        $suma($ingresosManuales, 'monto', 'ing');
        $suma($compras, 'total', 'egr');
        $suma($egresosCaja, 'monto', 'egr');

        return compact(
            'porMetodo', 'totalVentas', 'ingresosManuales', 'totalIngresosManuales',
            'compras', 'totalCompras', 'egresosCaja', 'totalEgresosCaja',
            'totalIngresos', 'totalEgresos', 'dias'
        ) + ['neto' => $totalIngresos - $totalEgresos];
    }

    // ------------------------------------------------------------------
    // Balance de rentabilidad
    // ------------------------------------------------------------------
    public function balance(Carbon $desde, Carbon $hasta): array
    {
        $actual = $this->resultado($desde, $hasta);

        $dias = max((int) $desde->copy()->startOfDay()->diffInDays($hasta->copy()->startOfDay()) + 1, 1);
        $antHasta = $desde->copy()->subSecond();
        $antDesde = $desde->copy()->startOfDay()->subDays($dias);
        $anterior = $this->resultado($antDesde, $antHasta);

        $anuladas = DB::table('ventas')->where('estado', 'ANULADA')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(total),0) as total')->first();

        $lineaNeta = 'dv.subtotal * CASE WHEN v.total > 0 THEN v.subtotal * 1.0 / v.total ELSE 1 END';
        $costoLinea = 'dv.cantidad * COALESCE(l.costo_unitario, p.precio_compra)';

        $base = fn () => DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id_venta', '=', 'dv.id_venta')
            ->join('productos as p', 'p.id_producto', '=', 'dv.id_producto')
            ->leftJoin('lotes as l', 'l.id_lote', '=', 'dv.id_lote')
            ->where('v.estado', 'COMPLETADA')
            ->whereBetween('v.fecha_venta', [$desde, $hasta]);

        $porProducto = $base()
            ->selectRaw("p.nombre, SUM(dv.cantidad) as unidades, SUM({$lineaNeta}) as venta, SUM({$costoLinea}) as costo")
            ->groupBy('p.id_producto', 'p.nombre')
            ->orderByRaw("SUM({$lineaNeta}) - SUM({$costoLinea}) DESC")
            ->limit(15)->get()
            ->map(function ($r) {
                $r->utilidad = (float) $r->venta - (float) $r->costo;
                $r->margen = $r->venta > 0 ? $r->utilidad / $r->venta : 0;
                return $r;
            });

        $porCategoria = $base()
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria')
            ->selectRaw("COALESCE(c.nombre,'Sin categoría') as nombre, SUM({$lineaNeta}) as venta, SUM({$costoLinea}) as costo")
            ->groupBy('c.id_categoria', 'c.nombre')
            ->orderByRaw("SUM({$lineaNeta}) - SUM({$costoLinea}) DESC")->get()
            ->map(function ($r) {
                $r->utilidad = (float) $r->venta - (float) $r->costo;
                $r->margen = $r->venta > 0 ? $r->utilidad / $r->venta : 0;
                return $r;
            });

        // Situación actual (foto de hoy, no depende del periodo)
        $inv = DB::table('productos')->where('activo', 1)
            ->selectRaw('COALESCE(SUM(stock_actual*precio_compra),0) as costo, COALESCE(SUM(stock_actual*precio_venta),0) as venta')->first();

        $cajaAbierta = DB::table('caja')->where('estado', 'ABIERTA')->first();
        $efectivoCaja = null;
        if ($cajaAbierta) {
            $neto = DB::table('movimientos_caja')
                ->where('id_caja', $cajaAbierta->id_caja)->where('forma_pago', 'EFECTIVO')
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo_movimiento='INGRESO' THEN monto ELSE -monto END),0) as n")->value('n');
            $efectivoCaja = (float) $cajaAbierta->saldo_inicial + (float) $neto;
        }

        $perdidaVencidos = (float) DB::table('lotes')
            ->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', now())
            ->where('cantidad_actual', '>', 0)
            ->sum(DB::raw('cantidad_actual*costo_unitario'));

        $comprasPeriodo = (float) DB::table('compras')->where('estado', 'APROBADA')
            ->whereBetween('fecha_compra', [$desde->toDateString(), $hasta->toDateString()])->sum('total');

        $veredicto = $actual['utilidad'] > 0 ? 'RENTABLE' : ($actual['utilidad'] < 0 ? 'EN PÉRDIDA' : 'EN EQUILIBRIO');

        return [
            'actual' => $actual,
            'anterior' => $anterior,
            'anuladas' => $anuladas,
            'porProducto' => $porProducto,
            'porCategoria' => $porCategoria,
            'inventarioCosto' => (float) $inv->costo,
            'inventarioVenta' => (float) $inv->venta,
            'efectivoCaja' => $efectivoCaja,
            'perdidaVencidos' => $perdidaVencidos,
            'comprasPeriodo' => $comprasPeriodo,
            'veredicto' => $veredicto,
            'antDesde' => $antDesde,
            'antHasta' => $antHasta,
        ];
    }

    /** Ventas netas (sin IVA), costo de lo vendido y utilidad de un periodo. */
    private function resultado(Carbon $desde, Carbon $hasta): array
    {
        $v = DB::table('ventas')->where('estado', 'COMPLETADA')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->selectRaw('COUNT(*) as tickets, COALESCE(SUM(subtotal),0) as neto, COALESCE(SUM(igv),0) as iva, COALESCE(SUM(total),0) as cobrado')
            ->first();

        $costo = (float) DB::table('detalle_ventas as dv')
            ->join('ventas as vv', 'vv.id_venta', '=', 'dv.id_venta')
            ->join('productos as p', 'p.id_producto', '=', 'dv.id_producto')
            ->leftJoin('lotes as l', 'l.id_lote', '=', 'dv.id_lote')
            ->where('vv.estado', 'COMPLETADA')
            ->whereBetween('vv.fecha_venta', [$desde, $hasta])
            ->sum(DB::raw('dv.cantidad * COALESCE(l.costo_unitario, p.precio_compra)'));

        $neto = (float) $v->neto;
        $utilidad = $neto - $costo;

        return [
            'tickets' => (int) $v->tickets,
            'neto' => $neto,
            'iva' => (float) $v->iva,
            'cobrado' => (float) $v->cobrado,
            'costo' => $costo,
            'utilidad' => $utilidad,
            'margen' => $neto > 0 ? $utilidad / $neto : 0,
        ];
    }

    // ------------------------------------------------------------------
    // Lotes vencidos y por vencer
    // ------------------------------------------------------------------
    public const VENTANAS = [
        'todos' => 'Vencidos + próximos 90 días',
        'vencidos' => 'Solo vencidos',
        '30' => 'Vencen en 30 días',
        '60' => 'Vencen en 60 días',
        '90' => 'Vencen en 90 días',
        '180' => 'Vencen en 180 días',
    ];

    /**
     * Lotes con stock que ya vencieron o vencen pronto.
     * Cada fila trae: dias (negativo = ya vencido), tramo, valor a costo y a precio de venta.
     */
    public function lotesVencimiento(string $ventana, $categoria = null): array
    {
        $ventana = array_key_exists($ventana, self::VENTANAS) ? $ventana : 'todos';
        $hoy = now()->startOfDay();

        $q = DB::table('lotes as l')
            ->join('productos as p', 'p.id_producto', '=', 'l.id_producto')
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria')
            ->leftJoin('compras as co', 'co.id_compra', '=', 'l.id_compra')
            ->leftJoin('proveedores as pr', 'pr.id_proveedor', '=', 'co.id_proveedor')
            ->whereNotNull('l.fecha_vencimiento')
            ->where('l.cantidad_actual', '>', 0);

        if ($ventana === 'vencidos') {
            $q->whereDate('l.fecha_vencimiento', '<', $hoy->toDateString());
        } else {
            $dias = $ventana === 'todos' ? 90 : (int) $ventana;
            $q->whereDate('l.fecha_vencimiento', '<=', $hoy->copy()->addDays($dias)->toDateString());
        }
        if ($categoria) {
            $q->where('p.id_categoria', (int) $categoria);
        }

        $filas = $q->select(
                'l.id_lote', 'l.numero_lote', 'l.fecha_vencimiento', 'l.cantidad_actual', 'l.costo_unitario', 'l.estado',
                'p.nombre as producto', 'p.precio_venta', 'c.nombre as categoria', 'pr.nombre as proveedor'
            )
            ->orderBy('l.fecha_vencimiento')->orderBy('p.nombre')->get()
            ->map(function ($f) use ($hoy) {
                $venc = Carbon::parse($f->fecha_vencimiento)->startOfDay();
                $f->dias = (int) $hoy->diffInDays($venc, false);
                $f->tramo = $f->dias < 0 ? 'Vencido' : ($f->dias <= 30 ? '0-30 días' : ($f->dias <= 60 ? '31-60 días' : ($f->dias <= 90 ? '61-90 días' : 'Más de 90 días')));
                $f->valor_costo = (float) $f->cantidad_actual * (float) $f->costo_unitario;
                $f->valor_venta = (float) $f->cantidad_actual * (float) $f->precio_venta;
                return $f;
            });

        $tramos = collect(['Vencido', '0-30 días', '31-60 días', '61-90 días', 'Más de 90 días'])->map(function ($t) use ($filas) {
            $g = $filas->where('tramo', $t);
            return (object) [
                'tramo' => $t,
                'lotes' => $g->count(),
                'unidades' => (int) $g->sum('cantidad_actual'),
                'costo' => (float) $g->sum('valor_costo'),
                'venta' => (float) $g->sum('valor_venta'),
            ];
        })->filter(fn ($t) => $t->tramo !== 'Más de 90 días' || $t->lotes > 0)->values();

        $porCategoria = $filas->groupBy(fn ($f) => $f->categoria ?: 'Sin categoría')->map(fn ($g, $k) => (object) [
            'nombre' => $k,
            'lotes' => $g->count(),
            'unidades' => (int) $g->sum('cantidad_actual'),
            'costo' => (float) $g->sum('valor_costo'),
        ])->sortByDesc('costo')->values();

        return [
            'ventana' => $ventana,
            'filas' => $filas,
            'tramos' => $tramos,
            'porCategoria' => $porCategoria,
            'totalCosto' => (float) $filas->sum('valor_costo'),
            'totalVenta' => (float) $filas->sum('valor_venta'),
            'totalUnidades' => (int) $filas->sum('cantidad_actual'),
        ];
    }

    // ------------------------------------------------------------------
    // Kardex (movimientos de inventario)
    // ------------------------------------------------------------------

    /**
     * Cambio de stock de un movimiento. La cantidad se guarda siempre en
     * positivo: ENTRADA suma, SALIDA resta y AJUSTE usa saldo_nuevo − saldo_anterior
     * (si no hay saldos, se trata como baja).
     */
    private const DELTA_SQL = "CASE m.tipo_movimiento WHEN 'ENTRADA' THEN m.cantidad WHEN 'SALIDA' THEN -m.cantidad ELSE CASE WHEN m.saldo_nuevo IS NOT NULL AND m.saldo_anterior IS NOT NULL THEN m.saldo_nuevo - m.saldo_anterior ELSE -m.cantidad END END";

    /** Kardex de un producto: cada movimiento del periodo con su saldo corrido. */
    public function kardexProducto(int $idProducto, Carbon $desde, Carbon $hasta): ?array
    {
        $producto = DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.id_categoria', '=', 'p.id_categoria')
            ->where('p.id_producto', $idProducto)
            ->select('p.id_producto', 'p.nombre', 'p.codigo_barra', 'p.presentacion', 'p.concentracion', 'p.stock_actual', 'p.stock_minimo', 'c.nombre as categoria')
            ->first();
        if (!$producto) {
            return null;
        }

        $todos = DB::table('movimientos_inventario as m')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->leftJoin('lotes as l', 'l.id_lote', '=', 'm.id_lote')
            ->where('m.id_producto', $idProducto)
            ->selectRaw('m.id_movimiento, m.creado_en, m.tipo_movimiento, m.cantidad, m.descripcion, m.motivo, u.nombre_completo as usuario, l.numero_lote, ' . self::DELTA_SQL . ' as delta')
            ->orderBy('m.creado_en')->orderBy('m.id_movimiento')->get();

        // El saldo se reconstruye hacia atrás desde el stock real de hoy, así
        // el último renglón siempre coincide con el stock actual del producto.
        $saldo = (int) $producto->stock_actual;
        for ($i = $todos->count() - 1; $i >= 0; $i--) {
            $todos[$i]->saldo = $saldo;
            $saldo -= (int) $todos[$i]->delta;
        }

        $ini = $desde->format('Y-m-d H:i:s');
        $fin = $hasta->format('Y-m-d H:i:s');
        $periodo = $todos->filter(fn ($r) => $r->creado_en >= $ini && $r->creado_en <= $fin)->values();

        $despues = $todos->filter(fn ($r) => $r->creado_en > $fin)->sum('delta');
        $saldoFinal = (int) $producto->stock_actual - (int) $despues;
        $entradas = (int) $periodo->where('delta', '>', 0)->sum('delta');
        $salidas = (int) abs($periodo->where('delta', '<', 0)->sum('delta'));

        return [
            'producto' => $producto,
            'filas' => $periodo,
            'saldoInicial' => $saldoFinal - $entradas + $salidas,
            'saldoFinal' => $saldoFinal,
            'entradas' => $entradas,
            'salidas' => $salidas,
            'totalMovimientos' => $todos->count(),
        ];
    }

    /** Consulta de resumen por producto (saldo inicial, entradas, salidas y saldo final del periodo). */
    public function kardexResumenQuery(Carbon $desde, Carbon $hasta, ?string $q, bool $soloConMovimientos)
    {
        $ini = $desde->format('Y-m-d H:i:s');
        $fin = $hasta->format('Y-m-d H:i:s');
        $d = self::DELTA_SQL;

        $query = DB::table('productos as p')
            ->leftJoin('movimientos_inventario as m', 'm.id_producto', '=', 'p.id_producto')
            ->selectRaw(
                "p.id_producto, p.nombre, p.codigo_barra, p.stock_actual,
                 COALESCE(SUM(CASE WHEN m.creado_en >= ? AND m.creado_en <= ? AND ({$d}) > 0 THEN ({$d}) ELSE 0 END), 0) as entradas,
                 COALESCE(-SUM(CASE WHEN m.creado_en >= ? AND m.creado_en <= ? AND ({$d}) < 0 THEN ({$d}) ELSE 0 END), 0) as salidas,
                 COALESCE(SUM(CASE WHEN m.creado_en > ? THEN ({$d}) ELSE 0 END), 0) as despues",
                [$ini, $fin, $ini, $fin, $fin]
            )
            ->groupBy('p.id_producto', 'p.nombre', 'p.codigo_barra', 'p.stock_actual')
            ->orderBy('p.nombre');

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('p.nombre', 'like', '%' . $q . '%')->orWhere('p.codigo_barra', 'like', '%' . $q . '%');
            });
        }
        if ($soloConMovimientos) {
            $query->havingRaw(
                "SUM(CASE WHEN m.creado_en >= ? AND m.creado_en <= ? THEN 1 ELSE 0 END) > 0",
                [$ini, $fin]
            );
        }
        return $query;
    }

    /** Completa saldo_final y saldo_inicial de una fila del resumen. */
    public static function completarResumen($r)
    {
        $r->entradas = (int) $r->entradas;
        $r->salidas = (int) $r->salidas;
        $r->saldo_final = (int) $r->stock_actual - (int) $r->despues;
        $r->saldo_inicial = $r->saldo_final - $r->entradas + $r->salidas;
        return $r;
    }

    // ------------------------------------------------------------------
    // Ventas detalladas
    // ------------------------------------------------------------------
    public function ventasQuery(Carbon $desde, Carbon $hasta, ?string $estado, ?string $metodo, $cajero)
    {
        $q = DB::table('ventas as v')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'v.id_usuario')
            ->whereBetween('v.fecha_venta', [$desde, $hasta]);

        $estado = $estado ?: 'COMPLETADA';
        if (in_array($estado, ['COMPLETADA', 'ANULADA', 'PENDIENTE'], true)) {
            $q->where('v.estado', $estado);
        }
        if ($metodo && in_array($metodo, self::METODOS, true)) {
            $q->where('v.metodo_pago', $metodo);
        }
        if ($cajero) {
            $q->where('v.id_usuario', (int) $cajero);
        }
        return $q;
    }

    public function ventasResumen($queryFactory): array
    {
        $tot = $queryFactory()->selectRaw('COUNT(*) as tickets, COALESCE(SUM(v.subtotal),0) as subtotal, COALESCE(SUM(v.igv),0) as igv, COALESCE(SUM(v.total),0) as total')->first();

        $porDia = $queryFactory()->selectRaw('DATE(v.fecha_venta) as fecha, COUNT(*) as tickets, SUM(v.total) as total')
            ->groupBy('fecha')->orderBy('fecha')->get();
        $porCajero = $queryFactory()->selectRaw("COALESCE(u.nombre_completo,'—') as nombre, COUNT(*) as tickets, SUM(v.total) as total")
            ->groupBy('u.id_usuario', 'u.nombre_completo')->orderByDesc('total')->get();
        $porMetodo = $queryFactory()->selectRaw('v.metodo_pago as nombre, COUNT(*) as tickets, SUM(v.total) as total')
            ->groupBy('v.metodo_pago')->orderByDesc('total')->get();

        return compact('tot', 'porDia', 'porCajero', 'porMetodo');
    }

    public static function filaVenta($q)
    {
        return $q->select('v.id_venta', 'v.numero_ticket', 'v.fecha_venta', 'v.cliente_nombre', 'u.nombre_completo as cajero', 'v.metodo_pago', 'v.subtotal', 'v.igv', 'v.total', 'v.estado')
            ->orderByDesc('v.fecha_venta');
    }
}
