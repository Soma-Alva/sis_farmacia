@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Compras</h4>
    <a href="{{ route('compras.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nueva Compra
    </a>
</div>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($compras as $c)
                    <tr>
                        <td class="fw-semibold">{{ $c->numero_factura }}</td>
                        <td>{{ $c->proveedor->nombre ?? 'N/A' }}</td>
                        <td>{{ $c->fecha_compra }}</td>
                        <td>C$ {{ number_format($c->total,2) }}</td>
                        <td>
                            <span class="gf-badge {{ $c->estado == 'APROBADA' ? 'gf-badge-exito' : ($c->estado == 'CANCELADA' ? 'gf-badge-peligro' : 'gf-badge-alerta') }}">
                                {{ $c->estado }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('compras.show', $c->id_compra) }}" class="gf-btn-soft gf-btn-neutro">
                                Ver
                            </a>

                            @if($c->estado == 'PENDIENTE')
                                <form action="{{ route('compras.aprobar', $c->id_compra) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Aprobar esta compra? Esto creará los lotes y sumará el stock.');">
                                    @csrf
                                    <button type="submit" class="gf-btn-soft gf-btn-exito">
                                        Aprobar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No existen compras registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
