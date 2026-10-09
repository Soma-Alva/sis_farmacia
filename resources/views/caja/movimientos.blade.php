@extends('layouts.app')

@section('content')

<h4 class="gf-page-title mb-3">Movimientos de Caja</h4>

<div class="gf-card">
    <div class="p-3">
        <div class="table-responsive">
            <table class="gf-table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Ticket</th>
                        <th>Cliente</th>
                        <th>Método de Pago</th>
                        <th>Monto</th>
                        <th>Motivo</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th>Saldo Anterior</th>
                        <th>Saldo Actual</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movimientos as $m)
                    <tr>
                        <td>{{ $m->creado_en }}</td>

                        <td>
                            @if($m->id_venta)
                                <span class="gf-badge gf-badge-neutro">Venta</span>
                            @elseif($m->tipo_movimiento=='INGRESO')
                                <span class="gf-badge gf-badge-exito">Ingreso</span>
                            @else
                                <span class="gf-badge gf-badge-peligro">Egreso</span>
                            @endif
                        </td>

                        <td>{{ $m->numero_ticket ?? '-' }}</td>
                        <td>{{ $m->cliente_nombre ?? '-' }}</td>
                        <td>{{ $m->metodo_pago ?? $m->forma_pago }}</td>
                        <td class="fw-semibold">C$ {{ number_format($m->monto,2) }}</td>
                        <td>{{ $m->motivo }}</td>
                        <td>{{ $m->descripcion }}</td>
                        <td>{{ $m->nombre_completo }}</td>
                        <td>C$ {{ number_format($m->saldo_anterior,2) }}</td>
                        <td>C$ {{ number_format($m->saldo_actual,2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            No existen movimientos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<button class="gf-btn-soft gf-btn-apagado mt-3" onclick="window.history.back()">
    Regresar
</button>
@endsection
