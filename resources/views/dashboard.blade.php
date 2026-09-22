@extends('layouts.app')

@section('content')

<div class="container-fluid mt-4">

    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Dashboard Farmacia</h2>
        </div>
    </div>

    <div class="row g-4">

        <!-- Ventas Hoy -->
        <div class="col-md-4">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">Ventas Hoy</h5>
                    <h2 class="fw-bold">C$ {{ $ventasHoy }}</h2>
                </div>
            </div>
        </div> 


        <!-- Total Productos -->
        <div class="col-md-4">
            <div class="card bg-info text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">Total Productos</h5>
                    <h2 class="fw-bold">{{ $totalProductos }}</h2>
                </div>
            </div>
        </div>

        <!-- Stock Bajo -->
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center"
                     style="min-height:150px;">
                    <h5 class="card-title">Stock Bajo</h5>
                    <h2 class="fw-bold">{{ $stockBajo }}</h2>
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
