@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Lote #{{ $lote->id_lote }} — {{ $lote->producto->nombre ?? 'Producto eliminado' }}</h4>
    <a href="{{ route('lotes.index') }}" class="gf-btn-soft gf-btn-apagado">Volver</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error') || $errors->any())
    <div class="alert alert-danger">{{ session('error') ?? $errors->first() }}</div>
@endif

@php
    $yaVencio = $lote->fecha_vencimiento && $lote->fecha_vencimiento->isPast();
@endphp

@if($yaVencio && $lote->cantidad_actual > 0)
    <div class="gf-card mb-3" style="border-left:4px solid #8C3A2A;">
        <div class="p-3">
            <div class="fw-semibold mb-1" style="color:#8C3A2A;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Este lote está vencido y todavía tiene {{ $lote->cantidad_actual }} unidad(es) en stock
            </div>
            <p class="gf-page-subtitle mb-3">
                No debería seguir vendiéndose (el sistema ya lo bloquea automáticamente
                en el punto de venta). Da de baja este stock para sacarlo del inventario
                con su motivo registrado.
            </p>
            <form action="{{ route('lotes.darDeBaja', $lote->id_lote) }}" method="POST"
                  onsubmit="return confirm('Esto va a retirar {{ $lote->cantidad_actual }} unidad(es) del stock de forma permanente. ¿Confirmas?');">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Motivo</label>
                        <select name="motivo_baja" class="form-select" required>
                            <option value="DESTRUIDO">Destruido</option>
                            <option value="DEVUELTO_PROVEEDOR">Devuelto al proveedor</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Observaciones (opcional)</label>
                        <input type="text" name="observaciones" class="form-control"
                               placeholder="Ej: acta de destrucción #123">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="gf-btn-primario w-100 justify-content-center" style="background:#8C3A2A;">
                            Dar de baja
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="gf-card">
            <div class="p-3 row">
                <div class="col-md-4">
                    <div class="gf-page-subtitle">N° de Lote</div>
                    <div class="fw-semibold">{{ $lote->numero_lote ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="gf-page-subtitle">Vencimiento</div>
                    <div class="fw-semibold">{{ $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : 'Sin registrar' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="gf-page-subtitle">Estado</div>
                    <span class="gf-badge
                        {{ $lote->estado == 'ACTIVO' ? 'gf-badge-exito' :
                           ($lote->estado == 'AGOTADO' ? 'gf-badge-apagado' :
                           ($lote->estado == 'VENCIDO' ? 'gf-badge-peligro' : 'gf-badge-neutro')) }}">
                        {{ $lote->estado }}
                    </span>
                </div>

                <div class="col-md-4 mt-3">
                    <div class="gf-page-subtitle">Cantidad inicial</div>
                    <div class="fw-semibold">{{ $lote->cantidad_inicial }}</div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="gf-page-subtitle">Cantidad actual</div>
                    <div class="fw-semibold">{{ $lote->cantidad_actual }}</div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="gf-page-subtitle">Costo unitario</div>
                    <div class="fw-semibold">C$ {{ number_format($lote->costo_unitario, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="gf-card">
            <div class="p-3">
                <div class="gf-page-subtitle mb-2">Origen</div>
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

<div class="gf-card mb-3">
    <div class="gf-card-header">Ventas donde se usó este lote</div>
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table">
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
                                <span class="gf-badge {{ $v->estado == 'ANULADA' ? 'gf-badge-peligro' : 'gf-badge-exito' }}">
                                    {{ $v->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Este lote todavía no se ha vendido.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="gf-card">
    <div class="gf-card-header">Historial de movimientos de inventario</div>
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table">
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
                                <span class="gf-badge {{ $m->tipo_movimiento == 'ENTRADA' ? 'gf-badge-exito' : 'gf-badge-neutro' }}">
                                    {{ $m->tipo_movimiento }}
                                </span>
                            </td>
                            <td>{{ $m->cantidad }}</td>
                            <td>{{ $m->descripcion }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Sin movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
