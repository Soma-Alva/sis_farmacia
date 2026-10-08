@extends('layouts.app')

@section('content')

<div class="container-fluid mt-4">

    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Dashboard Farmacia</h2>
        </div>
    </div>

    {{-- Alertas de vencimiento --}}
    @if($lotesVencidos->count() > 0)
        <div class="alert alert-danger d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-exclamation-triangle-fill"></i>
                Tienes <strong>{{ $lotesVencidos->count() }}</strong> lote(s)
                <strong>vencido(s)</strong> con stock disponible. No deberían
                seguir vendiéndose.
            </div>
            <a href="{{ route('lotes.index', ['estado' => 'ACTIVO']) }}" class="btn btn-sm btn-outline-light" style="color:inherit;border-color:currentColor">
                Revisar
            </a>
        </div>
    @endif

    @if($lotesPorVencer->count() > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-clock-history"></i>
                <strong>{{ $lotesPorVencer->count() }}</strong> lote(s) vencen
                en los próximos 90 días. Revisa el detalle abajo para
                priorizar su venta o devolución al proveedor.
            </div>
            <a href="{{ route('lotes.index', ['proximos_vencer' => 1]) }}" class="btn btn-sm btn-outline-dark">
                Ver en Lotes
            </a>
        </div>
    @endif

    <div class="row g-4">

        <!-- Ventas Hoy -->
        <div class="col-md-3">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">Ventas Hoy</h5>
                    <h2 class="fw-bold">C$ {{ $ventasHoy }}</h2>
                </div>
            </div>
        </div> 


        <!-- Total Productos -->
        <div class="col-md-3">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">Total Productos</h5>
                    <h2 class="fw-bold">{{ $totalProductos }}</h2>
                </div>
            </div>
        </div>

        <!-- Stock Bajo -->
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">Stock Bajo</h5>
                    <h2 class="fw-bold">{{ $stockBajo }}</h2>
                </div>
            </div>
        </div>

        <!-- Lotes por vencer -->
        <div class="col-md-3">
            <div class="card {{ $lotesVencidos->count() > 0 ? 'bg-danger' : 'bg-warning' }} text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">
                        {{ $lotesVencidos->count() > 0 ? 'Lotes Vencidos' : 'Por Vencer (90 días)' }}
                    </h5>
                    <h2 class="fw-bold">
                        {{ $lotesVencidos->count() > 0 ? $lotesVencidos->count() : $lotesPorVencer->count() }}
                    </h2>
                </div>
            </div>
        </div>

    </div>

    <hr class="my-5">
    <div class="row">

    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                Productos con Stock Bajo
            </div>

            <div class="card-body" style="height:350px;">
            <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                Productos por Categoría
            </div>

            <div class="card-body" style="height:350px;">
                <canvas id="categoriaChart"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                Ventas por Mes
            </div>

            <div class="card-body" style="height:350px;">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>
    </div>

</div>

@if($lotesVencidos->count() > 0 || $lotesPorVencer->count() > 0)
<div class="row mt-4">

    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <i class="bi bi-calendar-x"></i>
                Lotes vencidos o próximos a vencer
            </div>

            <div class="card-body">
                <table class="table table-sm table-hover">
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
                            <tr class="{{ $dias < 0 ? 'table-danger' : ($dias <= 30 ? 'table-warning' : '') }}">
                                <td>{{ $lote->producto->nombre ?? 'Producto eliminado' }}</td>
                                <td>{{ $lote->numero_lote ?? '—' }}</td>
                                <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td>
                                    @if($dias < 0)
                                        <span class="badge bg-danger">Vencido</span>
                                    @else
                                        <span class="badge bg-warning text-dark">en {{ $dias }} días</span>
                                    @endif
                                </td>
                                <td>{{ $lote->cantidad_actual }}</td>
                                <td>
                                    <a href="{{ route('lotes.show', $lote->id_lote) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endif




</div>

@push('scripts')
<script>

new Chart(document.getElementById('stockChart'),{

    type:'bar',

    data:{
        labels:@json($productosStockBajo->pluck('nombre')),
        datasets:[{
            label:'Stock',
            data:@json($productosStockBajo->pluck('stock_actual'))
        }]
    }

});


new Chart(document.getElementById('categoriaChart'),{

    type:'pie',

    data:{
        labels:@json($productosPorCategoria->pluck('nombre')),
        datasets:[{
            data:@json($productosPorCategoria->pluck('total'))
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
            fill:false
        }]
    }

});

</script>
@endpush
@endsection 
