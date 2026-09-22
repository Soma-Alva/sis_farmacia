@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h4 class="mb-0">Editar proveedor</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
                @csrf
                @method('PUT')
                @include('proveedores._form', ['textoBoton' => 'Actualizar'])
            </form>
        </div>
    </div>
</div>
@endsection
