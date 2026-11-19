@extends('layouts.app')

@section('content')

<style>
    .gf-stat-clickable{
        cursor:pointer;
        transition:transform .12s ease, box-shadow .12s ease;
    }
    .gf-stat-clickable:hover,
    .gf-stat-clickable:focus-visible{
        transform:translateY(-3px);
        box-shadow:0 8px 18px rgba(47,79,62,0.14);
        outline:none;
    }

    .gf-modal-stock{
        border:none;
        border-radius:22px;
        overflow:hidden;
        box-shadow:0 24px 60px rgba(47,79,62,0.3);
    }
    .gf-modal-stock-header{
        background:linear-gradient(135deg, #8A6A1F 0%, #C98A3B 100%);
        padding:24px 54px 22px 24px;
        display:flex;
        align-items:flex-start;
        gap:14px;
        position:relative;
    }
    .gf-modal-stock-icono{
        width:46px;
        height:46px;
        border-radius:14px;
        background:rgba(255,255,255,0.2);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:20px;
        color:#fff;
        flex:0 0 auto;
    }
    .gf-modal-stock-titulo{
        font-family:'Fraunces',serif;
        font-weight:600;
        font-size:19px;
        color:#fff;
        margin:0;
    }
    .gf-modal-stock-sub{
        font-size:12.5px;
        color:rgba(255,255,255,0.85);
        margin:3px 0 0;
    }
    .gf-modal-stock-cerrar{
        position:absolute;
        top:16px;
        right:16px;
        width:32px;
        height:32px;
        border-radius:50%;
        border:none;
        background:rgba(255,255,255,0.2);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
    }
    .gf-modal-stock-cerrar:hover{ background:rgba(255,255,255,0.35); }
    .gf-modal-stock-resumen{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        padding:16px 24px 4px;
        background:#fff;
    }
    .gf-modal-stock-lista{
        background:#fff;
        padding:8px 24px 22px;
        max-height:55vh;
        overflow-y:auto;
    }
    .gf-modal-stock-fila{
        display:flex;
        align-items:center;
        gap:14px;
        padding:12px 0;
        border-bottom:1px solid rgba(47,79,62,0.08);
    }
    .gf-modal-stock-fila:last-child{ border-bottom:none; }
    .gf-modal-stock-info{ flex:1 1 auto; min-width:0; }
    .gf-modal-stock-nombre{
        font-weight:600;
        font-size:13.5px;
        color:var(--carbon);
    }
    .gf-modal-stock-meta{
        font-size:11.5px;
        color:rgba(36,36,32,0.5);
    }
    .gf-modal-stock-cifras{
        text-align:right;
        white-space:nowrap;
    }
    .gf-modal-stock-cifras small{
        display:block;
        font-size:11px;
        color:rgba(36,36,32,0.5);
        margin-top:3px;
    }
    .gf-modal-stock-pie{
        background:#fff;
        padding:0 24px 22px;
        text-align:right;
    }
</style>

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
        {{-- Con productos en alerta, toda la tarjeta abre la ventana de detalle --}}
        <div class="gf-stat {{ $stockBajo > 0 ? 'gf-stat-clickable' : '' }}"
             @if($stockBajo > 0)
                 data-bs-toggle="modal" data-bs-target="#gfModalStockBajo"
                 role="button" tabindex="0" title="Ver productos con stock bajo"
             @endif
        >
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

document.querySelectorAll('.gf-stat-clickable').forEach(function (tarjeta) {
    tarjeta.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); tarjeta.click(); }
    });
});

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

{{-- Detalle de los productos con stock bajo (se abre desde la tarjeta "Stock Bajo") --}}
@if($stockBajo > 0)
    <div class="modal fade" id="gfModalStockBajo" tabindex="-1" aria-labelledby="gfModalStockBajoTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content gf-modal-stock">

                <div class="gf-modal-stock-header">
                    <div class="gf-modal-stock-icono"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <h5 class="gf-modal-stock-titulo" id="gfModalStockBajoTitulo">Productos con stock bajo</h5>
                        <p class="gf-modal-stock-sub">En o por debajo de su stock mínimo · los más críticos primero</p>
                    </div>
                    <button type="button" class="gf-modal-stock-cerrar" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="gf-modal-stock-resumen">
                    <span class="gf-badge gf-badge-peligro">{{ $productosStockBajo->where('stock_actual', '<=', 0)->count() }} agotado(s)</span>
                    <span class="gf-badge gf-badge-alerta">{{ $productosStockBajo->where('stock_actual', '>', 0)->count() }} por debajo del mínimo</span>
                </div>

                <div class="gf-modal-stock-lista">
                    @foreach($productosStockBajo as $producto)
                        <div class="gf-modal-stock-fila">
                            <div class="gf-modal-stock-info">
                                <div class="gf-modal-stock-nombre">{{ $producto->nombre }}</div>
                                <div class="gf-modal-stock-meta">
                                    {{ $producto->categoria ?: 'Sin categoría' }}
                                    @if($producto->presentacion) · {{ $producto->presentacion }} @endif
                                    @if($producto->concentracion) · {{ $producto->concentracion }} @endif
                                </div>
                            </div>
                            <div class="gf-modal-stock-cifras">
                                @if($producto->stock_actual <= 0)
                                    <span class="gf-badge gf-badge-peligro">Agotado</span>
                                @else
                                    <span class="gf-badge gf-badge-alerta">{{ $producto->stock_actual }} en stock</span>
                                @endif
                                <small>Mínimo: {{ $producto->stock_minimo }} · Faltan {{ max($producto->stock_minimo - $producto->stock_actual, 0) }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="gf-modal-stock-pie">
                    <a href="{{ route('productos.index') }}" class="gf-btn-soft gf-btn-neutro">
                        <i class="bi bi-box-seam"></i> Ir a Productos
                    </a>
                </div>

            </div>
        </div>
    </div>
@endif

@endsection
