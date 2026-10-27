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
            $fechasTurno = collect();

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
                $efectivoIngresos = DB::table('movimientos_caja')
                    ->where('id_caja', $caja->id_caja)
                    ->where('tipo_movimiento', 'INGRESO')
                    ->where('forma_pago', 'EFECTIVO')
                    ->sum('monto');

                $efectivoEgresos = DB::table('movimientos_caja')
                    ->where('id_caja', $caja->id_caja)
                    ->where('tipo_movimiento', 'EGRESO')
                    ->where('forma_pago', 'EFECTIVO')
                    ->sum('monto');

                $efectivoTeorico = $caja->saldo_inicial + $efectivoIngresos - $efectivoEgresos;

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
                'cajero',
                'movimientosHoy',
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

    public function arqueo()
    {
        $caja = Caja::where('estado','ABIERTA')->first();

        if(!$caja){

            return redirect()
                ->route('caja.index')
                ->with('error','No existe una caja abierta.');

        }

            // Solo ventas pagadas en EFECTIVO: las pagadas con tarjeta,
            // transferencia o dólares nunca estuvieron físicamente en la
            // gaveta, así que no deben sumarse al efectivo esperado
            // (antes se sumaban todas, sin importar el medio de pago, y
            // el conteo físico nunca cuadraba).
            $ventas = DB::table('movimientos_caja')
                ->where('id_caja', $caja->id_caja)
                ->where('tipo_movimiento', 'INGRESO')
                ->where('forma_pago', 'EFECTIVO')
                ->whereNotNull('id_venta')
                ->sum('monto');

            $ingresos = DB::table('movimientos_caja')
                ->where('id_caja', $caja->id_caja)
                ->where('tipo_movimiento', 'INGRESO')
                ->whereNull('id_venta') // solo ingresos manuales
                ->where('forma_pago', 'EFECTIVO')
                ->sum('monto');

            $egresos = DB::table('movimientos_caja')
                ->where('id_caja', $caja->id_caja)
                ->where('tipo_movimiento', 'EGRESO')
                ->where('forma_pago', 'EFECTIVO')
                ->sum('monto');

            $saldoEsperado =
                $caja->saldo_inicial +
                $ventas +
                $ingresos -
                $egresos;

            return view('caja.arqueo', compact(
                'caja',
                'ventas',
                'ingresos',
                'egresos',
                'saldoEsperado'
            ));
    }

    public function guardarArqueo(Request $request)
    {
        $request->validate([
            'saldo_contado' => 'required|numeric|min:0'
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {
            return redirect()
                ->route('caja.index')
                ->with('error', 'No existe una caja abierta.');
        }

        // Ventas del día pagadas en EFECTIVO únicamente (ver nota en
        // arqueo()): las de tarjeta/transferencia/dólares no cuentan
        // para el efectivo físico esperado.
        $ventas = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'INGRESO')
            ->where('forma_pago', 'EFECTIVO')
            ->whereNotNull('id_venta')
            ->sum('monto');

        // Ingresos manuales
        $ingresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'INGRESO')
            ->whereNull('id_venta')
            ->where('forma_pago', 'EFECTIVO')
            ->sum('monto');

        // Egresos
        $egresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'EGRESO')
            ->where('forma_pago', 'EFECTIVO')
            ->sum('monto');

        // Saldo esperado
        $saldoEsperado =
            $caja->saldo_inicial +
            $ventas +
            $ingresos -
            $egresos;

        $diferencia = $request->saldo_contado - $saldoEsperado;

        return view('caja.resultado_arqueo', [
            'caja' => $caja,
            'contado' => $request->saldo_contado,
            'ventas' => $ventas,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldoEsperado' => $saldoEsperado,
            'diferencia' => $diferencia
        ]);
    }
    public function cerrarCaja(Request $request)
    {
        $request->validate([
            'saldo_contado' => 'required|numeric|min:0'
        ]);

        $caja = Caja::where('estado', 'ABIERTA')->first();

        if (!$caja) {

            return redirect()
                ->route('caja.index')
                ->with('error','No existe una caja abierta.');

        }

        // Total de ventas del día pagadas en EFECTIVO únicamente (ver
        // nota en arqueo()): las de tarjeta/transferencia/dólares no
        // cuentan para el efectivo físico esperado.
        $ventas = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'INGRESO')
            ->where('forma_pago', 'EFECTIVO')
            ->whereNotNull('id_venta')
            ->sum('monto');

        // Ingresos manuales
        $ingresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'INGRESO')
            ->whereNull('id_venta')
            ->where('forma_pago', 'EFECTIVO')
            ->sum('monto');

        // Egresos
        $egresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'EGRESO')
            ->where('forma_pago', 'EFECTIVO')
            ->sum('monto');

        // Saldo esperado
        $saldoEsperado =
            $caja->saldo_inicial +
            $ventas +
            $ingresos -
            $egresos;

        // Diferencia
        $diferencia = $request->saldo_contado - $saldoEsperado;

        $caja->update([

            'fecha_cierre' => now()->toDateString(),

            'hora_cierre' => now()->format('H:i:s'),

            'id_usuario_cierre' => auth()->user()->id_usuario,

            'saldo_final' => $request->saldo_contado,

            'saldo_contado' => $request->saldo_contado,

            'total_ingresos' => $ingresos,

            'total_egresos' => $egresos,

            'diferencia' => $diferencia,

            'estado' => 'CERRADA'

        ]);

        return redirect()
                ->route('caja.index')
                ->with('success','Caja cerrada correctamente.');

    }

}