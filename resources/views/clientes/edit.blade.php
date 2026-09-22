@extends('layouts.app')

@section('content')

<div class="container">

    <h3>Editar Cliente</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.update', $cliente->id_cliente) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nombres</label>
            <input type="text"
                   name="nombres"
                   class="form-control"
                   value="{{ $cliente->nombres }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Apellidos</label>
            <input type="text"
                   name="apellidos"
                   class="form-control"
                   value="{{ $cliente->apellidos }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Cédula</label>
            <input type="text"
                   name="cedula"
                   class="form-control"
                   value="{{ $cliente->cedula }}">
        </div>

        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text"
                   name="telefono"
                   class="form-control"
                   value="{{ $cliente->telefono }}">
        </div>

        <div class="mb-3">
            <label>Dirección</label>
            <input type="text"
                   name="direccion"
                   class="form-control"
                   value="{{ $cliente->direccion }}">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   value="{{ $cliente->email }}">
        </div>

        <button class="btn btn-success">
            Actualizar
        </button>

        <a href="{{ route('clientes.index') }}"
           class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

@endsection