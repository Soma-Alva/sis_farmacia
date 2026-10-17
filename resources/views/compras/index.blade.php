@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Compras</h4>
    <a href="{{ route('compras.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nueva Compra
    </a>
</div>

{{-- El buscador del topbar reemplaza este bloque entero por AJAX cada
     vez que se escribe, para filtrar contra toda la tabla y no solo
     la página visible. Por eso vive en un parcial aparte. --}}
<div id="gf-resultados-busqueda">
    @include('compras._tabla')
</div>

@endsection
