@extends('layouts.app')

@section('content')

<div class="container">

    <h3 class="mb-4">Historial de Ventas</h3>

        <form method="GET" action="{{ route('ventas.historial') }}" class="row g-3 mb-4">

            <div class="col-md-3">
                <input
                    type="text"
                    name="ticket"
                    class="form-control"
                    placeholder="Buscar Ticket"
                    value="{{ request('ticket') }}">
            </div>

            <div class="col-md-3">
                <input
                    type="text"
                    name="cliente"
                    class="form-control"
                    placeholder="Buscar Cliente"
                    value="{{ request('cliente') }}">
            </div>

            <div class="col-md-3">
                <input
                    type="date"
                    name="fecha"
                    class="form-control"
                    value="{{ request('fecha') }}">
            </div>

            <div class="col-md-3 d-grid">
                <button class="btn btn-primary">
                    Buscar
                </button>
            </div>
            <div class="col-md-3 d-grid">
                <a href="{{ route('ventas.historial') }}"
                class="btn btn-secondary">
                    Limpiar filtros
                </a>
            </div>

        </form>

        <div class="alert alert-info">

            Total de ventas encontradas:

            <strong>{{ $ventas->count() }}</strong>

        </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-hover">

        <thead class="table-dark">

            <tr>
                <th>Ticket</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th width="180">Acciones</th>
            </tr>

        </thead>

        <tbody>

        @forelse($ventas as $venta)

            <tr>

                <td>{{ $venta->numero_ticket }}</td>

                <td>{{ $venta->fecha_venta }}</td>

                <td>{{ $venta->cliente_nombre }}</td>

                <td>C$ {{ number_format($venta->total,2) }}</td>

                <td>

                    @if($venta->estado=="COMPLETADA")

                        <span class="badge bg-success">
                            COMPLETADA
                        </span>

                    @else

                        <span class="badge bg-danger">
                            ANULADA
                        </span>

                    @endif

                </td>

                <td>

                    <a href="{{ route('ventas.show',$venta->id_venta) }}"
                       class="btn btn-info btn-sm">

                        Ver

                    </a>

                    @if($venta->estado=="COMPLETADA")

                    <form
                        action="{{ route('ventas.anular',$venta->id_venta) }}"
                        method="POST"
                        style="display:inline;">

                        @csrf
                        @method('PUT')

                        <button
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Desea anular esta venta?')">

                            Anular

                        </button>

                    </form>

                    @endif

                </td>

            </tr>

        @empty

            <tr>

                <td colspan="6" class="text-center">

                    No existen ventas.

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>
    <button class="btn btn-secondary mt-3" onclick="window.history.back();">Volver</button>
@endsection