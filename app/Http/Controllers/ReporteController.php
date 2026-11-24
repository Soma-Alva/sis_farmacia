<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use App\Support\XlsxBuilder as X;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function __construct(private ReporteService $reportes)
    {
    }

    public function index()
    {
        return view('reportes.index');
    }

    // ---------------------------------------------------------------
    // Ingresos y egresos
    // ---------------------------------------------------------------
    public function ingresosEgresos(Request $request)
    {
        [$periodo, $desde, $hasta] = $this->rango($request);
        $d = $this->reportes->ingresosEgresos($desde, $hasta);

        return view('reportes.ingresos_egresos', $d + compact('periodo', 'desde', 'hasta'));
    }

    public function ingresosEgresosExcel(Request $request)
    {
        [, $desde, $hasta] = $this->rango($request);
        $d = $this->reportes->ingresosEgresos($desde, $hasta);

        $x = new X();

        // --- Resumen ---
        $h = $x->hoja('Resumen');
        $this->encabezado($x, $h, 'Reporte de ingresos y egresos', $desde, $hasta);
        $x->ancho($h, 1, 38); $x->ancho($h, 2, 12); $x->ancho($h, 3, 20);

        $x->celda($h, 5, 1, 'INGRESOS', X::S_SECCION);
        $x->fila($h, 6, ['Concepto', 'Tickets', 'Monto'], X::S_ENCABEZADO);
        $r = 7;
        foreach ($d['porMetodo'] as $m) {
            $x->celda($h, $r, 1, 'Ventas · ' . ReporteService::etiquetaMetodo($m->metodo), X::S_TEXTO);
            $x->celda($h, $r, 2, $m->tickets, X::S_ENTERO);
            $x->celda($h, $r, 3, $m->total, X::S_DINERO);
            $r++;
        }
        $x->celda($h, $r, 1, 'Ingresos manuales de caja', X::S_TEXTO);
        $x->celda($h, $r, 2, $d['ingresosManuales']->count(), X::S_ENTERO);
        $x->celda($h, $r, 3, $d['totalIngresosManuales'], X::S_DINERO);
        $finIng = $r; $r++;
        $x->celda($h, $r, 1, 'TOTAL INGRESOS', X::S_TOTAL_ETIQUETA);
        $x->formula($h, $r, 2, "SUM(B7:B{$finIng})", $d['porMetodo']->sum('tickets') + $d['ingresosManuales']->count(), X::S_TOTAL_ENTERO);
        $x->formula($h, $r, 3, "SUM(C7:C{$finIng})", $d['totalIngresos'], X::S_TOTAL_DINERO);
        $filaTotIng = $r;

        $r += 2;
        $x->celda($h, $r, 1, 'EGRESOS', X::S_SECCION); $r++;
        $x->fila($h, $r, ['Concepto', 'Cantidad', 'Monto'], X::S_ENCABEZADO); $r++;
        $iniEgr = $r;
        $x->celda($h, $r, 1, 'Compras de mercadería (aprobadas)', X::S_TEXTO);
        $x->celda($h, $r, 2, $d['compras']->count(), X::S_ENTERO);
        $x->celda($h, $r, 3, $d['totalCompras'], X::S_DINERO); $r++;
        $x->celda($h, $r, 1, 'Egresos manuales de caja', X::S_TEXTO);
        $x->celda($h, $r, 2, $d['egresosCaja']->count(), X::S_ENTERO);
        $x->celda($h, $r, 3, $d['totalEgresosCaja'], X::S_DINERO);
        $finEgr = $r; $r++;
        $x->celda($h, $r, 1, 'TOTAL EGRESOS', X::S_TOTAL_ETIQUETA);
        $x->formula($h, $r, 2, "SUM(B{$iniEgr}:B{$finEgr})", $d['compras']->count() + $d['egresosCaja']->count(), X::S_TOTAL_ENTERO);
        $x->formula($h, $r, 3, "SUM(C{$iniEgr}:C{$finEgr})", $d['totalEgresos'], X::S_TOTAL_DINERO);
        $filaTotEgr = $r;

        $r += 2;
        $x->celda($h, $r, 1, 'NETO (ingresos − egresos)', X::S_TOTAL_ETIQUETA);
        $x->celda($h, $r, 2, '', X::S_TOTAL_ETIQUETA);
        $x->formula($h, $r, 3, "C{$filaTotIng}-C{$filaTotEgr}", $d['neto'], X::S_TOTAL_DINERO);

        // --- Por día ---
        $h = $x->hoja('Por día');
        $this->encabezado($x, $h, 'Ingresos y egresos por día', $desde, $hasta);
        foreach ([1 => 14, 2 => 20, 3 => 20, 4 => 20] as $c => $w) { $x->ancho($h, $c, $w); }
        $x->fila($h, 5, ['Fecha', 'Ingresos', 'Egresos', 'Neto'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($d['dias'] as $fecha => $v) {
            $x->celda($h, $r, 1, Carbon::parse($fecha)->format('d/m/Y'), X::S_TEXTO);
            $x->celda($h, $r, 2, $v['ing'], X::S_DINERO);
            $x->celda($h, $r, 3, $v['egr'], X::S_DINERO);
            $x->formula($h, $r, 4, "B{$r}-C{$r}", $v['ing'] - $v['egr'], X::S_DINERO);
            $r++;
        }
        $ult = $r - 1;
        $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
        $x->formula($h, $r, 2, "SUM(B6:B{$ult})", $d['totalIngresos'], X::S_TOTAL_DINERO);
        $x->formula($h, $r, 3, "SUM(C6:C{$ult})", $d['totalEgresos'], X::S_TOTAL_DINERO);
        $x->formula($h, $r, 4, "SUM(D6:D{$ult})", $d['neto'], X::S_TOTAL_DINERO);
        $x->congelar($h, 6);

        // --- Detalles ---
        $this->hojaMovimientos($x, 'Ingresos manuales', 'Ingresos manuales de caja', $desde, $hasta, $d['ingresosManuales']);
        $this->hojaCompras($x, $desde, $hasta, $d['compras'], $d['totalCompras']);
        $this->hojaMovimientos($x, 'Egresos de caja', 'Egresos manuales de caja', $desde, $hasta, $d['egresosCaja']);

        return $this->descargar($x, 'ingresos_egresos', $desde, $hasta);
    }

    // ---------------------------------------------------------------
    // Balance de rentabilidad
    // ---------------------------------------------------------------
    public function balance(Request $request)
    {
        [$periodo, $desde, $hasta] = $this->rango($request);
        $b = $this->reportes->balance($desde, $hasta);

        return view('reportes.balance', $b + compact('periodo', 'desde', 'hasta'));
    }

    public function balanceExcel(Request $request)
    {
        [, $desde, $hasta] = $this->rango($request);
        $b = $this->reportes->balance($desde, $hasta);
        $a = $b['actual'];
        $p = $b['anterior'];

        $x = new X();
        $h = $x->hoja('Balance');
        $this->encabezado($x, $h, 'Balance de rentabilidad', $desde, $hasta);
        $x->ancho($h, 1, 42); $x->ancho($h, 2, 22); $x->ancho($h, 3, 22); $x->ancho($h, 4, 14);

        $x->fila($h, 5, ['Concepto', 'Periodo actual', 'Periodo anterior', 'Variación'], X::S_ENCABEZADO);
        $x->celda($h, 6, 1, 'Ventas netas (sin IVA)', X::S_TEXTO);
        $x->celda($h, 6, 2, $a['neto'], X::S_DINERO);
        $x->celda($h, 6, 3, $p['neto'], X::S_DINERO);
        $x->celda($h, 7, 1, '(−) Costo de lo vendido', X::S_TEXTO);
        $x->celda($h, 7, 2, $a['costo'], X::S_DINERO);
        $x->celda($h, 7, 3, $p['costo'], X::S_DINERO);
        $x->celda($h, 8, 1, 'UTILIDAD DEL PERIODO', X::S_TOTAL_ETIQUETA);
        $x->formula($h, 8, 2, 'B6-B7', $a['utilidad'], X::S_TOTAL_DINERO);
        $x->formula($h, 8, 3, 'C6-C7', $p['utilidad'], X::S_TOTAL_DINERO);
        $x->celda($h, 9, 1, 'Margen de utilidad', X::S_TEXTO);
        $x->formula($h, 9, 2, 'IF(B6>0,B8/B6,0)', $a['margen'], X::S_PORCENTAJE);
        $x->formula($h, 9, 3, 'IF(C6>0,C8/C6,0)', $p['margen'], X::S_PORCENTAJE);
        foreach ([6, 7, 8] as $f) {
            $col = ['6' => ['neto'], '7' => ['costo'], '8' => ['utilidad']][(string) $f][0];
            $var = $p[$col] != 0 ? ($a[$col] - $p[$col]) / abs($p[$col]) : ($a[$col] != 0 ? 1 : 0);
            $x->formula($h, $f, 4, "IF(C{$f}<>0,(B{$f}-C{$f})/ABS(C{$f}),IF(B{$f}<>0,1,0))", $var, X::S_PORCENTAJE);
        }
        $x->celda($h, 9, 4, '', X::S_TEXTO);

        $x->celda($h, 11, 1, 'RESULTADO', X::S_SECCION);
        $x->celda($h, 11, 2, $b['veredicto'], $a['utilidad'] >= 0 ? X::S_BUENO : X::S_MALO);

        $x->celda($h, 13, 1, 'Datos informativos', X::S_SECCION);
        $x->fila($h, 14, ['Concepto', 'Monto'], X::S_ENCABEZADO);
        $info = [
            ['Tickets (ventas completadas)', $a['tickets'], X::S_ENTERO],
            ['IVA cobrado (no es ingreso propio)', $a['iva'], X::S_DINERO],
            ['Total cobrado a clientes (con IVA)', $a['cobrado'], X::S_DINERO],
            ['Ventas anuladas (cantidad)', (int) $b['anuladas']->n, X::S_ENTERO],
            ['Ventas anuladas (monto)', (float) $b['anuladas']->total, X::S_DINERO],
            ['Compras de mercadería del periodo', $b['comprasPeriodo'], X::S_DINERO],
        ];
        $r = 15;
        foreach ($info as [$et, $v, $st]) {
            $x->celda($h, $r, 1, $et, X::S_TEXTO);
            $x->celda($h, $r, 2, $v, $st);
            $r++;
        }

        $r++;
        $x->celda($h, $r, 1, 'Situación actual (hoy)', X::S_SECCION); $r++;
        $x->fila($h, $r, ['Concepto', 'Monto'], X::S_ENCABEZADO); $r++;
        $situacion = [
            ['Inventario a costo', $b['inventarioCosto']],
            ['Inventario a precio de venta', $b['inventarioVenta']],
            ['Utilidad potencial del inventario', $b['inventarioVenta'] - $b['inventarioCosto']],
            ['Efectivo esperado en caja' . ($b['efectivoCaja'] === null ? ' (caja cerrada)' : ''), $b['efectivoCaja'] ?? 0],
            ['Costo de lotes vencidos con stock (pérdida)', $b['perdidaVencidos']],
        ];
        foreach ($situacion as [$et, $v]) {
            $x->celda($h, $r, 1, $et, X::S_TEXTO);
            $x->celda($h, $r, 2, $v, X::S_DINERO);
            $r++;
        }

        $this->hojaRentabilidad($x, 'Por producto', 'Rentabilidad por producto (top 15)', $desde, $hasta, $b['porProducto'], true);
        $this->hojaRentabilidad($x, 'Por categoría', 'Rentabilidad por categoría', $desde, $hasta, $b['porCategoria'], false);

        return $this->descargar($x, 'balance_rentabilidad', $desde, $hasta);
    }

    // ---------------------------------------------------------------
    // Ventas detalladas
    // ---------------------------------------------------------------
    public function ventas(Request $request)
    {
        [$periodo, $desde, $hasta] = $this->rango($request);
        $estado = $request->input('estado', 'COMPLETADA');
        $metodo = $request->input('metodo');
        $cajero = $request->input('cajero');

        $factory = fn () => $this->reportes->ventasQuery($desde, $hasta, $estado, $metodo, $cajero);
        $resumen = $this->reportes->ventasResumen($factory);
        $ventas = ReporteService::filaVenta($factory())->paginate(15)->withQueryString();
        $cajeros = DB::table('usuarios')->select('id_usuario', 'nombre_completo')->orderBy('nombre_completo')->get();

        return view('reportes.ventas', $resumen + compact('periodo', 'desde', 'hasta', 'estado', 'metodo', 'cajero', 'ventas', 'cajeros'));
    }

    public function ventasExcel(Request $request)
    {
        [, $desde, $hasta] = $this->rango($request);
        $estado = $request->input('estado', 'COMPLETADA');
        $factory = fn () => $this->reportes->ventasQuery($desde, $hasta, $estado, $request->input('metodo'), $request->input('cajero'));
        $res = $this->reportes->ventasResumen($factory);
        $filas = ReporteService::filaVenta($factory())->get();

        $x = new X();

        $h = $x->hoja('Resumen');
        $this->encabezado($x, $h, 'Ventas detalladas · ' . ['COMPLETADA' => 'Completadas', 'ANULADA' => 'Anuladas', 'PENDIENTE' => 'Pendientes', 'TODAS' => 'Todas'][$estado ?: 'COMPLETADA'] ?? 'Todas', $desde, $hasta);
        $x->ancho($h, 1, 34); $x->ancho($h, 2, 12); $x->ancho($h, 3, 20);
        $x->fila($h, 5, ['Concepto', 'Tickets', 'Monto'], X::S_ENCABEZADO);
        $x->celda($h, 6, 1, 'Subtotal (sin IVA)', X::S_TEXTO);  $x->celda($h, 6, 2, '', X::S_TEXTO); $x->celda($h, 6, 3, (float) $res['tot']->subtotal, X::S_DINERO);
        $x->celda($h, 7, 1, 'IVA', X::S_TEXTO);                $x->celda($h, 7, 2, '', X::S_TEXTO); $x->celda($h, 7, 3, (float) $res['tot']->igv, X::S_DINERO);
        $x->celda($h, 8, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
        $x->celda($h, 8, 2, (int) $res['tot']->tickets, X::S_TOTAL_ENTERO);
        $x->formula($h, 8, 3, 'C6+C7', (float) $res['tot']->total, X::S_TOTAL_DINERO);

        $r = 10;
        $x->celda($h, $r, 1, 'Por método de pago', X::S_SECCION); $r++;
        $x->fila($h, $r, ['Método', 'Tickets', 'Monto'], X::S_ENCABEZADO); $r++;
        foreach ($res['porMetodo'] as $m) {
            $x->celda($h, $r, 1, ReporteService::etiquetaMetodo($m->nombre), X::S_TEXTO);
            $x->celda($h, $r, 2, (int) $m->tickets, X::S_ENTERO);
            $x->celda($h, $r, 3, (float) $m->total, X::S_DINERO);
            $r++;
        }
        $r++;
        $x->celda($h, $r, 1, 'Por cajero', X::S_SECCION); $r++;
        $x->fila($h, $r, ['Cajero', 'Tickets', 'Monto'], X::S_ENCABEZADO); $r++;
        foreach ($res['porCajero'] as $c) {
            $x->celda($h, $r, 1, $c->nombre, X::S_TEXTO);
            $x->celda($h, $r, 2, (int) $c->tickets, X::S_ENTERO);
            $x->celda($h, $r, 3, (float) $c->total, X::S_DINERO);
            $r++;
        }

        $h = $x->hoja('Detalle');
        $this->encabezado($x, $h, 'Detalle de ventas', $desde, $hasta);
        foreach ([1 => 16, 2 => 17, 3 => 26, 4 => 24, 5 => 15, 6 => 15, 7 => 13, 8 => 16, 9 => 14] as $c => $w) { $x->ancho($h, $c, $w); }
        $x->fila($h, 5, ['Ticket', 'Fecha', 'Cliente', 'Cajero', 'Método', 'Subtotal', 'IVA', 'Total', 'Estado'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($filas as $v) {
            $x->celda($h, $r, 1, $v->numero_ticket, X::S_TEXTO);
            $x->celda($h, $r, 2, Carbon::parse($v->fecha_venta)->format('d/m/Y H:i'), X::S_TEXTO);
            $x->celda($h, $r, 3, $v->cliente_nombre ?: 'Cliente General', X::S_TEXTO);
            $x->celda($h, $r, 4, $v->cajero ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 5, $v->metodo_pago, X::S_TEXTO);
            $x->celda($h, $r, 6, (float) $v->subtotal, X::S_DINERO);
            $x->celda($h, $r, 7, (float) $v->igv, X::S_DINERO);
            $x->celda($h, $r, 8, (float) $v->total, X::S_DINERO);
            $x->celda($h, $r, 9, $v->estado, X::S_TEXTO);
            $r++;
        }
        if ($filas->count() > 0) {
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTAL (' . $filas->count() . ' ventas)', X::S_TOTAL_ETIQUETA);
            for ($c = 2; $c <= 5; $c++) { $x->celda($h, $r, $c, '', X::S_TOTAL_ETIQUETA); }
            $x->formula($h, $r, 6, "SUM(F6:F{$ult})", (float) $res['tot']->subtotal, X::S_TOTAL_DINERO);
            $x->formula($h, $r, 7, "SUM(G6:G{$ult})", (float) $res['tot']->igv, X::S_TOTAL_DINERO);
            $x->formula($h, $r, 8, "SUM(H6:H{$ult})", (float) $res['tot']->total, X::S_TOTAL_DINERO);
            $x->celda($h, $r, 9, '', X::S_TOTAL_ETIQUETA);
        }
        $x->congelar($h, 6);

        $h = $x->hoja('Por día');
        $this->encabezado($x, $h, 'Ventas por día', $desde, $hasta);
        $x->ancho($h, 1, 14); $x->ancho($h, 2, 12); $x->ancho($h, 3, 20);
        $x->fila($h, 5, ['Fecha', 'Tickets', 'Monto'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($res['porDia'] as $dia) {
            $x->celda($h, $r, 1, Carbon::parse($dia->fecha)->format('d/m/Y'), X::S_TEXTO);
            $x->celda($h, $r, 2, (int) $dia->tickets, X::S_ENTERO);
            $x->celda($h, $r, 3, (float) $dia->total, X::S_DINERO);
            $r++;
        }
        if ($res['porDia']->count() > 0) {
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
            $x->formula($h, $r, 2, "SUM(B6:B{$ult})", (int) $res['tot']->tickets, X::S_TOTAL_ENTERO);
            $x->formula($h, $r, 3, "SUM(C6:C{$ult})", (float) $res['tot']->total, X::S_TOTAL_DINERO);
        }

        return $this->descargar($x, 'ventas_detalladas', $desde, $hasta);
    }

    // ---------------------------------------------------------------
    // Kardex
    // ---------------------------------------------------------------
    public function kardex(Request $request)
    {
        [$periodo, $desde, $hasta] = $this->rango($request);
        $idProducto = (int) $request->input('producto');
        $q = trim((string) $request->input('q'));

        if ($idProducto) {
            $k = $this->reportes->kardexProducto($idProducto, $desde, $hasta);
            if ($k) {
                return view('reportes.kardex', $k + compact('periodo', 'desde', 'hasta'));
            }
        }

        $soloMov = $this->soloMov($request);
        $resumen = $this->reportes->kardexResumenQuery($desde, $hasta, $q, $soloMov)->paginate(15)->withQueryString();
        $resumen->getCollection()->transform(fn ($r) => ReporteService::completarResumen($r));

        return view('reportes.kardex_resumen', compact('periodo', 'desde', 'hasta', 'q', 'soloMov', 'resumen'));
    }

    public function kardexExcel(Request $request)
    {
        [, $desde, $hasta] = $this->rango($request);
        $idProducto = (int) $request->input('producto');
        $x = new X();

        $k = $idProducto ? $this->reportes->kardexProducto($idProducto, $desde, $hasta) : null;

        if ($k) {
            $p = $k['producto'];
            $h = $x->hoja('Kardex');
            $this->encabezado($x, $h, 'Kardex · ' . $p->nombre . ($p->codigo_barra ? ' (' . $p->codigo_barra . ')' : ''), $desde, $hasta);
            foreach ([1 => 17, 2 => 11, 3 => 44, 4 => 16, 5 => 22, 6 => 11, 7 => 11, 8 => 11] as $c => $w) { $x->ancho($h, $c, $w); }
            $x->fila($h, 5, ['Fecha', 'Tipo', 'Detalle', 'Lote', 'Usuario', 'Entrada', 'Salida', 'Saldo'], X::S_ENCABEZADO);
            $x->celda($h, 6, 1, 'Saldo inicial', X::S_TOTAL_ETIQUETA);
            for ($c = 2; $c <= 7; $c++) { $x->celda($h, 6, $c, '', X::S_TOTAL_ETIQUETA); }
            $x->celda($h, 6, 8, $k['saldoInicial'], X::S_TOTAL_ENTERO);
            $r = 7;
            foreach ($k['filas'] as $f) {
                $x->celda($h, $r, 1, Carbon::parse($f->creado_en)->format('d/m/Y H:i'), X::S_TEXTO);
                $x->celda($h, $r, 2, ucfirst(strtolower($f->tipo_movimiento)), X::S_TEXTO);
                $x->celda($h, $r, 3, $f->descripcion ?: ($f->motivo ?: '—'), X::S_TEXTO);
                $x->celda($h, $r, 4, $f->numero_lote ?: '—', X::S_TEXTO);
                $x->celda($h, $r, 5, $f->usuario ?: '—', X::S_TEXTO);
                $x->celda($h, $r, 6, $f->delta > 0 ? (int) $f->delta : '', X::S_ENTERO);
                $x->celda($h, $r, 7, $f->delta < 0 ? (int) abs($f->delta) : '', X::S_ENTERO);
                $x->formula($h, $r, 8, "H" . ($r - 1) . "+F{$r}-G{$r}", (int) $f->saldo, X::S_ENTERO);
                $r++;
            }
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTALES / Saldo final', X::S_TOTAL_ETIQUETA);
            for ($c = 2; $c <= 5; $c++) { $x->celda($h, $r, $c, '', X::S_TOTAL_ETIQUETA); }
            $x->formula($h, $r, 6, "SUM(F7:F{$ult})", $k['entradas'], X::S_TOTAL_ENTERO);
            $x->formula($h, $r, 7, "SUM(G7:G{$ult})", $k['salidas'], X::S_TOTAL_ENTERO);
            $x->formula($h, $r, 8, "H6+F{$r}-G{$r}", $k['saldoFinal'], X::S_TOTAL_ENTERO);
            $x->congelar($h, 6);

            return $this->descargar($x, 'kardex_' . $p->id_producto, $desde, $hasta);
        }

        $q = trim((string) $request->input('q'));
        $soloMov = $this->soloMov($request);
        $filas = $this->reportes->kardexResumenQuery($desde, $hasta, $q, $soloMov)->get()
            ->map(fn ($r) => ReporteService::completarResumen($r));

        $h = $x->hoja('Resumen de inventario');
        $this->encabezado($x, $h, 'Kardex · resumen de movimientos por producto', $desde, $hasta);
        foreach ([1 => 38, 2 => 18, 3 => 14, 4 => 12, 5 => 12, 6 => 14] as $c => $w) { $x->ancho($h, $c, $w); }
        $x->fila($h, 5, ['Producto', 'Código', 'Saldo inicial', 'Entradas', 'Salidas', 'Saldo final'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($filas as $f) {
            $x->celda($h, $r, 1, $f->nombre, X::S_TEXTO);
            $x->celda($h, $r, 2, $f->codigo_barra ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 3, $f->saldo_inicial, X::S_ENTERO);
            $x->celda($h, $r, 4, $f->entradas, X::S_ENTERO);
            $x->celda($h, $r, 5, $f->salidas, X::S_ENTERO);
            $x->formula($h, $r, 6, "C{$r}+D{$r}-E{$r}", $f->saldo_final, X::S_ENTERO);
            $r++;
        }
        if ($filas->count() > 0) {
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
            $x->celda($h, $r, 2, '', X::S_TOTAL_ETIQUETA);
            foreach ([3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F'] as $c => $l) {
                $v = [3 => $filas->sum('saldo_inicial'), 4 => $filas->sum('entradas'), 5 => $filas->sum('salidas'), 6 => $filas->sum('saldo_final')][$c];
                $x->formula($h, $r, $c, "SUM({$l}6:{$l}{$ult})", $v, X::S_TOTAL_ENTERO);
            }
        }
        $x->congelar($h, 6);

        return $this->descargar($x, 'kardex_resumen', $desde, $hasta);
    }

    // ---------------------------------------------------------------
    // Lotes vencidos y por vencer
    // ---------------------------------------------------------------
    public function lotes(Request $request)
    {
        $ventana = (string) $request->input('ventana', 'todos');
        $categoria = $request->input('categoria');
        $d = $this->reportes->lotesVencimiento($ventana, $categoria);
        $categorias = DB::table('categorias')->select('id_categoria', 'nombre')->orderBy('nombre')->get();

        // Paginado a mano (15 por página), igual que el resto de listados
        $porPagina = 15;
        $pagina = LengthAwarePaginator::resolveCurrentPage('page');
        $lotes = new LengthAwarePaginator(
            $d['filas']->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
            $d['filas']->count(), $porPagina, $pagina,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('reportes.lotes', $d + compact('categoria', 'categorias', 'lotes'));
    }

    public function lotesExcel(Request $request)
    {
        $d = $this->reportes->lotesVencimiento((string) $request->input('ventana', 'todos'), $request->input('categoria'));
        $filas = $d['filas'];
        $x = new X();

        $h = $x->hoja('Resumen');
        $x->celda($h, 1, 1, config('farmacia.nombre', 'Grey Farmacia'), X::S_TITULO);
        $x->celda($h, 2, 1, 'Lotes vencidos y por vencer · ' . ReporteService::VENTANAS[$d['ventana']], X::S_SECCION);
        $x->celda($h, 3, 1, 'Fecha de corte: ' . now()->format('d/m/Y') . ' · Generado: ' . now()->format('d/m/Y H:i') . ' por ' . (optional(auth()->user())->nombre_completo ?? ''), X::S_SUBTITULO);
        foreach ([1 => 22, 2 => 10, 3 => 12, 4 => 20, 5 => 22] as $c => $w) { $x->ancho($h, $c, $w); }

        $x->fila($h, 5, ['Tramo', 'Lotes', 'Unidades', 'Valor a costo', 'Valor a precio venta'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($d['tramos'] as $t) {
            $x->celda($h, $r, 1, $t->tramo, X::S_TEXTO);
            $x->celda($h, $r, 2, $t->lotes, X::S_ENTERO);
            $x->celda($h, $r, 3, $t->unidades, X::S_ENTERO);
            $x->celda($h, $r, 4, $t->costo, X::S_DINERO);
            $x->celda($h, $r, 5, $t->venta, X::S_DINERO);
            $r++;
        }
        $ult = $r - 1;
        $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
        $x->formula($h, $r, 2, "SUM(B6:B{$ult})", $filas->count(), X::S_TOTAL_ENTERO);
        $x->formula($h, $r, 3, "SUM(C6:C{$ult})", $d['totalUnidades'], X::S_TOTAL_ENTERO);
        $x->formula($h, $r, 4, "SUM(D6:D{$ult})", $d['totalCosto'], X::S_TOTAL_DINERO);
        $x->formula($h, $r, 5, "SUM(E6:E{$ult})", $d['totalVenta'], X::S_TOTAL_DINERO);

        $r += 2;
        $x->celda($h, $r, 1, 'Por categoría', X::S_SECCION); $r++;
        $x->fila($h, $r, ['Categoría', 'Lotes', 'Unidades', 'Valor a costo'], X::S_ENCABEZADO); $r++;
        foreach ($d['porCategoria'] as $c) {
            $x->celda($h, $r, 1, $c->nombre, X::S_TEXTO);
            $x->celda($h, $r, 2, $c->lotes, X::S_ENTERO);
            $x->celda($h, $r, 3, $c->unidades, X::S_ENTERO);
            $x->celda($h, $r, 4, $c->costo, X::S_DINERO);
            $r++;
        }

        $h = $x->hoja('Detalle');
        $x->celda($h, 1, 1, config('farmacia.nombre', 'Grey Farmacia'), X::S_TITULO);
        $x->celda($h, 2, 1, 'Detalle de lotes', X::S_SECCION);
        $x->celda($h, 3, 1, 'Fecha de corte: ' . now()->format('d/m/Y'), X::S_SUBTITULO);
        foreach ([1 => 34, 2 => 20, 3 => 16, 4 => 14, 5 => 12, 6 => 16, 7 => 10, 8 => 14, 9 => 16, 10 => 16, 11 => 26] as $c => $w) { $x->ancho($h, $c, $w); }
        $x->fila($h, 5, ['Producto', 'Categoría', 'Lote', 'Vence', 'Días', 'Tramo', 'Cant.', 'Costo unit.', 'Valor a costo', 'Valor a venta', 'Proveedor'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($filas as $f) {
            $x->celda($h, $r, 1, $f->producto, X::S_TEXTO);
            $x->celda($h, $r, 2, $f->categoria ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 3, $f->numero_lote ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 4, Carbon::parse($f->fecha_vencimiento)->format('d/m/Y'), X::S_TEXTO);
            $x->celda($h, $r, 5, $f->dias, X::S_ENTERO);
            $x->celda($h, $r, 6, $f->tramo, $f->dias < 0 ? X::S_MALO : X::S_TEXTO);
            $x->celda($h, $r, 7, (int) $f->cantidad_actual, X::S_ENTERO);
            $x->celda($h, $r, 8, (float) $f->costo_unitario, X::S_DINERO);
            $x->formula($h, $r, 9, "G{$r}*H{$r}", $f->valor_costo, X::S_DINERO);
            $x->celda($h, $r, 10, $f->valor_venta, X::S_DINERO);
            $x->celda($h, $r, 11, $f->proveedor ?: '—', X::S_TEXTO);
            $r++;
        }
        if ($filas->count() > 0) {
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
            for ($c = 2; $c <= 6; $c++) { $x->celda($h, $r, $c, '', X::S_TOTAL_ETIQUETA); }
            $x->formula($h, $r, 7, "SUM(G6:G{$ult})", $d['totalUnidades'], X::S_TOTAL_ENTERO);
            $x->celda($h, $r, 8, '', X::S_TOTAL_ETIQUETA);
            $x->formula($h, $r, 9, "SUM(I6:I{$ult})", $d['totalCosto'], X::S_TOTAL_DINERO);
            $x->formula($h, $r, 10, "SUM(J6:J{$ult})", $d['totalVenta'], X::S_TOTAL_DINERO);
            $x->celda($h, $r, 11, '', X::S_TOTAL_ETIQUETA);
        }
        $x->congelar($h, 6);

        $tmp = tempnam(sys_get_temp_dir(), 'rep');
        $x->guardar($tmp);

        return response()->download($tmp, 'lotes_vencimiento_' . now()->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ---------------------------------------------------------------
    // Utilidades
    // ---------------------------------------------------------------
    /** Por defecto solo productos con movimientos; si se envió el formulario, manda el checkbox. */
    private function soloMov(Request $request): bool
    {
        return $request->has('q') ? $request->boolean('solo_mov') : $request->boolean('solo_mov', true);
    }

    private function rango(Request $request): array
    {
        return ReporteService::rango($request->input('periodo'), $request->input('desde'), $request->input('hasta'));
    }

    private function encabezado(X $x, int $h, string $titulo, Carbon $desde, Carbon $hasta): void
    {
        $x->celda($h, 1, 1, config('farmacia.nombre', 'Grey Farmacia'), X::S_TITULO);
        $x->celda($h, 2, 1, $titulo, X::S_SECCION);
        $x->celda($h, 3, 1, 'Periodo: ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y')
            . ' · Generado: ' . now()->format('d/m/Y H:i') . ' por ' . (optional(auth()->user())->nombre_completo ?? ''), X::S_SUBTITULO);
    }

    private function hojaMovimientos(X $x, string $hoja, string $titulo, Carbon $desde, Carbon $hasta, $filas): void
    {
        $h = $x->hoja($hoja);
        $this->encabezado($x, $h, $titulo, $desde, $hasta);
        foreach ([1 => 17, 2 => 36, 3 => 30, 4 => 15, 5 => 18, 6 => 22] as $c => $w) { $x->ancho($h, $c, $w); }
        $x->fila($h, 5, ['Fecha', 'Descripción', 'Motivo', 'Forma de pago', 'Monto', 'Usuario'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($filas as $f) {
            $x->celda($h, $r, 1, Carbon::parse($f->fecha)->format('d/m/Y H:i'), X::S_TEXTO);
            $x->celda($h, $r, 2, $f->descripcion ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 3, $f->motivo ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 4, $f->forma_pago ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 5, (float) $f->monto, X::S_DINERO);
            $x->celda($h, $r, 6, $f->usuario ?: '—', X::S_TEXTO);
            $r++;
        }
        if ($filas->count() > 0) {
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
            for ($c = 2; $c <= 4; $c++) { $x->celda($h, $r, $c, '', X::S_TOTAL_ETIQUETA); }
            $x->formula($h, $r, 5, "SUM(E6:E{$ult})", (float) $filas->sum('monto'), X::S_TOTAL_DINERO);
            $x->celda($h, $r, 6, '', X::S_TOTAL_ETIQUETA);
        }
        $x->congelar($h, 6);
    }

    private function hojaCompras(X $x, Carbon $desde, Carbon $hasta, $filas, float $total): void
    {
        $h = $x->hoja('Compras');
        $this->encabezado($x, $h, 'Compras de mercadería aprobadas', $desde, $hasta);
        foreach ([1 => 14, 2 => 20, 3 => 36, 4 => 18] as $c => $w) { $x->ancho($h, $c, $w); }
        $x->fila($h, 5, ['Fecha', 'Factura', 'Proveedor', 'Total'], X::S_ENCABEZADO);
        $r = 6;
        foreach ($filas as $f) {
            $x->celda($h, $r, 1, Carbon::parse($f->fecha)->format('d/m/Y'), X::S_TEXTO);
            $x->celda($h, $r, 2, $f->numero_factura, X::S_TEXTO);
            $x->celda($h, $r, 3, $f->proveedor ?: '—', X::S_TEXTO);
            $x->celda($h, $r, 4, (float) $f->total, X::S_DINERO);
            $r++;
        }
        if ($filas->count() > 0) {
            $ult = $r - 1;
            $x->celda($h, $r, 1, 'TOTAL', X::S_TOTAL_ETIQUETA);
            $x->celda($h, $r, 2, '', X::S_TOTAL_ETIQUETA);
            $x->celda($h, $r, 3, '', X::S_TOTAL_ETIQUETA);
            $x->formula($h, $r, 4, "SUM(D6:D{$ult})", $total, X::S_TOTAL_DINERO);
        }
        $x->congelar($h, 6);
    }

    private function hojaRentabilidad(X $x, string $hoja, string $titulo, Carbon $desde, Carbon $hasta, $filas, bool $conUnidades): void
    {
        $h = $x->hoja($hoja);
        $this->encabezado($x, $h, $titulo, $desde, $hasta);
        $x->ancho($h, 1, 38);
        $cols = $conUnidades ? ['Producto', 'Unidades', 'Venta neta', 'Costo', 'Utilidad', 'Margen'] : ['Categoría', 'Venta neta', 'Costo', 'Utilidad', 'Margen'];
        $x->fila($h, 5, $cols, X::S_ENCABEZADO);
        $off = $conUnidades ? 1 : 0;
        for ($c = 2; $c <= count($cols); $c++) { $x->ancho($h, $c, 17); }

        $r = 6;
        foreach ($filas as $f) {
            $x->celda($h, $r, 1, $f->nombre, X::S_TEXTO);
            if ($conUnidades) { $x->celda($h, $r, 2, (int) $f->unidades, X::S_ENTERO); }
            $x->celda($h, $r, 2 + $off, (float) $f->venta, X::S_DINERO);
            $x->celda($h, $r, 3 + $off, (float) $f->costo, X::S_DINERO);
            $cv = X::col(2 + $off); $cc = X::col(3 + $off);
            $x->formula($h, $r, 4 + $off, "{$cv}{$r}-{$cc}{$r}", $f->utilidad, X::S_DINERO);
            $cu = X::col(4 + $off);
            $x->formula($h, $r, 5 + $off, "IF({$cv}{$r}>0,{$cu}{$r}/{$cv}{$r},0)", $f->margen, X::S_PORCENTAJE);
            $r++;
        }
        $x->congelar($h, 6);
    }

    private function descargar(X $x, string $base, Carbon $desde, Carbon $hasta)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'rep');
        $x->guardar($tmp);
        $nombre = $base . '_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.xlsx';

        return response()->download($tmp, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
