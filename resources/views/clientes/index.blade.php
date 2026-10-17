@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="gf-page-title">Clientes</h4>
    <a href="{{ route('clientes.create') }}" class="gf-btn-primario">
        <i class="bi bi-plus-lg"></i> Nuevo Cliente
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
    @include('clientes._tabla')
</div>

@endsection
