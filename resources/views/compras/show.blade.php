@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Compra {{ $compra->numero_factura }}</h4>
    <a href="{{ route('compras.index') }}" class="gf-btn-soft gf-btn-apagado">Volver</a>
</div>

<div class="gf-card mb-3">
    <div class="p-3 row">
        <div class="col-md-3">
            <div class="gf-page-subtitle">Proveedor</div>
            <div class="fw-semibold">{{ $compra->proveedor->nombre ?? 'N/A' }}</div>
        </div>
        <div class="col-md-3">
            <div class="gf-page-subtitle">Fecha</div>
            <div class="fw-semibold">{{ $compra->fecha_compra }}</div>
        </div>
        <div class="col-md-3">
            <div class="gf-page-subtitle">Estado</div>
            <span class="gf-badge {{ $compra->estado == 'APROBADA' ? 'gf-badge-exito' : 'gf-badge-alerta' }}">
                {{ $compra->estado }}
            </span>
        </div>
        <div class="col-md-3">
            <div class="gf-page-subtitle">Total</div>
            <div class="fw-semibold">C$ {{ number_format($compra->total, 2) }}</div>
        </div>
    </div>
</div>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
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
                            <td class="fw-semibold">{{ $d->producto->nombre ?? 'Producto eliminado' }}</td>
                            <td>{{ $d->cantidad }}</td>
                            <td>C$ {{ number_format($d->precio_unitario, 2) }}</td>
                            <td>C$ {{ number_format($d->subtotal, 2) }}</td>
                            <td>{{ $d->numero_lote ?? '—' }}</td>
                            <td>{{ $d->fecha_vencimiento ?? '—' }}</td>
                            <td>
                                @if($d->id_lote)
                                    <span class="gf-badge gf-badge-exito">#{{ $d->id_lote }}</span>
                                @else
                                    <span class="gf-badge gf-badge-apagado">Pendiente de aprobar</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($compra->estado == 'PENDIENTE')
            <form action="{{ route('compras.aprobar', $compra->id_compra) }}" method="POST"
                  class="mt-3"
                  onsubmit="return confirm('¿Aprobar esta compra? Esto creará los lotes y sumará el stock.');">
                @csrf
                <button type="submit" class="gf-btn-primario" style="background:#2F6B45;">Aprobar compra</button>
            </form>
        @endif
    </div>
</div>

@endsection
