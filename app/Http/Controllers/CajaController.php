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

            if($caja){

                $ventasHoy = DB::table('ventas')
                    ->whereDate('fecha_venta', today())
                    ->where('estado','COMPLETADA')
                    ->sum('total');

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

                // Bitácora del día, para mostrarla directo en el
                // dashboard de caja en vez de mandar a otra pantalla
                $movimientosHoy = DB::table('movimientos_caja')
                    ->leftJoin('usuarios', 'movimientos_caja.id_usuario', '=', 'usuarios.id_usuario')
                    ->leftJoin('ventas', 'movimientos_caja.id_venta', '=', 'ventas.id_venta')
                    ->where('movimientos_caja.id_caja', $caja->id_caja)
                    ->whereDate('movimientos_caja.creado_en', today())
                    ->select(
                        'movimientos_caja.*',
                        'usuarios.nombre_completo',
                        'ventas.numero_ticket'
                    )
                    ->orderBy('movimientos_caja.creado_en')
                    ->get();
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
                'movimientosHoy'
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

            $ventas = DB::table('ventas')
                ->where('estado', 'COMPLETADA')
                ->whereDate('fecha_venta', today())
                ->sum('total');

            $ingresos = DB::table('movimientos_caja')
                ->where('id_caja', $caja->id_caja)
                ->where('tipo_movimiento', 'INGRESO')
                ->whereNull('id_venta') // solo ingresos manuales
                ->sum('monto');

            $egresos = DB::table('movimientos_caja')
                ->where('id_caja', $caja->id_caja)
                ->where('tipo_movimiento', 'EGRESO')
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

        // Ventas del día
        $ventas = DB::table('ventas')
            ->where('estado', 'COMPLETADA')
            ->whereDate('fecha_venta', today())
            ->sum('total');

        // Ingresos manuales
        $ingresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'INGRESO')
            ->whereNull('id_venta')
            ->sum('monto');

        // Egresos
        $egresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'EGRESO')
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

        // Total de ventas del día
        $ventas = DB::table('ventas')
            ->where('estado', 'COMPLETADA')
            ->whereDate('fecha_venta', today())
            ->sum('total');

        // Ingresos manuales
        $ingresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'INGRESO')
            ->whereNull('id_venta')
            ->sum('monto');

        // Egresos
        $egresos = DB::table('movimientos_caja')
            ->where('id_caja', $caja->id_caja)
            ->where('tipo_movimiento', 'EGRESO')
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