@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="gf-page-title mb-0">Ingresos y egresos</h4>
        <p class="gf-page-subtitle mb-0">{{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('reportes.index') }}" class="gf-btn-soft gf-btn-apagado"><i class="bi bi-arrow-left"></i> Reportes</a>
</div>

@include('reportes._filtro', ['ruta' => 'reportes.ingresos-egresos', 'excel' => 'reportes.ingresos-egresos.excel'])

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="gf-rep-kpi">
            <div class="et">Total ingresos</div>
            <div class="val pos">C$ {{ number_format($totalIngresos, 2) }}</div>
            <div class="sub">Ventas C$ {{ number_format($totalVentas, 2) }} · Manuales C$ {{ number_format($totalIngresosManuales, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gf-rep-kpi">
            <div class="et">Total egresos</div>
            <div class="val neg">C$ {{ number_format($totalEgresos, 2) }}</div>
            <div class="sub">Compras C$ {{ number_format($totalCompras, 2) }} · Caja C$ {{ number_format($totalEgresosCaja, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gf-rep-kpi">
            <div class="et">Neto (ingresos − egresos)</div>
            <div class="val {{ $neto >= 0 ? 'pos' : 'neg' }}">C$ {{ number_format($neto, 2) }}</div>
            <div class="sub">{{ $neto >= 0 ? 'Entró más dinero del que salió' : 'Salió más dinero del que entró' }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ingresos vs egresos por día</div>
            <div class="p-3" style="height:300px;"><canvas id="ieChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ingresos por método de pago</div>
            <div class="p-3">
                <table class="gf-table">
                    <thead><tr><th>Método</th><th class="text-end">Tickets</th><th class="text-end">Monto</th></tr></thead>
                    <tbody>
                        @foreach($porMetodo as $m)
                            <tr>
                                <td>{{ \App\Services\ReporteService::etiquetaMetodo($m->metodo) }}</td>
                                <td class="text-end">{{ $m->tickets }}</td>
                                <td class="text-end">C$ {{ number_format($m->total, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="gf-tot-fila"><td>Ventas</td><td class="text-end">{{ $porMetodo->sum('tickets') }}</td><td class="text-end">C$ {{ number_format($totalVentas, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Ingresos manuales de caja <span class="gf-badge gf-badge-neutro ms-1">{{ $ingresosManuales->count() }}</span></div>
            <div class="p-3">
                @include('reportes._movimientos', ['filas' => $ingresosManuales, 'total' => $totalIngresosManuales, 'vacio' => 'Sin ingresos manuales en este periodo'])
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="gf-card h-100">
            <div class="gf-card-header">Egresos manuales de caja <span class="gf-badge gf-badge-neutro ms-1">{{ $egresosCaja->count() }}</span></div>
            <div class="p-3">
                @include('reportes._movimientos', ['filas' => $egresosCaja, 'total' => $totalEgresosCaja, 'vacio' => 'Sin egresos manuales en este periodo'])
            </div>
        </div>
    </div>
</div>

<div class="gf-card mb-3">
    <div class="gf-card-header">Compras de mercadería aprobadas <span class="gf-badge gf-badge-neutro ms-1">{{ $compras->count() }}</span></div>
    <div class="p-3">
        @if($compras->count() > 0)
            <div class="table-responsive">
                <table class="gf-table table-hover">
                    <thead><tr><th>Fecha</th><th>Factura</th><th>Proveedor</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach($compras as $c)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                                <td>{{ $c->numero_factura }}</td>
                                <td>{{ $c->proveedor ?: '—' }}</td>
                                <td class="text-end">C$ {{ number_format($c->total, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="gf-tot-fila"><td colspan="3">Total compras</td><td class="text-end">C$ {{ number_format($totalCompras, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="gf-page-subtitle mb-0">Sin compras aprobadas en este periodo</p>
        @endif
    </div>
</div>

<p class="gf-page-subtitle">
    Nota: las compras se toman de la pantalla de Compras (estado Aprobada). Los egresos de caja que estén ligados a una compra no se cuentan dos veces.
</p>

@push('scripts')
<script>
const etiquetas = @json(collect($dias)->keys()->map(fn ($f) => \Carbon\Carbon::parse($f)->format('d/m'))->values());
const ingresos = @json(collect($dias)->pluck('ing')->map(fn ($v) => round($v, 2))->values());
const egresos = @json(collect($dias)->pluck('egr')->map(fn ($v) => round($v, 2))->values());
Chart.defaults.font.family = "'Inter', sans-serif";
new Chart(document.getElementById('ieChart'), {
    type: 'bar',
    data: { labels: etiquetas, datasets: [
        { label: 'Ingresos', data: ingresos, backgroundColor: '#4B7A61', borderRadius: 4 },
        { label: 'Egresos', data: egresos, backgroundColor: '#C98A3B', borderRadius: 4 }
    ]},
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'C$ ' + v } } } }
});
</script>
@endpush

@endsection
