@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Lote #{{ $lote->id_lote }} — {{ $lote->producto->nombre ?? 'Producto eliminado' }}</h3>
        <a href="{{ route('lotes.index') }}" class="btn btn-secondary btn-sm">Volver</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body row">
                    <div class="col-md-4">
                        <strong>N° de Lote:</strong><br>
                        {{ $lote->numero_lote ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Vencimiento:</strong><br>
                        {{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'Sin registrar' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Estado:</strong><br>
                        <span class="badge
                            {{ $lote->estado == 'ACTIVO' ? 'bg-success' :
                               ($lote->estado == 'AGOTADO' ? 'bg-secondary' :
                               ($lote->estado == 'VENCIDO' ? 'bg-danger' : 'bg-dark')) }}">
                            {{ $lote->estado }}
                        </span>
                    </div>

                    <div class="col-md-4 mt-3">
                        <strong>Cantidad inicial:</strong><br>
                        {{ $lote->cantidad_inicial }}
                    </div>
                    <div class="col-md-4 mt-3">
                        <strong>Cantidad actual:</strong><br>
                        {{ $lote->cantidad_actual }}
                    </div>
                    <div class="col-md-4 mt-3">
                        <strong>Costo unitario:</strong><br>
                        C$ {{ number_format($lote->costo_unitario, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <strong>Origen</strong><br>
                    @if($lote->compra)
                        Compra:
                        <a href="{{ route('compras.show', $lote->compra->id_compra) }}">
                            {{ $lote->compra->numero_factura }}
                        </a><br>
                        Proveedor: {{ $lote->compra->proveedor->nombre ?? '—' }}
                    @else
                        <span class="text-muted">Lote inicial (stock migrado, sin compra registrada)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header">Ventas donde se usó este lote</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventasDelLote as $v)
                        <tr>
                            <td>{{ $v->numero_ticket }}</td>
                            <td>{{ \Carbon\Carbon::parse($v->fecha_venta)->format('d/m/Y H:i') }}</td>
                            <td>{{ $v->cantidad }}</td>
                            <td>C$ {{ number_format($v->subtotal, 2) }}</td>
                            <td>
                                <span class="badge {{ $v->estado == 'ANULADA' ? 'bg-danger' : 'bg-success' }}">
                                    {{ $v->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Este lote todavía no se ha vendido.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Historial de movimientos de inventario</div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $m)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($m->creado_en)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $m->tipo_movimiento == 'ENTRADA' ? 'bg-success' : 'bg-primary' }}">
                                    {{ $m->tipo_movimiento }}
                                </span>
                            </td>
                            <td>{{ $m->cantidad }}</td>
                            <td>{{ $m->descripcion }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Sin movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
