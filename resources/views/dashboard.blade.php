@extends('layouts.app')

@section('content')

<div class="mb-3">
    <h4 class="gf-page-title">Dashboard</h4>
    <p class="gf-page-subtitle">Resumen general de la farmacia</p>
</div>

{{-- Alertas de vencimiento --}}
@if($lotesVencidos->count() > 0)
    <div class="gf-banner danger">
        <div>
            <i class="bi bi-exclamation-triangle-fill"></i>
            Tienes <strong>{{ $lotesVencidos->count() }}</strong> lote(s) <strong>vencido(s)</strong>
            con stock disponible. No deberían seguir vendiéndose.
        </div>
        <a href="{{ route('lotes.index', ['estado' => 'ACTIVO']) }}">Revisar</a>
    </div>
@endif

@if($lotesPorVencer->count() > 0)
    <div class="gf-banner warning">
        <div>
            <i class="bi bi-clock-history"></i>
            <strong>{{ $lotesPorVencer->count() }}</strong> lote(s) vencen en los próximos 90 días.
        </div>
        <a href="{{ route('lotes.index', ['proximos_vencer' => 1]) }}">Ver en Lotes</a>
    </div>
@endif

<div class="row g-3 mb-3">

    <div class="col-md-3">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-oscuro);"><i class="bi bi-cart-check"></i></div>
            <div class="etiqueta">Ventas Hoy</div>
            <div class="valor">C$ {{ number_format($ventasHoy, 2) }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gf-stat">
            <div class="icono" style="background:var(--verde-medio);"><i class="bi bi-box-seam"></i></div>
            <div class="etiqueta">Total Productos</div>
            <div class="valor">{{ $totalProductos }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gf-stat">
            <div class="icono" style="background:#C98A3B;"><i class="bi bi-exclamation-circle"></i></div>
            <div class="etiqueta">Stock Bajo</div>
            <div class="valor">{{ $stockBajo }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="gf-stat">
            <div class="icono" style="background:{{ $lotesVencidos->count() > 0 ? '#C0503D' : '#C98A3B' }};">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div class="etiqueta">
                {{ $lotesVencidos->count() > 0 ? 'Lotes Vencidos' : 'Por Vencer (90 días)' }}
            </div>
            <div class="valor">
                {{ $lotesVencidos->count() > 0 ? $lotesVencidos->count() : $lotesPorVencer->count() }}
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mb-3">

    <div class="col-md-6">
        <div class="gf-card">
            <div class="gf-card-header">Productos con Stock Bajo</div>
            <div class="p-3" style="height:320px;">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="gf-card">
            <div class="gf-card-header">Productos por Categoría</div>
            <div class="p-3" style="height:320px;">
                <canvas id="categoriaChart"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mb-3">
    <div class="col-md-12">
        <div class="gf-card">
            <div class="gf-card-header">Ventas por Mes</div>
            <div class="p-3" style="height:320px;">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>
    </div>
</div>

@if($lotesVencidos->count() > 0 || $lotesPorVencer->count() > 0)
<div class="row g-3">
    <div class="col-md-12">
        <div class="gf-card">
            <div class="gf-card-header">
                <i class="bi bi-calendar-x"></i> Lotes vencidos o próximos a vencer
            </div>

            <div class="p-3">
                <div class="table-responsive">
                    <table class="gf-table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>N° de Lote</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Stock</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lotesVencidos->concat($lotesPorVencer) as $lote)
                                @php
                                    $dias = (int) now()->diffInDays($lote->fecha_vencimiento, false);
                                @endphp
                                <tr class="{{ $dias < 0 ? 'gf-row-danger' : ($dias <= 30 ? 'gf-row-warning' : '') }}">
                                    <td class="fw-semibold">{{ $lote->producto->nombre ?? 'Producto eliminado' }}</td>
                                    <td>{{ $lote->numero_lote ?? '—' }}</td>
                                    <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td>
                                        @if($dias < 0)
                                            <span class="gf-badge gf-badge-peligro">Vencido</span>
                                        @else
                                            <span class="gf-badge gf-badge-alerta">en {{ $dias }} días</span>
                                        @endif
                                    </td>
                                    <td>{{ $lote->cantidad_actual }}</td>
                                    <td>
                                        <a href="{{ route('lotes.show', $lote->id_lote) }}" class="gf-btn-soft gf-btn-neutro">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>

const COLOR_VERDE_OSCURO = '#2F4F3E';
const COLOR_VERDE_MEDIO  = '#4B7A61';
const PALETA = ['#2F4F3E', '#4B7A61', '#6FA082', '#A9CDB4', '#C98A3B', '#8C3A2A'];

new Chart(document.getElementById('stockChart'),{
    type:'bar',
    data:{
        labels:@json($productosStockBajo->pluck('nombre')),
        datasets:[{
            label:'Stock',
            data:@json($productosStockBajo->pluck('stock_actual')),
            backgroundColor: COLOR_VERDE_MEDIO,
            borderRadius:6
        }]
    },
    options:{
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true } }
    }
});


new Chart(document.getElementById('categoriaChart'),{
    type:'pie',
    data:{
        labels:@json($productosPorCategoria->pluck('nombre')),
        datasets:[{
            data:@json($productosPorCategoria->pluck('total')),
            backgroundColor: PALETA
        }]
    }
});


new Chart(document.getElementById('ventasChart'),{
    type:'line',
    data:{
        labels:@json($ventasPorMes->pluck('mes')),
        datasets:[{
            label:'Ventas',
            data:@json($ventasPorMes->pluck('total')),
            fill:true,
            backgroundColor:'rgba(75,122,97,0.12)',
            borderColor: COLOR_VERDE_OSCURO,
            tension:0.35,
            pointBackgroundColor: COLOR_VERDE_OSCURO
        }]
    },
    options:{
        plugins:{ legend:{ display:false } }
    }
});

</script>
@endpush
@endsection
