@extends('layouts.app')

@section('content')

<div class="container">

    <h3>Nuevo Cliente</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nombres</label>
            <input type="text"
                   name="nombres"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label>Apellidos</label>
            <input type="text"
                   name="apellidos"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label>Cédula</label>
            <input type="text"
                   name="cedula"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text"
                   name="telefono"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Dirección</label>
            <input type="text"
                   name="direccion"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email"
                   name="email"
                   class="form-control">
        </div>

        <button class="btn btn-success">
            Guardar
        </button>

        <a href="{{ route('clientes.index') }}"
           class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

@endsection