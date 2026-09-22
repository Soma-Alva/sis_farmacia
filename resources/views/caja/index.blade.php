@extends('layouts.app')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            💰 Caja
        </h2>

        <span class="text-muted">
            {{ now()->format('d/m/Y H:i') }}
        </span>

    </div>

    @if($caja)

    <div class="row">

        <div class="col-md-3 mb-3">

            <div class="card shadow border-success">

                <div class="card-body text-center">

                    <h6>Estado</h6>

                    @if($abierta)
                        <span class="badge bg-success fs-6">ABIERTA</span>
                    @else
                        <span class="badge bg-danger fs-6">CERRADA</span>
                    @endif

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-3">

            <div class="card shadow">

                <div class="card-body text-center">

                    <h6>Saldo Inicial</h6>

                    <h4>

                        C$ {{ number_format($caja->saldo_inicial,2) }}

                    </h4>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-3">

            <div class="card shadow">

                <div class="card-body text-center">

                    <h6>Saldo Actual</h6>

                    <h4>

                        C$ {{ number_format($caja->saldo_final,2) }}

                    </h4>

                </div>

            </div>

        </div>

        <div class="col-md-3 mb-3">

            <div class="card shadow">

                <div class="card-body text-center">

                    <h6>Tipo de Cambio</h6>

                    <h4>

                        {{ number_format($caja->tipo_cambio,2) }}

                    </h4>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow mt-4">

        <div class="card-header bg-primary text-white">

            Resumen del Día

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 text-center">

                    <h6>Ventas</h6>

                    <h3 class="text-success">

                        C$ {{ number_format($ventasHoy ?? 0,2) }}

                    </h3>

                </div>

                <div class="col-md-4 text-center">

                    <h6>Ingresos</h6>

                    <h3 class="text-primary">

                        C$ {{ number_format($ingresos ?? 0,2) }}

                    </h3>

                </div>

                <div class="col-md-4 text-center">

                    <h6>Egresos</h6>

                    <h3 class="text-danger">

                        C$ {{ number_format($egresos ?? 0,2) }}

                    </h3>

                </div>

            </div>

        </div>

    </div>

    <div class="row mt-4">

        <div class="col-md-2">
            @if($abierta)
                <a href="{{ route('caja.ingreso') }}"
                class="btn btn-success w-100">
                    ➕<br>Nuevo Ingreso
                </a>
            @else
                <button class="btn btn-success w-100" disabled>
                    ➕<br>Nuevo Ingreso
                </button>
            @endif
        </div>

        <div class="col-md-2">
            @if($abierta)
                <a href="{{ route('caja.egreso') }}"
                class="btn btn-danger w-100">
                    ➖<br>Nuevo Egreso
                </a>
            @else
                <button class="btn btn-danger w-100" disabled>
                    ➖<br>Nuevo Egreso
                </button>
            @endif
        </div>

        <div class="col-md-2">

            <a href="{{ route('caja.movimientos') }}"
                class="btn btn-info w-100">

                📋<br>
                Movimientos

            </a>

        </div>

        <div class="col-md-2">
            @if($abierta)
                <a href="{{ route('caja.arqueo') }}"
                class="btn btn-warning w-100">
                    💵<br>Arqueo
                </a>
            @else
                <a href="{{ route('caja.index') }}#abrirCaja"
                class="btn btn-primary w-100">
                    🔓<br>Abrir Caja
                </a>
            @endif
        </div>

       

    </div>

        @if(!$abierta)
            <div class="card shadow mt-4" id="abrirCaja">
                <div class="card-header bg-warning">
                    Abrir Nueva Caja
                </div>

                <div class="card-body">
                    <form action="{{ route('caja.abrir') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Saldo Inicial</label>
                                <input type="number"
                                    step="0.01"
                                    min="0"
                                    name="saldo_inicial"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Tipo de Cambio</label>
                                <input type="number"
                                    step="0.0001"
                                    min="0"
                                    name="tipo_cambio"
                                    class="form-control"
                                    value="36.62"
                                    required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Observaciones</label>
                                <input type="text"
                                    name="observaciones"
                                    class="form-control">
                            </div>
                        </div>

                        <button class="btn btn-primary">
                            Abrir Caja
                        </button>
                    </form>
                </div>
            </div>
        @endif

    @else

    <div class="card shadow">

        <div class="card-header bg-warning">

            Apertura de Caja

        </div>

        <div class="card-body">

            <form action="{{ route('caja.abrir') }}" method="POST">

                @csrf

                <div class="mb-3">

                    <label>Saldo Inicial</label>

                    <input
                        type="number"
                        step="0.01"
                        name="saldo_inicial"
                        class="form-control"
                        required>

                </div>

                <div class="mb-3">

                    <label>Tipo de Cambio</label>

                    <input
                        type="number"
                        step="0.0001"
                        name="tipo_cambio"
                        class="form-control"
                        value="36.62"
                        required>

                </div>

                <div class="mb-3">

                    <label>Observaciones</label>

                    <textarea
                        name="observaciones"
                        class="form-control"
                        rows="3"></textarea>

                </div>

                <button class="btn btn-primary">

                    Abrir Caja

                </button>

            </form>

        </div>

    </div>

    @endif

</div>

@endsection