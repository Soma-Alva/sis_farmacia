@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-warning">

            Arqueo de Caja

        </div>

        <div class="card-body">

            <form action="{{ route('caja.arqueo.guardar') }}"
                  method="POST">

                @csrf

                <div class="mb-3">
                    <label>Saldo Inicial</label>
                    <input class="form-control"
                        value="C$ {{ number_format($caja->saldo_inicial,2) }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label>Ventas del día</label>
                    <input class="form-control"
                        value="C$ {{ number_format($ventas,2) }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label>Ingresos</label>
                    <input class="form-control"
                        value="C$ {{ number_format($ingresos,2) }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label>Egresos</label>
                    <input class="form-control"
                        value="C$ {{ number_format($egresos,2) }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-success">
                        Saldo Esperado
                    </label>

                    <input
                        class="form-control fw-bold"
                        value="C$ {{ number_format($saldoEsperado,2) }}"
                        readonly>
                </div>

                <div class="mb-3">
                    <label>Efectivo contado</label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="saldo_contado"
                        class="form-control"
                        required>
                </div>
                <button class="btn btn-warning">

                    Calcular Diferencia

                </button>
                <button class="btn btn-secondary"
                    onclick="window.history.back()">

                    Regresar

                </button>

            </form>

        </div>

    </div>

</div>

@endsection