@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Productos</h4>

    <a href="{{ route('productos.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nuevo producto
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- El buscador del topbar reemplaza este bloque entero por AJAX cada
     vez que se escribe, para filtrar contra toda la tabla y no solo
     la página visible. Por eso vive en un parcial aparte. --}}
<div id="gf-resultados-busqueda">
    @include('productos._tabla')
</div>

@endsection