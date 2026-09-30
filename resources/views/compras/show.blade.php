@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Compra {{ $compra->numero_factura }}</h3>
        <a href="{{ route('compras.index') }}" class="btn btn-secondary btn-sm">Volver</a>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body row">
            <div class="col-md-3">
                <strong>Proveedor:</strong><br>
                {{ $compra->proveedor->nombre ?? 'N/A' }}
            </div>
            <div class="col-md-3">
                <strong>Fecha:</strong><br>
                {{ $compra->fecha_compra }}
            </div>
            <div class="col-md-3">
                <strong>Estado:</strong><br>
                <span class="badge {{ $compra->estado == 'APROBADA' ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $compra->estado }}
                </span>
            </div>
            <div class="col-md-3">
                <strong>Total:</strong><br>
                C$ {{ number_format($compra->total, 2) }}
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unit.</th>
                        <th>Subtotal</th>
                        <th>N° de Lote</th>
                        <th>Vencimiento</th>
                        <th>Lote creado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->detalles as $d)
                        <tr>
                            <td>{{ $d->producto->nombre ?? 'Producto eliminado' }}</td>
                            <td>{{ $d->cantidad }}</td>
                            <td>C$ {{ number_format($d->precio_unitario, 2) }}</td>
                            <td>C$ {{ number_format($d->subtotal, 2) }}</td>
                            <td>{{ $d->numero_lote ?? '—' }}</td>
                            <td>{{ $d->fecha_vencimiento ?? '—' }}</td>
                            <td>
                                @if($d->id_lote)
                                    <span class="badge bg-success">#{{ $d->id_lote }}</span>
                                @else
                                    <span class="badge bg-secondary">Pendiente de aprobar</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($compra->estado == 'PENDIENTE')
                <form action="{{ route('compras.aprobar', $compra->id_compra) }}" method="POST"
                      onsubmit="return confirm('¿Aprobar esta compra? Esto creará los lotes y sumará el stock.');">
                    @csrf
                    <button type="submit" class="btn btn-success">Aprobar compra</button>
                </form>
            @endif
        </div>
    </div>

</div>

@endsection
