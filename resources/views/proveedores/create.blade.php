@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Nuevo proveedor</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('proveedores.store') }}" method="POST">
                @csrf
                @include('proveedores._form', ['textoBoton' => 'Guardar'])
            </form>
        </div>
    </div>
</div>
@endsection
