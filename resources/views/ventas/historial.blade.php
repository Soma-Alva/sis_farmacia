@extends('layouts.app')

@section('content')

<h4 class="gf-page-title mb-3">Historial de Ventas</h4>

<div class="gf-filtros">
    <form method="GET" action="{{ route('ventas.historial') }}" class="row g-2">
        <div class="col-md-3">
            <input type="text" name="ticket" class="form-control" placeholder="Buscar Ticket" value="{{ request('ticket') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="cliente" class="form-control" placeholder="Buscar Cliente" value="{{ request('cliente') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
        </div>
        <div class="col-md-2">
            <button class="gf-btn-soft gf-btn-neutro w-100" style="padding:9px 0;">Buscar</button>
        </div>
        <div class="col-md-1">
            <a href="{{ route('ventas.historial') }}" class="gf-btn-soft gf-btn-apagado d-block text-center" style="padding:9px 0;">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<p class="gf-page-subtitle mb-3">Total de ventas encontradas: <strong>{{ $ventas->count() }}</strong></p>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
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
                            <td class="fw-semibold">{{ $venta->numero_ticket }}</td>
                            <td>{{ $venta->fecha_venta }}</td>
                            <td>{{ $venta->cliente_nombre }}</td>
                            <td>C$ {{ number_format($venta->total,2) }}</td>
                            <td>
                                <span class="gf-badge {{ $venta->estado == 'COMPLETADA' ? 'gf-badge-exito' : 'gf-badge-peligro' }}">
                                    {{ $venta->estado }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('ventas.show',$venta->id_venta) }}" class="gf-btn-soft gf-btn-neutro">
                                    Ver
                                </a>

                                @if($venta->estado=="COMPLETADA")
                                <form action="{{ route('ventas.anular',$venta->id_venta) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button class="gf-btn-soft gf-btn-peligro" onclick="return confirm('¿Desea anular esta venta?')">
                                        Anular
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No existen ventas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<button class="gf-btn-soft gf-btn-apagado mt-3" onclick="window.history.back();">Volver</button>
@endsection
