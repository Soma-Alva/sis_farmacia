<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{

    public function index()
    {
            /* Buscar primero una caja ABIERTA; si no existe, mostrar la última caja registrada */
            $caja = DB::table('caja')
                ->where('estado', 'ABIERTA')
                ->first();

            if (!$caja) {
                $caja = DB::table('caja')
                    ->orderByDesc('id_caja')
                    ->first();
            }

            $abierta = $caja && $caja->estado === 'ABIERTA';

            $ventasHoy = 0;
            $ingresos = 0;
            $egresos = 0;
            $egresosCount = 0;
            $efectivoTeorico = 0;
            $cajero = null;
            $movimientosHoy = collect();
            $ventasPorMetodo = collect();
            $fechasTurno = collect();
            $bancoTarjeta = 0;
            $bancoTransferencia = 0;
            $bancoCount = 0;
            $ultimaConciliacion = null;

            if($caja){

                // Importante: todo lo que sigue se calcula POR TURNO (desde
                // que se abrió esta caja, sin importar cuántos días
                // lleve abierta), no "por hoy". Antes esto filtraba por
                // whereDate(..., today()), así que si la caja seguía
                // abierta de un día para otro, un movimiento de ayer
                // (ej. un egreso) desaparecía de los totales y de la
                // bitácora aunque siguiera siendo del mismo turno sin
                // cerrar. El filtro por día (ver $fechasTurno más abajo)
                // ahora es solo un filtro visual sobre la bitácora, no
                // afecta los totales de las tarjetas.
                $ventasHoy = DB::table('movimientos_caja')
                    ->where('id_caja', $caja->id_caja)
                    ->where('tipo_movimiento', 'INGRESO')
                    ->whereNotNull('id_venta')
                    ->sum('monto');

                // Desglose de las ventas del turno por método de pago,
                // para la tarjeta "Total ingresos" del dashboard. Queda
                // como ['EFECTIVO' => 25.0, 'TARJETA' => 96.0, ...] — la
                // vista solo muestra los métodos que de verdad tuvieron
                // alguna venta, nunca los que están en cero.
                $ventasPorMetodo = DB::table('movimientos_caja')
                    ->where('id_caja', $caja->id_caja)
                    ->where('tipo_movimiento', 'INGRESO')
                    ->whereNotNull('id_venta')
                    ->groupBy('forma_pago')
                    ->selectRaw('forma_pago, SUM(monto) as total')
                    ->pluck('total', 'forma_pago');

                // Solo ingresos manuales: los que ya vienen de una venta
                // se cuentan en $ventasHoy, para no sumarlos dos veces en
                // el dashboard (el arqueo/cierre ya filtraba así, el
                // dashboard no lo hacía y mezclaba ambos montos).
                $ingresos = DB::table('movimientos_caja')
                    ->where('id_caja',$caja->id_caja)
                    ->where('tipo_movimiento','INGRESO')
                    ->whereNull('id_venta')
                    ->sum('monto');

                $egresosQuery = DB::table('movimientos_caja')
                    ->where('id_caja',$caja->id_caja)
                    ->where('tipo_movimiento','EGRESO');

                $egresos = (clone $egresosQuery)->sum('monto');
                $egresosCount = (clone $egresosQuery)->count();

                // Efectivo teórico: a diferencia de caja.saldo_final (que
                // mezcla ventas en efectivo, tarjeta, transferencia y
                // dólares), esto solo suma/resta los movimientos cuya
                // forma_pago es EFECTIVO, que es lo único que debería
                // estar físicamente en la gaveta y lo que se compara en
                // el arqueo/cierre.
                $efectivoTeorico = $this->resumenEfectivo($caja)['saldoEsperado'];

                // Pagos que van al banco (tarjeta + transferencia), netos
                // de egresos por el mismo medio (p. ej. devoluciones).
                $banco = $this->totalesBanco($caja->id_caja);
                $bancoTarjeta = $banco['TARJETA'];
                $bancoTransferencia = $banco['TRANSFERENCIA'];
                $bancoCount = $banco['count'];
                $ultimaConciliacion = DB::table('conciliaciones_bancarias')
                    ->where('id_caja', $caja->id_caja)
                    ->orderByDesc('id_conciliacion')
                    ->first();

                // Quién abrió este turno, para mostrarlo en el encabezado
                $cajero = optional(
                    DB::table('usuarios')
                        ->where('id_usuario', $caja->id_usuario_apertura)
                        ->first()
                )->nombre_completo;

                // Bitácora completa del turno (no solo "hoy"), para
                // mostrarla directo en el dashboard de caja en vez de
                // mandar a otra pantalla. El filtro de fecha de la vista
                // deja elegir un día en particular dentro de este turno
                // sin tener que volver a consultar el servidor.
                $movimientosHoy = DB::table('movimientos_caja')
                    ->leftJoin('usuarios', 'movimientos_caja.id_usuario', '=', 'usuarios.id_usuario')
                    ->leftJoin('ventas', 'movimientos_caja.id_venta', '=', 'ventas.id_venta')
                    ->where('movimientos_caja.id_caja', $caja->id_caja)
                    ->select(
                        'movimientos_caja.*',
                        'usuarios.nombre_completo',
                        'ventas.numero_ticket'
                    )
                    ->orderBy('movimientos_caja.creado_en')
                    ->get();

                // Fechas distintas presentes en este turno (incluida la
                // de apertura), para armar el filtro "ver un día en
                // particular" en la bitácora, de más reciente a más
                // antigua.
                $fechasTurno = collect([$caja->fecha_apertura])
                    ->merge($movimientosHoy->pluck('creado_en')->map(
                        fn($fecha) => \Carbon\Carbon::parse($fecha)->toDateString()
                    ))
                    ->unique()
                    ->sortDesc()
                    ->values();
            }

            return view('caja.index', compact(
                'caja',
                'abierta',
                'ventasHoy',
                'ingresos',
                'egresos',
                'egresosCount',
                'efectivoTeorico',
                'bancoTarjeta',
                'bancoTransferencia',
                'bancoCount',
                'ultimaConciliacion',
                'cajero',
                'movimientosHoy',
                'ventasPorMetodo',
                'fechasTurno'
            ));
    }

    /**
     * Permite corregir la tasa de cambio del día sin tener que cerrar y
     * volver a abrir la caja: el ícono de lápiz del encabezado manda
     * aquí. Solo cambia caja.tipo_cambio, no recalcula ventas ya
     * registradas con la tasa anterior.
     */
    public function actualizarTipoCambio(Request $request)
    {
        $request->validate([
            'tipo_cambio' => 'required|numeric|min:0.0001',
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'No existe una caja abierta.');
        }

        $caja->update(['tipo_cambio' => $request->tipo_cambio]);

        return redirect()
            ->route('caja.index')
            ->with('success', 'Tasa de cambio actualizada correctamente.');
    }

    public function abrir(Request $request)
    {

        Caja::create([

            'fecha_apertura' => now()->toDateString(),

            'hora_apertura' => now()->format('H:i:s'),

            'id_usuario_apertura' => auth()->user()->id_usuario,

            'saldo_inicial' => $request->saldo_inicial,

            'saldo_final' => $request->saldo_inicial,

            'tipo_cambio' => $request->tipo_cambio,

            'aplica_iva' => $request->boolean('aplica_iva'),

            'iva_porcentaje' => $request->iva_porcentaje ?? 15,

            'total_ingresos' => 0,

            'total_egresos' => 0,

            'diferencia' => 0,

            'estado' => 'ABIERTA',

            'observaciones' => $request->observaciones

        ]);

        return redirect()
                ->route('caja.index')
                ->with('success','Caja abierta correctamente');

    }

    public function ingreso()
    {
        return view('caja.ingreso');
    }


   public function guardarIngreso(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'motivo' => 'required|max:255',
            'descripcion' => 'nullable|max:500'
        ]);

        $caja = DB::table('caja')
            ->where('estado', 'ABIERTA')
            ->first();

        if (!$caja) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'No existe una caja abierta.');
        }

        $saldoAnterior = $caja->saldo_final;
        $saldoActual = $saldoAnterior + $request->monto;

        DB::table('movimientos_caja')->insert([

            'id_caja' => $caja->id_caja,

            'id_venta' => null,

            'id_compra' => null,

            'tipo_movimiento' => 'INGRESO',

            'forma_pago' => 'EFECTIVO',

            'monto' => $request->monto,

            'descripcion' => $request->descripcion,

            'motivo' => $request->motivo,

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

        return redirect()
            ->route('caja.index')
            ->with('success','Ingreso registrado correctamente.');
    }

    public function egreso()
    {
        return view('caja.egreso');
    }

    public function guardarEgreso(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'motivo' => 'required'
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return back()->with('error', 'No existe una caja abierta.');
        }

        if ($request->monto > $caja->saldo_final) {
            return back()->with('error', 'El monto supera el saldo disponible.');
        }

        $saldoAnterior = $caja->saldo_final;
        $saldoActual = $saldoAnterior - $request->monto;

        DB::table('movimientos_caja')->insert([

        'id_caja'=>$caja->id_caja,

        'tipo_movimiento'=>'EGRESO',

        'forma_pago'=>'EFECTIVO',

        'monto'=>$request->monto,

        'descripcion'=>$request->descripcion,

        'motivo'=>$request->motivo,

        'saldo_anterior'=>$saldoAnterior,

        'saldo_actual'=>$saldoActual,

        'id_usuario'=>auth()->user()->id_usuario,

        'creado_en'=>now()

       ]);
        $caja->update([

            'saldo_final' => $saldoActual

        ]);

        return redirect()
                ->route('caja.index')
                ->with('success','Egreso registrado correctamente.');
    }

    public function movimientos()
    {
        $movimientos = DB::table('movimientos_caja')

            ->leftJoin(
                'usuarios',
                'movimientos_caja.id_usuario',
                '=',
                'usuarios.id_usuario'
            )

            ->leftJoin(
                'ventas',
                'movimientos_caja.id_venta',
                '=',
                'ventas.id_venta'
            )

            ->select(

                'movimientos_caja.*',

                'usuarios.nombre_completo',

                'ventas.numero_ticket',

                'ventas.cliente_nombre',

                'ventas.metodo_pago',

                'ventas.estado as estado_venta'

            )

            ->orderByDesc('movimientos_caja.creado_en')

            ->paginate(15)
            ->withQueryString();

        return view('caja.movimientos', compact('movimientos'));
    }

    /**
     * Totales del turno de los pagos que van al banco. Ingresos menos
     * egresos por cada medio, y cantidad de movimientos de ingreso.
     */
    private function totalesBanco($idCaja): array
    {
        $res = ['TARJETA' => 0.0, 'TRANSFERENCIA' => 0.0, 'count' => 0];

        $filas = DB::table('movimientos_caja')
            ->where('id_caja', $idCaja)
            ->whereIn('forma_pago', ['TARJETA', 'TRANSFERENCIA'])
            ->get(['tipo_movimiento', 'forma_pago', 'monto']);

        foreach ($filas as $f) {
            $signo = $f->tipo_movimiento === 'INGRESO' ? 1 : -1;
            $res[$f->forma_pago] += $signo * (float) $f->monto;
            if ($f->tipo_movimiento === 'INGRESO') {
                $res['count']++;
            }
        }

        return $res;
    }

    /**
     * Conciliación bancaria: arqueo de los pagos que NO pasan por la
     * gaveta. Se compara lo registrado por el sistema contra lo que
     * confirma el banco / voucher del POS.
     */
    public function conciliacion()
    {
        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return redirect()->route('caja.index')->with('error', 'No existe una caja abierta.');
        }

        $totales = $this->totalesBanco($caja->id_caja);

        $pagos = DB::table('movimientos_caja')
            ->leftJoin('ventas', 'movimientos_caja.id_venta', '=', 'ventas.id_venta')
            ->where('movimientos_caja.id_caja', $caja->id_caja)
            ->whereIn('movimientos_caja.forma_pago', ['TARJETA', 'TRANSFERENCIA'])
            ->select('movimientos_caja.*', 'ventas.numero_ticket')
            ->orderBy('movimientos_caja.creado_en')
            ->get();

        $historial = DB::table('conciliaciones_bancarias')
            ->leftJoin('usuarios', 'conciliaciones_bancarias.id_usuario', '=', 'usuarios.id_usuario')
            ->where('id_caja', $caja->id_caja)
            ->orderByDesc('id_conciliacion')
            ->select('conciliaciones_bancarias.*', 'usuarios.nombre_completo')
            ->get();

        return view('caja.conciliacion', compact('caja', 'totales', 'pagos', 'historial'));
    }

    public function guardarConciliacion(Request $request)
    {
        $request->validate([
            'confirmado_tarjeta' => 'required|numeric|min:0',
            'confirmado_transferencia' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return redirect()->route('caja.index')->with('error', 'No existe una caja abierta.');
        }

        $t = $this->totalesBanco($caja->id_caja);
        $dif = ($request->confirmado_tarjeta - $t['TARJETA'])
             + ($request->confirmado_transferencia - $t['TRANSFERENCIA']);

        DB::table('conciliaciones_bancarias')->insert([
            'id_caja' => $caja->id_caja,
            'id_usuario' => auth()->id(),
            'esperado_tarjeta' => $t['TARJETA'],
            'confirmado_tarjeta' => $request->confirmado_tarjeta,
            'esperado_transferencia' => $t['TRANSFERENCIA'],
            'confirmado_transferencia' => $request->confirmado_transferencia,
            'diferencia_total' => $dif,
            'observaciones' => $request->observaciones,
            'creado_en' => now(),
        ]);

        return redirect()->route('caja.conciliacion')->with(
            abs($dif) < 0.005 ? 'success' : 'error',
            abs($dif) < 0.005
                ? 'Conciliación bancaria cuadrada.'
                : 'Conciliación guardada con diferencia de C$ ' . number_format($dif, 2) . '.'
        );
    }

    /**
     * Efectivo físico del turno, expresado en C$. La gaveta guarda
     * córdobas Y dólares: una venta pagada en dólares es dinero que
     * queda físicamente en la gaveta, así que cuenta igual que una en
     * efectivo (su monto en movimientos_caja ya está en C$, al tipo de
     * cambio de la venta, neto del vuelto). Tarjeta y transferencia no
     * pasan por la gaveta.
     */
    private function resumenEfectivo($caja): array
    {
        $fisico = ['EFECTIVO', 'DOLARES'];

        $base = fn() => DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->whereIn('forma_pago', $fisico);

        $ventas = $base()->where('tipo_movimiento', 'INGRESO')->whereNotNull('id_venta')->where('forma_pago', 'EFECTIVO')->sum('monto');
        $ventasDolares = $base()->where('tipo_movimiento', 'INGRESO')->whereNotNull('id_venta')->where('forma_pago', 'DOLARES')->sum('monto');
        $ingresos = $base()->where('tipo_movimiento', 'INGRESO')->whereNull('id_venta')->sum('monto');
        $egresos = $base()->where('tipo_movimiento', 'EGRESO')->sum('monto');

        return [
            'ventas' => $ventas,
            'ventasDolares' => $ventasDolares,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldoEsperado' => $caja->saldo_inicial + $ventas + $ventasDolares + $ingresos - $egresos,
        ];
    }

    /**
     * Estado de la conciliación bancaria para poder cerrar: si hubo
     * pagos al banco, debe existir una conciliación guardada, cuadrada
     * y hecha sobre los totales actuales (si entró otro pago después,
     * hay que volver a conciliar).
     */
    private function estadoConciliacion($caja): array
    {
        $t = $this->totalesBanco($caja->id_caja);

        if ($t['count'] == 0 && abs($t['TARJETA']) < 0.005 && abs($t['TRANSFERENCIA']) < 0.005) {
            return ['ok' => true, 'motivo' => null, 'requerida' => false];
        }

        $c = DB::table('conciliaciones_bancarias')
            ->where('id_caja', $caja->id_caja)
            ->orderByDesc('id_conciliacion')
            ->first();

        if (!$c) {
            return ['ok' => false, 'motivo' => 'Falta hacer la conciliación bancaria.', 'requerida' => true];
        }
        if (abs($c->esperado_tarjeta - $t['TARJETA']) > 0.005 || abs($c->esperado_transferencia - $t['TRANSFERENCIA']) > 0.005) {
            return ['ok' => false, 'motivo' => 'Hubo pagos al banco después de la última conciliación; vuelve a conciliar.', 'requerida' => true];
        }
        if (abs($c->diferencia_total) >= 0.005) {
            return ['ok' => false, 'motivo' => 'La conciliación bancaria tiene diferencia; corrígela hasta que cuadre.', 'requerida' => true];
        }

        return ['ok' => true, 'motivo' => null, 'requerida' => true];
    }

    /** Efectivo contado en C$ = córdobas + dólares * tipo de cambio. */
    private function contadoTotal(Request $request, $caja): float
    {
        return round((float) $request->saldo_contado + (float) $request->dolares_contados * (float) $caja->tipo_cambio, 2);
    }

    public function arqueo()
    {
        $caja = Caja::where('estado','ABIERTA')->first();

        if(!$caja){
            return redirect()->route('caja.index')->with('error','No existe una caja abierta.');
        }

        return view('caja.arqueo', array_merge(['caja' => $caja], $this->resumenEfectivo($caja)));
    }

    public function guardarArqueo(Request $request)
    {
        $request->validate([
            'saldo_contado' => 'required|numeric|min:0',
            'dolares_contados' => 'nullable|numeric|min:0',
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return redirect()->route('caja.index')->with('error', 'No existe una caja abierta.');
        }

        $r = $this->resumenEfectivo($caja);
        $contado = $this->contadoTotal($request, $caja);
        $diferencia = $contado - $r['saldoEsperado'];

        return view('caja.resultado_arqueo', array_merge($r, [
            'caja' => $caja,
            'contado' => $contado,
            'contadoCordobas' => (float) $request->saldo_contado,
            'dolaresContados' => (float) $request->dolares_contados,
            'diferencia' => $diferencia,
            'conciliacion' => $this->estadoConciliacion($caja),
        ]));
    }

    public function cerrarCaja(Request $request)
    {
        $request->validate([
            'saldo_contado' => 'required|numeric|min:0',
            'dolares_contados' => 'nullable|numeric|min:0',
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return redirect()->route('caja.index')->with('error','No existe una caja abierta.');
        }

        $r = $this->resumenEfectivo($caja);
        $contado = $this->contadoTotal($request, $caja);
        $diferencia = $contado - $r['saldoEsperado'];

        // Para cerrar deben cuadrar AMBOS arqueos: efectivo físico
        // (córdobas + dólares) y conciliación bancaria.
        if (abs($diferencia) >= 0.01) {
            return redirect()->route('caja.arqueo')->with('error',
                'No se puede cerrar: el arqueo de efectivo no cuadra (diferencia C$ ' . number_format($diferencia, 2) . ').');
        }

        $conc = $this->estadoConciliacion($caja);
        if (!$conc['ok']) {
            return redirect()->route('caja.conciliacion')->with('error', 'No se puede cerrar: ' . $conc['motivo']);
        }

        $caja->update([
            'fecha_cierre' => now()->toDateString(),
            'hora_cierre' => now()->format('H:i:s'),
            'id_usuario_cierre' => auth()->user()->id_usuario,
            'saldo_final' => $contado,
            'saldo_contado' => $contado,
            'total_ingresos' => $r['ventas'] + $r['ventasDolares'] + $r['ingresos'],
            'total_egresos' => $r['egresos'],
            'diferencia' => $diferencia,
            'estado' => 'CERRADA'
        ]);

        return redirect()->route('caja.index')->with('success','Caja cerrada correctamente.');
    }

}
