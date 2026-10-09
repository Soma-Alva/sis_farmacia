@extends('layouts.app')

@section('content')

<h4 class="gf-page-title mb-3">Detalle de Venta</h4>

<div class="gf-card mb-3">
    <div class="p-3 row">
        <div class="col-md-4 mb-2">
            <div class="gf-page-subtitle">Ticket</div>
            <div class="fw-semibold">{{ $venta->numero_ticket }}</div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="gf-page-subtitle">Fecha</div>
            <div class="fw-semibold">{{ $venta->fecha_venta }}</div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="gf-page-subtitle">Cliente</div>
            <div class="fw-semibold">{{ $venta->cliente_nombre ?? '—' }}</div>
        </div>
        <div class="col-md-4">
            <div class="gf-page-subtitle">DNI</div>
            <div class="fw-semibold">{{ $venta->cliente_dni ?? '—' }}</div>
        </div>
        <div class="col-md-4">
            <div class="gf-page-subtitle">Método de Pago</div>
            <div class="fw-semibold">{{ $venta->metodo_pago }}</div>
        </div>
        <div class="col-md-4">
            <div class="gf-page-subtitle">Total</div>
            <div class="fw-semibold">C$ {{ number_format($venta->total,2) }}</div>
        </div>
    </div>
</div>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalle as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->nombre }}</td>
                        <td>{{ $item->cantidad }}</td>
                        <td>C$ {{ number_format($item->precio_unitario,2) }}</td>
                        <td>C$ {{ number_format($item->subtotal,2) }}</td>
                        <td>{{ $item->numero_lote ?? '—' }}</td>
                        <td>{{ $item->fecha_vencimiento ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<a href="{{ route('ventas.historial') }}" class="gf-btn-soft gf-btn-apagado mt-3 d-inline-block">
    Volver
</a>

@endsection
