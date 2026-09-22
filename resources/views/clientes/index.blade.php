@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">
        <h3>Clientes</h3>

        <a href="{{ route('clientes.create') }}"
           class="btn btn-primary">
            + Nuevo Cliente
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body">

            <table class="table table-hover">

                <thead class="table-dark">
                    <tr>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($clientes as $cliente)

                    <tr>
                        <td>{{ $cliente->nombres }}</td>
                        <td>{{ $cliente->apellidos }}</td>
                        <td>{{ $cliente->cedula }}</td>
                        <td>{{ $cliente->telefono }}</td>
                        <td>{{ $cliente->email }}</td>

                        <td>
                            @if($cliente->estado)
                                Activo
                            @else
                                Inactivo
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('clientes.edit', $cliente->id_cliente) }}"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Eliminar cliente?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection