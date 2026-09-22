@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Nueva categoría</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf
                @include('categorias._form', ['textoBoton' => 'Guardar'])
            </form>
        </div>
    </div>
</div>
@endsection
