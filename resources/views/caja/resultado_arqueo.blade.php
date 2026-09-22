@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-warning text-dark">

            Resultado del Arqueo de Caja

        </div>

        <div class="card-body">

            <table class="table table-bordered">

               <tr>
                    <th>Saldo Inicial</th>
                    <td>C$ {{ number_format($caja->saldo_inicial,2) }}</td>
                </tr>

                <tr>
                    <th>Ventas del día</th>
                    <td>C$ {{ number_format($ventas,2) }}</td>
                </tr>

                <tr>
                    <th>Ingresos</th>
                    <td>C$ {{ number_format($ingresos,2) }}</td>
                </tr>

                <tr>
                    <th>Egresos</th>
                    <td>C$ {{ number_format($egresos,2) }}</td>
                </tr>

                <tr class="table-success">
                    <th>Saldo Esperado</th>
                    <td>
                        <strong>
                            C$ {{ number_format($saldoEsperado,2) }}
                        </strong>
                    </td>
                </tr>

                <tr>
                    <th>Efectivo contado</th>
                    <td>C$ {{ number_format($contado,2) }}</td>
                </tr>

                <tr>
                    <th>Diferencia</th>

                    <td>

                        @if($diferencia == 0)

                            <span class="badge bg-success fs-6">
                                Caja Cuadrada
                            </span>

                        @elseif($diferencia > 0)

                            <span class="badge bg-primary fs-6">
                                Sobrante: C$ {{ number_format($diferencia,2) }}
                            </span>

                        @else

                            <span class="badge bg-danger fs-6">
                                Faltante: C$ {{ number_format(abs($diferencia),2) }}
                            </span>

                        @endif

                    </td>

                </tr>

            </table>

            <div class="mt-4 d-flex justify-content-between">

                <a href="{{ route('caja.index') }}"
                   class="btn btn-secondary">

                    Volver

                </a>

                <form action="{{ route('caja.cerrar') }}"
                      method="POST">

                    @csrf

                    <input type="hidden"
                           name="saldo_contado"
                           value="{{ $contado }}">

                    <button class="btn btn-danger">

                        🔒 Cerrar Caja

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection