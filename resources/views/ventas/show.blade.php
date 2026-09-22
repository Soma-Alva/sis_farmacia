@extends('layouts.app')

@section('content')

<div class="container">

<h3>Detalle de Venta</h3>

<div class="card mb-4">

    <div class="card-body">

        <p><strong>Ticket:</strong> {{ $venta->numero_ticket }}</p>

        <p><strong>Fecha:</strong> {{ $venta->fecha_venta }}</p>

        <p><strong>Cliente:</strong> {{ $venta->cliente_nombre }}</p>

        <p><strong>DNI:</strong> {{ $venta->cliente_dni }}</p>

        <p><strong>Método Pago:</strong> {{ $venta->metodo_pago }}</p>

        <p><strong>Total:</strong>

            C$ {{ number_format($venta->total,2) }}

        </p>

    </div>

</div>

<table class="table table-bordered">

<thead class="table-dark">

<tr>

<th>Producto</th>

<th>Cantidad</th>

<th>Precio</th>

<th>Subtotal</th>

</tr>

</thead>

<tbody>

@foreach($detalle as $item)

<tr>

<td>{{ $item->nombre }}</td>

<td>{{ $item->cantidad }}</td>

<td>C$ {{ number_format($item->precio_unitario,2) }}</td>

<td>C$ {{ number_format($item->subtotal,2) }}</td>

</tr>

@endforeach

</tbody>

</table>

<a href="{{ route('ventas.historial') }}"
class="btn btn-secondary">

Volver

</a>

</div>

@endsection