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

            if($caja){

                $ventasHoy = DB::table('ventas')
                    ->whereDate('fecha_venta', today())
                    ->where('estado','COMPLETADA')
                    ->sum('total');

                $ingresos = DB::table('movimientos_caja')
                    ->where('id_caja',$caja->id_caja)
                    ->where('tipo_movimiento','INGRESO')
                    ->sum('monto');

                $egresos = DB::table('movimientos_caja')
                    ->where('id_caja',$caja->id_caja)
                    ->where('tipo_movimiento','EGRESO')
                    ->sum('monto');
            }

            return view('caja.index', compact(
                'caja',
                'abierta',
                'ventasHoy',
                'ingresos',
                'egresos'
            ));
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

            ->get();

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